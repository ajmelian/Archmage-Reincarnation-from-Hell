<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TalentTree {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /** Devuelve los talentos del héroe con sus rangos y efectos parseados. */
    public function heroTalents(int $realmId, string $heroId): array {
        $rows = $this->CI->db->select('t.talent_id, t.rank, d.effects')
            ->from('hero_talents t')
            ->join('talent_def d', 'd.id = t.talent_id', 'left')
            ->where(['t.realm_id'=>$realmId,'t.hero_id'=>$heroId])
            ->get()->result_array();
        $out = [];
        foreach ($rows as $r) {
            $eff = json_decode($r['effects'] ?? '{}', true) ?: [];
            $out[] = ['id'=>$r['talent_id'],'rank'=>(int)$r['rank'],'effects'=>$eff];
        }
        return $out;
    }

    /** Agrega los efectos de talentos por tipo (p.ej. attack_bonus, mana_bonus...) */
    public function aggregateBonuses(array $talents): array {
        $bonuses = []; // key => [values...]
        foreach ($talents as $t) {
            $rank = max(0, (int)$t['rank']);
            $eff  = is_array($t['effects']) ? $t['effects'] : [];
            foreach ($eff as $k=>$v) {
                $perRank = is_array($v) && isset($v['per_rank']) ? (float)$v['per_rank'] : 0.0;
                $base    = is_array($v) && isset($v['base']) ? (float)$v['base'] : 0.0;
                $val = $base + $perRank * $rank;
                if (!isset($bonuses[$k])) $bonuses[$k] = [];
                $bonuses[$k][] = $val;
            }
        }
        return $bonuses;
    }
}
