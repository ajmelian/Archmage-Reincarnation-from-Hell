<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MarketService {
    private array $cfg;

    public function __construct() {
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->config('market');
        $CI->load->library('Wallet');
        $CI->load->library('Inventory');
        $this->cfg = $CI->config->item('market') ?? [];
        $this->CI = $CI;
    }

    public function createListing(int $realmId, string $itemId, int $qty, int $ppu): int {
        $this->guardFloor($ppu);
        $this->guardDailyLimit($realmId, 'sell');
        $this->CI->inventory->remove($realmId, $itemId, max(1,$qty), 'market_escrow');
        $now = time();
        $exp = $now + (int)($this->cfg['listing_lifetime'] ?? 259200);
        $data = [
            'realm_id'=>$realmId,'item_id'=>$itemId,'qty'=>max(1,$qty),
            'price_per_unit'=>max($this->floor(),$ppu),
            'currency'=>'gold','tax_rate'=>(float)($this->cfg['tax_rate'] ?? 0.05),
            'status'=>'active','sold_qty'=>0,'buyer_realm_id'=>null,
            'created_at'=>$now,'expires_at'=>$exp,
        ];
        $this->CI->db->insert('market_listings', $data);
        $id = (int)$this->CI->db->insert_id();
        $this->log('listing', $realmId, $id, $data);
        return $id;
    }

    public function buy(int $buyerRealmId, int $listingId, int $qty): array {
        $this->guardDailyLimit($buyerRealmId, 'buy');
        $l = $this->CI->db->get_where('market_listings', ['id'=>$listingId])->row_array();
        if (!$l) throw new Exception('Listing not found');
        if ($l['status'] !== 'active') throw new Exception('Listing not active');
        if ($l['expires_at'] < time()) { $this->expire($l['id']); throw new Exception('Listing expired'); }
        $remain = (int)$l['qty'] - (int)$l['sold_qty'];
        $qty = max(1, min($qty, $remain));
        if ($qty <= 0) throw new Exception('No quantity left');
        $total = $qty * (int)$l['price_per_unit'];
        $tax = (float)$l['tax_rate'] * $total;
        $pay = (int)ceil($total + $tax);
        $this->CI->wallet->spend($buyerRealmId, 'gold', $pay, 'market_buy', 'listing', (int)$l['id']);
        $this->CI->db->trans_begin();
        try {
            $this->CI->db->set('sold_qty', 'sold_qty+'.$qty, FALSE)
                ->where('id', $l['id'])->update('market_listings');
            $status = ($qty === $remain) ? 'sold' : 'partial';
            $this->CI->db->where('id', $l['id'])->update('market_listings', ['status'=>$status,'buyer_realm_id'=>$buyerRealmId]);
            // Transfer items/currency should be implemented here
            $this->log('buy', $buyerRealmId, $l['id'], ['qty'=>$qty,'total'=>$total,'tax'=>$tax,'pay'=>$pay]);
            $this->CI->db->trans_commit();
        } catch (Throwable $e) {
            $this->CI->db->trans_rollback();
            throw $e;
        }
        return ['status'=>$status,'qty'=>$qty,'pay'=>$pay];
    }

    public function cancel(int $realmId, int $listingId): void {
        $l = $this->CI->db->get_where('market_listings', ['id'=>$listingId])->row_array();
        if (!$l || (int)$l['realm_id'] !== $realmId) throw new Exception('Not your listing');
        if ($l['status'] !== 'active') throw new Exception('Cannot cancel this listing');
        $this->CI->db->where('id', $listingId)->update('market_listings', ['status'=>'canceled']);
        $l = $this->CI->db->get_where('market_listings', ['id'=>$listingId])->row_array();
        if ($l) {
            $remain = (int)$l['qty'] - (int)$l['sold_qty'];
            if ($remain>0) $this->CI->inventory->add($realmId, $l['item_id'], $remain, 'market_return', 'listing', (int)$l['id']);
        }
        $this->log('cancel', $realmId, $listingId, []);
    }

    public function expire(int $listingId): void {
        $this->CI->db->where('id', $listingId)->update('market_listings', ['status'=>'expired']);
        $l = $this->CI->db->get_where('market_listings', ['id'=>$listingId])->row_array();
        if ($l) {
            $remain = (int)$l['qty'] - (int)$l['sold_qty'];
            if ($remain>0) $this->CI->inventory->add((int)$l['realm_id'], $l['item_id'], $remain, 'market_return', 'listing', (int)$l['id']);
        }
        $this->log('expire', null, $listingId, []);
    }

    public function cleanupExpired(): int {
        $now = time();
        $this->CI->db->where('status','active')->where('expires_at <', $now)->update('market_listings', ['status'=>'expired']);
        return $this->CI->db->affected_rows();
    }

    private function guardFloor(int $ppu): void {
        if ($ppu < $this->floor()) throw new Exception('Price below floor');
    }
    private function floor(): int { return (int)($this->cfg['min_price_floor'] ?? 1); }

    private function guardDailyLimit(int $realmId, string $kind): void {
        $since = strtotime('today 00:00:00');
        if ($kind === 'sell') {
            $count = $this->CI->db->where('type','listing')->where('realm_id',$realmId)->where('created_at >=', $since)->count_all_results('market_logs');
            $max = (int)($this->cfg['max_daily_sell_listings'] ?? 50);
            if ($count >= $max) throw new Exception('Daily sell listing limit reached');
        } else {
            $count = $this->CI->db->where('type','buy')->where('realm_id',$realmId)->where('created_at >=', $since)->count_all_results('market_logs');
            $max = (int)($this->cfg['max_daily_buy_operations'] ?? 200);
            if ($count >= $max) throw new Exception('Daily buy operation limit reached');
        }
    }

    private function log(string $type, ?int $realmId, ?int $refId, $payload): void {
        $this->CI->db->insert('market_logs', [
            'type'=>$type,'realm_id'=>$realmId,'ref_id'=>$refId,
            'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at'=>time()
        ]);
    }
}
