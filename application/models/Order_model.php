<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Order_model extends CI_Model {
    protected string $table = 'orders';
    public function create(array $data): int { $this->db->insert($this->table, $data); return (int)$this->db->insert_id(); }
    public function pendingByTick(int $tick): array { return $this->db->get_where($this->table, ['tick'=>$tick, 'status'=>'pending'])->result_array(); }
}
