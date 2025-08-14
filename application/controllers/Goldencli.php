<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Goldencli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
        $this->load->library(['TickRunner','TalentTree','Engine']);
        $this->load->config('talents');
    }

    private function ensureRealm(): int {
        // Crea un reino dummy si no hay
        $r = $this->db->get('realms')->row_array();
        if ($r) return (int)$r['id'];
        $this->db->insert('users',['email'=>'golden@example.com','password_hash'=>'x','created_at'=>time()]);
        $uid = (int)$this->db->insert_id();
        $this->db->insert('realms',['user_id'=>$uid,'name'=>'Golden','created_at'=>time()]);
        return (int)$this->db->insert_id();
    }

    public function run() {
        $rid = $this->ensureRealm();
        $this->testEconomyBonuses($rid);
        $this->testCombatBonuses($rid);
    }

    private function resetBonuses($rid) {
        $this->db->delete('hero_talents',['realm_id'=>$rid]);
        $this->db->delete('compiled_bonuses',['realm_id'=>$rid]);
        if ($this->db->table_exists('equipment')) $this->db->delete('equipment',['realm_id'=>$rid]);
    }

    private function testEconomyBonuses($rid) {
        echo "== Economy bonuses\n";
        $this->resetBonuses($rid);
        // Give buildings: 10 farms -> base gold = 10*5 = 50
        $this->db->delete('buildings',['realm_id'=>$rid]);
        $this->db->insert('buildings',['realm_id'=>$rid,'building_id'=>'farm','qty'=>10,'level'=>0,'updated_at'=>time()]);

        // Define two talents for hero H1 producing +10% and +20% gold
        $this->db->delete('talent_def');
        $this->db->insert('talent_def',['id'=>'gold_I','max_rank'=>1,'effects'=>json_encode(['gold_pct'=>0.10])]);
        $this->db->insert('talent_def',['id'=>'gold_II','max_rank'=>1,'effects'=>json_encode(['gold_pct'=>0.20])]);
        $this->db->delete('realm_heroes',['realm_id'=>$rid]);
        $this->db->insert('realm_heroes',['realm_id'=>$rid,'hero_id'=>'H1']);
        $this->db->insert('hero_talents',['realm_id'=>$rid,'hero_id'=>'H1','talent_id'=>'gold_I','rank'=>1]);
        $this->db->insert('hero_talents',['realm_id'=>$rid,'hero_id'=>'H1','talent_id'=>'gold_II','rank'=>1]);

        $this->talenttree->compileRealm($rid);
        $res = $this->tickrunner->runOne();
        $bal = $this->db->get_where('wallets',['realm_id'=>$rid])->row_array();
        $gold = (int)$bal['gold'];
        $expected = (int)round(50 * (1+0.10+0.20)); // stacking 'add'
        echo "Gold produced: $gold (expected $expected)\n";
    }

    private function testCombatBonuses($rid) {
        echo "== Combat bonuses\n";
        $this->resetBonuses($rid);
        // talents: +20% attack, +5 flat atk
        $this->db->delete('talent_def');
        $this->db->insert('talent_def',['id'=>'atk_pct','max_rank'=>1,'effects'=>json_encode(['attack_pct'=>0.20])]);
        $this->db->insert('talent_def',['id'=>'atk_flat','max_rank'=>1,'effects'=>json_encode(['unit_attack_flat'=>5])]);
        $this->db->delete('realm_heroes',['realm_id'=>$rid]);
        $this->db->insert('realm_heroes',['realm_id'=>$rid,'hero_id'=>'H1']);
        $this->db->insert('hero_talents',['realm_id'=>$rid,'hero_id'=>'H1','talent_id'=>'atk_pct','rank'=>1]);
        $this->db->insert('hero_talents',['realm_id'=>$rid,'hero_id'=>'H1','talent_id'=>'atk_flat','rank'=>1]);
        $this->talenttree->compileRealm($rid);

        $armyA = ['units'=>[['id'=>'inf','atk'=>10,'def'=>5,'qty'=>100]]];
        $armyB = ['units'=>[['id'=>'inf','atk'=>10,'def'=>5,'qty'=>100]]];
        $res = $this->engine->duel($rid, $rid, $armyA, $armyB);
        echo "Scores A vs B: {$res['scoreA']} vs {$res['scoreB']} (should be equal due to same bonuses)\n";
    }
}
