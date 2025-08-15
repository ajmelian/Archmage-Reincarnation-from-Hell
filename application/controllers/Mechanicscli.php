<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Mechanicscli extends CI_Controller { public function __construct(){ parent::__construct(); if(!is_cli()) show_404(); 
    public function prebattle_demo() {
        $this->load->library('PreBattleService');
        $payload = [
            'battle_id'=>123,
            'attacker'=>['realm_id'=>1,'np'=>20000],
            'defender'=>['realm_id'=>2,'np'=>8000,'barrier_pct'=>0.6,'color_resists'=>['red'=>0.25]],
            'attack_spell'=>['id'=>100,'name'=>'Stun','color'=>'red','base_success'=>0.65],
            'attack_item'=>['id'=>10,'name'=>'Sunray','base_success'=>1.0],
            'is_counter'=>true
        ];
        $res = $this->prebattleservice->resolve($payload);
        $this->load->library('BattlePolicy');
        $res['loot_modifier'] = $this->battlepolicy->lootModifier($payload['attacker']['np'],$payload['defender']['np'],true);
        echo json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).\"\\n\";
    }
}
    public function prebattle_demo() {
        $this->load->library('PreBattleService');
        $payload = [
            'battle_id'=>123,
            'attacker'=>['realm_id'=>1,'np'=>20000],
            'defender'=>['realm_id'=>2,'np'=>8000,'barrier_pct'=>0.6,'color_resists'=>['red'=>0.25]],
            'attack_spell'=>['id'=>100,'name'=>'Stun','color'=>'red','base_success'=>0.65],
            'attack_item'=>['id'=>10,'name'=>'Sunray','base_success'=>1.0],
            'is_counter'=>true
        ];
        $res = $this->prebattleservice->resolve($payload);
        $this->load->library('BattlePolicy');
        $res['loot_modifier'] = $this->battlepolicy->lootModifier($payload['attacker']['np'],$payload['defender']['np'],true);
        echo json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).\"\\n\";
    }
}