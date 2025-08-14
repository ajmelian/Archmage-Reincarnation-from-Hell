<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HeroProgress {

    private int $xpBase;
    private float $xpGrowth;

    public function __construct() {
        $this->xpBase = 100;
        $this->xpGrowth = 1.25; // coste de nivel ~ base * growth^(level-1)
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function gain_xp(int $realmId, string $heroId, int $xp): array {
        $row = $this->getOrCreate($realmId, $heroId);
        $row['xp'] += max(0, $xp);

        $leveled = 0;
        while ($row['xp'] >= $this->xpForLevel($row['level'])) {
            $row['xp'] -= $this->xpForLevel($row['level']);
            $row['level'] += 1;
            $row['talent_points'] += 1;
            $leveled++;
        }
        $row['updated_at'] = time();
        $this->CI->db->where('id', $row['id'])->update('hero_progress', [
            'xp'=>$row['xp'],'level'=>$row['level'],'talent_points'=>$row['talent_points'],'updated_at'=>$row['updated_at']
        ]);
        return ['level'=>$row['level'],'talent_points'=>$row['talent_points'],'leveled_up'=>$leveled];
    }

    public function allocate_talent(int $realmId, string $heroId, string $talentId): array {
        $hp = $this->getOrCreate($realmId, $heroId);
        if ($hp['talent_points'] <= 0) throw new Exception('No talent points');
        // load talent_def
        $tal = $this->CI->db->get_where('talent_def', ['id'=>$talentId])->row_array();
        if (!$tal) throw new Exception('Talent not found');
        $rankRow = $this->CI->db->get_where('hero_talents', ['realm_id'=>$realmId,'hero_id'=>$heroId,'talent_id'=>$talentId])->row_array();
        $rank = (int)($rankRow['rank'] ?? 0);
        $maxr = (int)($tal['max_rank'] ?? 1);
        if ($rank >= $maxr) throw new Exception('Talent at max rank');

        if ($rankRow) {
            $this->CI->db->where('id', $rankRow['id'])->update('hero_talents', ['rank'=>$rank+1]);
        } else {
            $this->CI->db->insert('hero_talents', ['realm_id'=>$realmId,'hero_id'=>$heroId,'talent_id'=>$talentId,'rank'=>1]);
        }
        $hp['talent_points'] -= 1;
        $this->CI->db->where('id', $hp['id'])->update('hero_progress', ['talent_points'=>$hp['talent_points'], 'updated_at'=>time()]);
        return ['talent_id'=>$talentId,'new_rank'=>$rank+1,'remaining_points'=>$hp['talent_points']];
    }

    public function getOrCreate(int $realmId, string $heroId): array {
        $row = $this->CI->db->get_where('hero_progress', ['realm_id'=>$realmId,'hero_id'=>$heroId])->row_array();
        if ($row) return $row;
        $data = [
            'realm_id'=>$realmId,'hero_id'=>$heroId,'level'=>1,'xp'=>0,'talent_points'=>0,
            'created_at'=>time(),'updated_at'=>time()
        ];
        $this->CI->db->insert('hero_progress', $data);
        $data['id'] = (int)$this->CI->db->insert_id();
        return $data;
    }

    private function xpForLevel(int $level): int {
        return (int)round($this->xpBase * pow($this->xpGrowth, max(0, $level-1)));
    }
}
