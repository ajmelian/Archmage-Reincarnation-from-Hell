<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Engine {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->library('TalentTree');
    }

    /** Calcula daño simple A vs B con modificadores de talentos/sets compilados */
    public function duel(int $attackerRealmId, int $defenderRealmId, array $armyA, array $armyB): array {
        // army: ['units'=>[['id'=>'swordsman','atk'=>10,'def'=>5,'qty'=>100], ...]]
        $bonA = $this->CI->talenttree->getCompiled($attackerRealmId, 'combat');
        $bonB = $this->CI->talenttree->getCompiled($defenderRealmId, 'combat');

        $scoreA = 0; $scoreB = 0;
        foreach ($armyA['units'] as $u) {
            $atk = ($u['atk'] + (float)($bonA['unit_attack_flat'] ?? 0)) * (1.0 + (float)($bonA['attack_pct'] ?? 0));
            $def = ($u['def'] + (float)($bonA['unit_defense_flat'] ?? 0)) * (1.0 + (float)($bonA['defense_pct'] ?? 0));
            $scoreA += ($atk*0.7 + $def*0.3) * (int)$u['qty'];
        }
        foreach ($armyB['units'] as $u) {
            $atk = ($u['atk'] + (float)($bonB['unit_attack_flat'] ?? 0)) * (1.0 + (float)($bonB['attack_pct'] ?? 0));
            $def = ($u['def'] + (float)($bonB['unit_defense_flat'] ?? 0)) * (1.0 + (float)($bonB['defense_pct'] ?? 0));
            $scoreB += ($atk*0.7 + $def*0.3) * (int)$u['qty'];
        }

        $result = ($scoreA===$scoreB) ? 'draw' : (($scoreA>$scoreB)?'A':'B');
        return ['result'=>$result,'scoreA'=>(int)round($scoreA),'scoreB'=>(int)round($scoreB),'bonA'=>$bonA,'bonB'=>$bonB];
    }
}
