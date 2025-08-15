<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LootService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('game');
        $this->CI->load->library('BattlePolicy');
    }

    private function cfg() {
        return [
            'modes' => $this->CI->config->item('game')['battle_modes'] ?? [],
            'loot'  => $this->CI->config->item('game')['loot'] ?? [],
        ];
    }

    private function hasColumn($table, $column): bool {
        $q = $this->CI->db->query('SHOW COLUMNS FROM `'.$table.'` LIKE '.$this->CI->db->escape($column));
        return $q->row_array() ? true : false;
    }

    /**
     * Calcula botín (oro/maná) y tierras por victoria.
     * @return array ['gold'=>int,'mana'=>int,'land'=>int,'modifier'=>float]
     */
    public function computeLoot(array $ctx): array {
        // ctx: type, is_counter, attacker_np, defender_np, np_losses(att/def), attacker_win(bool)
        $cfg = $this->cfg();
        $mode = $cfg['modes'][$ctx['type']] ?? ['loot_rate'=>0.15,'land_percent'=>0.02];
        $base = (float)($cfg['loot']['base_gold_per_np'] ?? 1.0);
        $mod  = $this->CI->battlepolicy->lootModifier((int)$ctx['attacker_np'], (int)$ctx['defender_np'], !empty($ctx['is_counter']));

        $gold = 0; $mana = 0; $land = 0;
        if (!empty($ctx['attacker_win'])) {
            $gold = (int)round($ctx['np_losses']['def'] * $base * (float)$mode['loot_rate'] * $mod);
            $mana = 0; // por defecto
            // tierras: % de las tierras del defensor
            if ($this->hasColumn('realms','land')) {
                $row = $this->CI->db->select('land')->get_where('realms',['id'=>(int)$ctx['defender_realm_id']])->row_array();
                $defLand = (int)($row['land'] ?? 0);
                $land = (int)floor($defLand * (float)$mode['land_percent'] * $mod);
                if ($land < 0) $land = 0;
                if ($land > $defLand) $land = $defLand;
            }
        }
        $gold = max((int)($cfg['loot']['min_loot'] ?? 0), min((int)($cfg['loot']['max_loot'] ?? 1000000), (int)$gold));
        return ['gold'=>$gold,'mana'=>$mana,'land'=>$land,'modifier'=>$mod];
    }

    /**
     * Aplica el loot y la transferencia de tierras a la BD si las columnas existen.
     */
    public function applyLoot(int $attRealmId, int $defRealmId, array $loot): array {
        $res = ['gold_applied'=>false,'land_applied'=>false];
        // oro
        if ($loot['gold'] > 0 && $this->hasColumn('realms','gold')) {
            $this->CI->db->set('gold', 'gold + '.(int)$loot['gold'], false)->where('id',$attRealmId)->update('realms');
            if ($this->CI->db->affected_rows()>=0) $res['gold_applied'] = true;
            if ($this->hasColumn('realms','gold')) {
                $this->CI->db->set('gold', 'GREATEST(0, gold - '.(int)$loot['gold'].')', false)->where('id',$defRealmId)->update('realms');
            }
        }
        // tierras
        if ($loot['land'] > 0 && $this->hasColumn('realms','land')) {
            $this->CI->db->set('land', 'land + '.(int)$loot['land'], false)->where('id',$attRealmId)->update('realms');
            $this->CI->db->set('land', 'GREATEST(0, land - '.(int)$loot['land'].')', false)->where('id',$defRealmId)->update('realms');
            $res['land_applied'] = true;
        }
        return $res;
    }
}
