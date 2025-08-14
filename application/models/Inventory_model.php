<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Inventory_model extends CI_Model {
    protected string $table = 'realm_inventory';

    public function get(int $realmId, string $itemId): ?array {
        $row = $this->db->get_where($this->table, ['realm_id'=>$realmId,'item_id'=>$itemId])->row_array();
        return $row ?: null;
    }
    public function add(int $realmId, string $itemId, int $qty): void {
        $row = $this->get($realmId, $itemId);
        if ($row) {
            $this->db->where('id', $row['id'])->update($this->table, ['qty'=>(int)$row['qty'] + $qty]);
        } else {
            $this->db->insert($this->table, ['realm_id'=>$realmId,'item_id'=>$itemId,'qty'=>$qty]);
        }
    }
    public function take(int $realmId, string $itemId, int $qty): bool {
        $row = $this->get($realmId, $itemId);
        if (!$row || (int)$row['qty'] < $qty) return FALSE;
        $new = (int)$row['qty'] - $qty;
        $this->db->where('id', $row['id'])->update($this->table, ['qty'=>$new]);
        return TRUE;
    }
    public function all(int $realmId): array {
        return $this->db->get_where($this->table, ['realm_id'=>$realmId])->result_array();
    }
}
