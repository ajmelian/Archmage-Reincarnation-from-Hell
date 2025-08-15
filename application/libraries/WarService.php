<?php defined('BASEPATH') OR exit('No direct script access allowed');

class WarService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('game');
    }

    private function cfg() { return $this->CI->config->item('game')['war'] ?? []; }

    public function declareWar($aId, $bId): int {
        $now = time();
        // evita duplicados activos
        $war = $this->getActiveWar($aId, $bId);
        if ($war) return (int)$war['id'];
        $this->CI->db->insert('wars',[
            'alliance_a_id'=>(int)$aId, 'alliance_b_id'=>(int)$bId,
            'status'=>'active','started_at'=>$now,'battles_count'=>0
        ]);
        return (int)$this->CI->db->insert_id();
    }

    public function getActiveWar($aId, $bId) {
        $row = $this->CI->db->where("(alliance_a_id=".(int)$aId." AND alliance_b_id=".(int)$bId.") OR (alliance_a_id=".(int)$bId." AND alliance_b_id=".(int)$aId.")", NULL, false)
            ->where('status','active')->order_by('id','DESC')->get('wars')->row_array();
        return $row ?: null;
    }

    public function recordBattle(array $battle) {
        // battle: fields from battles table + np_losses, loot and attacker_win
        $aId = (int)($battle['attacker_alliance_id'] ?? 0);
        $dId = (int)($battle['defender_alliance_id'] ?? 0);
        if (!$aId || !$dId) return null;
        $war = $this->getActiveWar($aId, $dId);
        if (!$war) return null; // no war
        $pointsPerNp = (int)($this->cfg()['points_per_np'] ?? 1);
        $pointsPerLand = (int)($this->cfg()['points_per_land'] ?? 10);
        $delta = ((int)$battle['defender_np_loss'] - (int)$battle['attacker_np_loss']) * $pointsPerNp;
        $landPts = ((int)$battle['land_taken']) * $pointsPerLand;
        if (!empty($battle['attacker_win'])) $delta += $landPts;
        // aplica a score_a si el atacante es alliance_a
        $isAatt = ((int)$war['alliance_a_id'] === (int)$aId);
        if ($isAatt) {
            $this->CI->db->set('score_a', 'score_a + '.(int)$delta, false);
            $this->CI->db->set('land_delta_a', 'land_delta_a + '.(int)$battle['land_taken'], false);
        } else {
            $this->CI->db->set('score_b', 'score_b + '.(int)$delta, false);
            $this->CI->db->set('land_delta_b', 'land_delta_b + '.(int)$battle['land_taken'], false);
        }
        $this->CI->db->set('battles_count', 'battles_count + 1', false)->where('id',(int)$war['id'])->update('wars');
        // vincular
        $this->CI->db->insert('war_battles',['war_id'=>(int)$war['id'],'battle_id'=>(int)$battle['id']]);
        return $war['id'];
    }

    public function scoreboard($warId) {
        $war = $this->CI->db->get_where('wars',['id'=>(int)$warId])->row_array();
        if (!$war) return null;
        $battles = $this->CI->db->order_by('id','DESC')->get_where('war_battles',['war_id'=>(int)$warId])->result_array();
        return ['war'=>$war,'battles'=>$battles];
    }
}
