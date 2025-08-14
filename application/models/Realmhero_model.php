<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Realmhero_model extends CI_Model {
    protected string $table = 'realm_heroes';
    public function forRealm(int $realmId): array {
        return $this->db->get_where($this->table, ['realm_id'=>$realmId])->result_array();
    }
    public function add(int $realmId, string $heroId, array $stats=[]): int {
        $this->db->insert($this->table, [
            'realm_id'=>$realmId,'hero_id'=>$heroId,'level'=>1,
            'stats'=>json_encode($stats, JSON_UNESCAPED_UNICODE),
            'created_at'=>time()
        ]);
        return (int)$this->db->insert_id();
    }
}
