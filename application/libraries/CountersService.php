<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CountersService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('game');
    }

    public function record($attackerRealmId, $defenderRealmId, int $atkToDef, int $defToAtk, $battleId=null) {
        $this->CI->db->insert('pvp_damage',[
            'attacker_realm_id'=>$attackerRealmId,
            'defender_realm_id'=>$defenderRealmId,
            'damage_from_attacker_to_defender'=>$atkToDef,
            'damage_from_defender_to_attacker'=>$defToAtk,
            'battle_id'=>$battleId,
            'created_at'=>time(),
        ]);
    }

    public function canCounter($realmA, $realmB): bool {
        // true si B infligió más daño neto a A que A a B en las últimas 24h
        $since = time() - 24*3600;
        $db = $this->CI->db;
        $row = $db->select('
            SUM(CASE WHEN attacker_realm_id='.$db->escape($realmB).' AND defender_realm_id='.$db->escape($realmA).' THEN damage_from_attacker_to_defender ELSE 0 END) as b_to_a,
            SUM(CASE WHEN attacker_realm_id='.$db->escape($realmA).' AND defender_realm_id='.$db->escape($realmB).' THEN damage_from_attacker_to_defender ELSE 0 END) as a_to_b
        ', false)
        ->from('pvp_damage')
        ->where('created_at >=', $since)
        ->get()->row_array();
        $b_to_a = (int)($row['b_to_a'] ?? 0);
        $a_to_b = (int)($row['a_to_b'] ?? 0);
        return $b_to_a > $a_to_b;
    }
}
