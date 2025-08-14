<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Leaderboard extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index() {
        $tick = (int)($this->input->get('tick') ?: 0);
        $row = $this->db->get_where('leaderboard_cache', ['tick'=>$tick])->row_array();
        if (!$row) {
            $data = $this->generate($tick);
            $this->db->replace('leaderboard_cache', ['tick'=>$tick,'data'=>json_encode($data, JSON_UNESCAPED_UNICODE),'generated_at'=>time()]);
        } else {
            $data = json_decode($row['data'] ?? '[]', true) ?: [];
        }
        $this->load->view('leaderboard/index', ['rows'=>$data, 'tick'=>$tick]);
    }

    private function generate(int $tick): array {
        $rows = $this->db->get('realms')->result_array();
        $units = $this->db->get('unit_def')->result_array();
        $uCost = []; foreach ($units as $u) { $uCost[$u['id']] = (int)($u['cost'] ?? 0); }
        $out = [];
        foreach ($rows as $r) {
            $s = $r['state'] ? json_decode($r['state'], true) : [];
            $gold = (int)($s['resources']['gold'] ?? 0);
            $mana = (int)($s['resources']['mana'] ?? 0);
            $land = (int)($s['resources']['land'] ?? 0);
            $army = 0;
            if (!empty($s['army'])) {
                foreach ($s['army'] as $uid=>$qty) { $army += ((int)$qty) * ($uCost[$uid] ?? 0); }
            }
            $nw = $gold + ($mana*2) + ($land*5) + $army;
            $out[] = ['realm_id'=>(int)$r['id'],'name'=>$r['name'],'networth'=>$nw,'gold'=>$gold,'mana'=>$mana,'land'=>$land,'army_value'=>$army];
        }
        usort($out, function($a,$b){ return $b['networth'] <=> $a['networth']; });
        return array_slice($out, 0, 100);
    }
}
