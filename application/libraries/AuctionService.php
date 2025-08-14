<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuctionService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('market');
        $this->CI->load->library(['Wallet','Observability']);
    }

    private function cfg($key, $default=null) {
        $cfg = $this->CI->config->item('market') ?? [];
        $val = $cfg;
        foreach (explode('.', $key) as $k) { $val = $val[$k] ?? null; if ($val===null) return $default; }
        return $val;
    }

    private function rateBump($realmId) {
        $now = time();
        $r = $this->cfg('rate.bids', ['window_sec'=>60,'max'=>30]);
        $ws = (int)floor($now / $r['window_sec']) * $r['window_sec'];
        $key = ['realm_id'=>$realmId,'action'=>'auction_bid','window_start'=>$ws];
        $row = $this->CI->db->get_where('rate_counters', $key)->row_array();
        if (!$row) {
            $this->CI->db->insert('rate_counters', $key + ['count'=>1,'updated_at'=>$now]); return;
        }
        if ((int)$row['count'] >= (int)$r['max']) throw new Exception('Rate limited');
        $this->CI->db->set('count','count+1',FALSE)->set('updated_at',$now)->where($key)->update('rate_counters');
    }

    private function decInventory($realmId, $itemId, $qty) {
        $row = $this->CI->db->get_where('inventory',['realm_id'=>$realmId,'item_id'=>$itemId])->row_array();
        $have = (int)($row['qty'] ?? 0);
        if ($have < $qty) throw new Exception('Insufficient items');
        $left = $have - $qty;
        $this->CI->db->update('inventory',['qty'=>$left,'updated_at'=>time()],['realm_id'=>$realmId,'item_id'=>$itemId]);
    }
    private function incInventory($realmId, $itemId, $qty) {
        $row = $this->CI->db->get_where('inventory',['realm_id'=>$realmId,'item_id'=>$itemId])->row_array();
        if ($row) $this->CI->db->update('inventory',['qty'=>((int)$row['qty'])+$qty,'updated_at'=>time()],['id'=>$row['id']]);
        else $this->CI->db->insert('inventory',['realm_id'=>$realmId,'item_id'=>$itemId,'qty'=>$qty,'updated_at'=>time()]);
    }

    public function create(int $realmId, string $itemId, int $qty, int $start, ?int $buyout, int $minutes): int {
        if ($qty<=0 || $start<=0) throw new Exception('Invalid');
        $minM = (int)$this->cfg('auction_min_minutes',30);
        $maxD = (int)$this->cfg('auction_max_days',7);
        $minutes = max($minM, min($minutes, $maxD*24*60));
        // depósito (como en market)
        $depBps = (int)$this->cfg('deposit_bps',50);
        $deposit = (int)floor(($qty * $start) * $depBps / 10000);
        if ($deposit>0) {
            if (!$this->CI->wallet->spend($realmId,'gold',$deposit,'auction_deposit','auction',null)) throw new Exception('Not enough gold for deposit');
        }
        $this->decInventory($realmId, $itemId, $qty);
        $ends = time() + $minutes*60;
        $minInc = max(1, (int)floor($start * (int)$this->cfg('min_increment_bps',500) / 10000));
        $this->CI->db->insert('auctions',[
            'seller_realm_id'=>$realmId,'item_id'=>$itemId,'qty'=>$qty,'start_price'=>$start,'buyout_price'=>$buyout,
            'min_increment'=>$minInc,'deposit'=>$deposit,'tax_bps'=>(int)$this->cfg('fee_bps',250),'ends_at'=>$ends,'created_at'=>time(),'status'=>0
        ]);
        return (int)$this->CI->db->insert_id();
    }

    public function bid(int $realmId, int $auctionId, int $amount): void {
        $this->rateBump($realmId);
        $a = $this->CI->db->get_where('auctions',['id'=>$auctionId])->row_array();
        if (!$a || (int)$a['status']!==0) throw new Exception('Auction not active');
        if ((int)$a['seller_realm_id'] === $realmId) throw new Exception('Self bid not allowed');
        $now = time();
        if ($now >= (int)$a['ends_at']) throw new Exception('Auction ended');
        // current price
        $top = $this->CI->db->order_by('amount','DESC')->limit(1)->get_where('auction_bids',['auction_id'=>$auctionId])->row_array();
        $current = (int)($top ? $top['amount'] : $a['start_price']);
        $minNext = $current + max((int)$a['min_increment'],1);
        if ($amount < $minNext) throw new Exception('Bid too low');
        // reserve gold: we do NOT reserve immediately; we only check balance
        $bal = $this->CI->db->get_where('wallets',['realm_id'=>$realmId])->row_array();
        // Fallback if Wallet lib required; try library
        if (method_exists($this->CI->wallet ?? null, 'balance')) {
            $wb = $this->CI->wallet->balance($realmId);
            $gold = (int)($wb['gold'] ?? 0);
        } else {
            $gold = (int)($bal['gold'] ?? 0);
        }
        if ($gold < $amount) throw new Exception('Not enough gold');
        // record bid
        $this->CI->db->insert('auction_bids',['auction_id'=>$auctionId,'bidder_realm_id'=>$realmId,'amount'=>$amount,'created_at'=>$now]);
        // soft extend
        $soft = (int)$this->cfg('soft_extend_seconds',30);
        if (($a['ends_at'] - $now) <= $soft) {
            $this->CI->db->update('auctions',['ends_at'=>$a['ends_at'] + $soft],['id'=>$auctionId]);
        }
        // buyout?
        if (!empty($a['buyout_price']) && $amount >= (int)$a['buyout_price']) {
            $this->finalize($auctionId, $realmId, (int)$a['buyout_price']);
        }
    }

    private function topBid($auctionId) {
        return $this->CI->db->order_by('amount','DESC')->limit(1)->get_where('auction_bids',['auction_id'=>$auctionId])->row_array();
    }

    public function finalizeExpired(): int {
        $now = time();
        $rows = $this->CI->db->get_where('auctions',['status'=>0,'ends_at <='=>$now])->result_array();
        foreach ($rows as $a) {
            $top = $this->topBid((int)$a['id']);
            if ($top) {
                $this->finalize((int)$a['id'], (int)$top['bidder_realm_id'], (int)$top['amount']);
            } else {
                // sin pujas: devolver items y depósito
                $this->CI->db->update('auctions',['status'=>3],['id'=>$a['id']]);
                $this->incInventory((int)$a['seller_realm_id'], $a['item_id'], (int)$a['qty']);
                if ((int)$a['deposit']>0) $this->CI->wallet->add((int)$a['seller_realm_id'],'gold',(int)$a['deposit'],'auction_deposit_refund','auction',(int)$a['id']);
            }
        }
        return count($rows);
    }

    private function finalize(int $auctionId, int $winnerRealmId, int $price): void {
        $a = $this->CI->db->get_where('auctions',['id'=>$auctionId])->row_array();
        if (!$a || (int)$a['status']!==0) return;
        // cobrar al ganador
        if (!$this->CI->wallet->spend($winnerRealmId,'gold',$price,'auction_win','auction',$auctionId)) {
            // si falla, no finaliza; dejar activo
            return;
        }
        $fee = (int)floor($price * (int)$a['tax_bps'] / 10000);
        $net = max(0, $price - $fee);
        // pagar al vendedor + devolver depósito
        $this->CI->wallet->add((int)$a['seller_realm_id'],'gold',$net,'auction_sale','auction',$auctionId);
        if ((int)$a['deposit']>0) $this->CI->wallet->add((int)$a['seller_realm_id'],'gold',(int)$a['deposit'],'auction_deposit_refund','auction',$auctionId);
        // entregar items
        $this->incInventory($winnerRealmId, $a['item_id'], (int)$a['qty']);
        $this->CI->db->update('auctions',['status'=>1,'winner_realm_id'=>$winnerRealmId,'final_price'=>$price],['id'=>$auctionId]);
        $this->CI->observability->inc('auction.win', ['item'=>$a['item_id']], 1);
    }

    public function cancel(int $realmId, int $auctionId): void {
        $a = $this->CI->db->get_where('auctions',['id'=>$auctionId])->row_array();
        if (!$a || (int)$a['status']!==0) throw new Exception('Auction not active');
        if ((int)$a['seller_realm_id'] !== $realmId) throw new Exception('Not your auction');
        $hasBids = $this->CI->db->where('auction_id',$auctionId)->count_all_results('auction_bids')>0;
        if ($hasBids) throw new Exception('Cannot cancel after bids');
        $this->CI->db->update('auctions',['status'=>2],['id'=>$auctionId]);
        $this->incInventory($realmId, $a['item_id'], (int)$a['qty']);
        if ((int)$a['deposit']>0) $this->CI->wallet->add($realmId,'gold',(int)$a['deposit'],'auction_deposit_refund','auction',$auctionId);
    }
}
