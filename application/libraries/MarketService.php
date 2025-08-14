<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MarketService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('market');
        $this->CI->load->library(['Wallet','Observability','Caching']);
    }

    private function cfg($key, $default=null) {
        $cfg = $this->CI->config->item('market') ?? [];
        $val = $cfg;
        foreach (explode('.', $key) as $k) { $val = $val[$k] ?? null; if ($val===null) return $default; }
        return $val;
    }

    private function rateBump($realmId, $action) {
        $now = time();
        $r = $this->cfg('rate.'.$action, ['window_sec'=>60,'max'=>30]);
        $ws = (int)floor($now / $r['window_sec']) * $r['window_sec'];
        $key = ['realm_id'=>$realmId,'action'=>'market_'.$action,'window_start'=>$ws];
        $row = $this->CI->db->get_where('rate_counters', $key)->row_array();
        if (!$row) {
            $this->CI->db->insert('rate_counters', $key + ['count'=>1,'updated_at'=>$now]); return;
        }
        if ((int)$row['count'] >= (int)$r['max']) throw new Exception('Rate limited');
        $this->CI->db->set('count','count+1',FALSE)->set('updated_at',$now)->where($key)->update('rate_counters');
    }

    private function refPrice($itemId): ?int {
        $over = $this->cfg('ref_prices');
        if (is_array($over) && isset($over[$itemId])) return (int)$over[$itemId];
        // mediana de últimas 50 trades
        $rows = $this->CI->db->order_by('created_at','DESC')->limit(50)->get_where('market_trades',['item_id'=>$itemId])->result_array();
        if (!$rows) return null;
        $prices = array_map(function($r){ return (int)$r['price_per_unit']; }, $rows);
        sort($prices);
        $n = count($prices);
        if ($n%2==1) return $prices[intval($n/2)];
        return intval(($prices[$n/2 -1] + $prices[$n/2]) / 2);
    }

    private function assertPriceBounds($itemId, $ppu) {
        $ref = $this->refPrice($itemId);
        $minF = (float)$this->cfg('min_factor', 0.5);
        $maxF = (float)$this->cfg('max_factor', 2.0);
        if ($ref === null) {
            if (!$this->cfg('allow_without_ref', true)) throw new Exception('No reference price');
            if ($ppu < 1) throw new Exception('Price too low');
            return;
        }
        $min = (int)floor($ref * $minF);
        $max = (int)ceil($ref * $maxF);
        if ($ppu < max(1,$min) || $ppu > max($min+1, $max)) throw new Exception('Price out of bounds');
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

    public function listItem(int $realmId, string $itemId, int $qty, int $ppu): int {
        if ($qty <= 0 || $ppu <= 0) throw new Exception('Invalid qty/price');
        $this->rateBump($realmId, 'listings');
        $this->assertPriceBounds($itemId, $ppu);
        // límite de listados activos por reino
        $act = $this->CI->db->where(['seller_realm_id'=>$realmId,'status'=>0])->count_all_results('market_listings');
        $maxA = (int)$this->cfg('max_active_listings', 20);
        if ($act >= $maxA) throw new Exception('Too many active listings');
        // depósito (oro)
        $feeBps = (int)$this->cfg('deposit_bps', 50);
        $deposit = (int)floor(($qty * $ppu) * $feeBps / 10000);
        if ($deposit > 0) {
            if (!$this->CI->wallet->spend($realmId, 'gold', $deposit, 'market_deposit', 'market', null)) throw new Exception('Not enough gold for deposit');
        }
        // mover items a "escrow": restar del inventario
        $this->decInventory($realmId, $itemId, $qty);
        $exp = time() + ((int)$this->cfg('listing_hours',24))*3600;
        $this->CI->db->insert('market_listings',[
            'seller_realm_id'=>$realmId,'item_id'=>$itemId,'qty'=>$qty,'price_per_unit'=>$ppu,'deposit'=>$deposit,
            'tax_bps'=>(int)$this->cfg('fee_bps',250),'status'=>0,'created_at'=>time(),'expires_at'=>$exp
        ]);
        return (int)$this->CI->db->insert_id();
    }

    public function cancel(int $realmId, int $listingId): void {
        $row = $this->CI->db->get_where('market_listings',['id'=>$listingId])->row_array();
        if (!$row || (int)$row['status']!==0) throw new Exception('Cannot cancel');
        if ((int)$row['seller_realm_id'] !== $realmId) throw new Exception('Not your listing');
        $this->CI->db->update('market_listings',['status'=>2],['id'=>$listingId]);
        // devolver items y depósito
        $this->incInventory($realmId, $row['item_id'], (int)$row['qty']);
        if ((int)$row['deposit'] > 0) $this->CI->wallet->add($realmId, 'gold', (int)$row['deposit'], 'market_deposit_refund', 'market', $listingId);
    }

    public function buy(int $buyerRealmId, int $listingId): int {
        $this->rateBump($buyerRealmId, 'buys');
        $row = $this->CI->db->get_where('market_listings',['id'=>$listingId])->row_array();
        if (!$row || (int)$row['status']!==0) throw new Exception('Listing not available');
        if ((int)$row['seller_realm_id'] === $buyerRealmId) throw new Exception('Self trade not allowed');
        $total = (int)$row['qty'] * (int)$row['price_per_unit'];
        if (!$this->CI->wallet->spend($buyerRealmId, 'gold', $total, 'market_buy', 'market', $listingId)) throw new Exception('Buyer lacks gold');
        // fee
        $feeBps = (int)$row['tax_bps'];
        $fee = (int)floor($total * $feeBps / 10000);
        $sellerNet = max(0, $total - $fee);
        // pagar al vendedor y devolver depósito
        $this->CI->wallet->add((int)$row['seller_realm_id'], 'gold', $sellerNet, 'market_sale', 'market', $listingId);
        if ((int)$row['deposit'] > 0) $this->CI->wallet->add((int)$row['seller_realm_id'], 'gold', (int)$row['deposit'], 'market_deposit_refund', 'market', $listingId);
        // entregar items
        $this->incInventory($buyerRealmId, $row['item_id'], (int)$row['qty']);
        // registrar trade
        $this->CI->db->insert('market_trades',[
            'listing_id'=>$listingId,'item_id'=>$row['item_id'],'qty'=>$row['qty'],'price_per_unit'=>$row['price_per_unit'],
            'total_price'=>$total,'tax_paid'=>$fee,'seller_realm_id'=>$row['seller_realm_id'],'buyer_realm_id'=>$buyerRealmId,'created_at'=>time()
        ]);
        $tradeId = (int)$this->CI->db->insert_id();
        $this->CI->db->update('market_listings',['status'=>1,'buyer_realm_id'=>$buyerRealmId,'trade_id'=>$tradeId],['id'=>$listingId]);
        $this->CI->observability->inc('market.trade', ['item'=>$row['item_id']], 1);
        return $tradeId;
    }

    public function expireOld(): int {
        $now = time();
        $rows = $this->CI->db->get_where('market_listings', ['status'=>0, 'expires_at <'=>$now])->result_array();
        foreach ($rows as $r) {
            $this->CI->db->update('market_listings',['status'=>3],['id'=>$r['id']]);
            // devolver items pero NO depósito (se pierde)
            $this->incInventory((int)$r['seller_realm_id'], $r['item_id'], (int)$r['qty']);
        }
        return count($rows);
    }
}
