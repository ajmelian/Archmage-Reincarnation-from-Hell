<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    private function ensure(int $realmId, string $itemId): void {
        $row = $this->CI->db->get_where('inventories', ['realm_id'=>$realmId,'item_id'=>$itemId])->row_array();
        if (!$row) $this->CI->db->insert('inventories', ['realm_id'=>$realmId,'item_id'=>$itemId,'qty'=>0,'updated_at'=>time()]);
    }

    public function qty(int $realmId, string $itemId): int {
        $this->ensure($realmId,$itemId);
        $row = $this->CI->db->get_where('inventories', ['realm_id'=>$realmId,'item_id'=>$itemId])->row_array();
        return (int)$row['qty'];
    }

    public function add(int $realmId, string $itemId, int $amount, string $reason, string $refType=null, int $refId=null): void {
        $this->ensure($realmId,$itemId);
        $this->CI->db->trans_start();
        $this->CI->db->set('qty', 'qty+'.$amount, FALSE)->set('updated_at', time())->where(['realm_id'=>$realmId,'item_id'=>$itemId])->update('inventories');
        $this->CI->db->insert('inventory_logs', ['realm_id'=>$realmId,'item_id'=>$itemId,'delta'=>$amount,'reason'=>$reason,'ref_type'=>$refType,'ref_id'=>$refId,'created_at'=>time()]);
        $this->CI->db->trans_complete();
    }

    public function remove(int $realmId, string $itemId, int $amount, string $reason, string $refType=null, int $refId=null): void {
        $this->ensure($realmId,$itemId);
        $row = $this->CI->db->get_where('inventories', ['realm_id'=>$realmId,'item_id'=>$itemId])->row_array();
        if ((int)$row['qty'] < $amount) throw new Exception('Insufficient items');
        $this->CI->db->trans_start();
        $this->CI->db->set('qty', 'qty-'.$amount, FALSE)->set('updated_at', time())->where(['realm_id'=>$realmId,'item_id'=>$itemId])->update('inventories');
        $this->CI->db->insert('inventory_logs', ['realm_id'=>$realmId,'item_id'=>$itemId,'delta'=>-$amount,'reason'=>$reason,'ref_type'=>$refType,'ref_id'=>$refId,'created_at'=>time()]);
        $this->CI->db->trans_complete();
    }
}
