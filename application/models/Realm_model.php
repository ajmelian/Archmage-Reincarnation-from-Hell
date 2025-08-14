<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Realm_model extends CI_Model {
    protected string $table = 'realms';

    public function getOrCreate(int $userId): array {
        $row = $this->db->get_where($this->table, ['user_id'=>$userId])->row_array();
        if ($row) return $row;
        $state = [
            'resources' => ['gold'=>1000,'mana'=>500,'research'=>0,'land'=>50],
            'buildings' => ['mine'=>10,'lab'=>5,'barracks'=>2,'mana_well'=>5],
            'army' => [],
            'researchCompleted' => [],
            'researchProgress' => []
        ];
        $data = [
            'user_id'=>$userId,
            'name'=>'New Realm',
            'race'=>'human',
            'created_at'=>time(),
            'state'=>json_encode($state, JSON_UNESCAPED_UNICODE)
        ];
        $this->db->insert($this->table, $data);
        $data['id'] = (int)$this->db->insert_id();
        return $data;
    }

    public function loadState(array $realm): array {
        $state = $realm['state'] ? json_decode($realm['state'], true) : [];
        if (!is_array($state)) $state = [];
        $state += ['resources'=>['gold'=>0,'mana'=>0,'research'=>0,'land'=>0],'buildings'=>[],'army'=>[],'researchCompleted'=>[],'researchProgress'=>[]];
        return $state;
    }

    public function saveState(int $realmId, array $state): void {
        $this->db->where('id', $realmId)->update($this->table, ['state'=>json_encode($state, JSON_UNESCAPED_UNICODE)]);
    }
}
