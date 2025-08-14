<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ArenaService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('arena');
        $this->CI->load->library('Observability');
        $this->CI->load->library(['Engine','Wallet','TalentTree']);
    }

    private function activeSeason(): array {
        $now = time();
        $row = $this->CI->db->order_by('id','DESC')->get_where('arena_seasons',['active'=>1,'starts_at <='=>$now,'ends_at >='=>$now])->row_array();
        if (!$row) {
            // fallback: create a default rolling season
            $this->CI->db->insert('arena_seasons',['name'=>'Temporada Auto','starts_at'=>$now-86400,'ends_at'=>$now+30*86400,'active'=>1]);
            $row = $this->CI->db->order_by('id','DESC')->get('arena_seasons')->row_array();
        }
        return $row;
    }

    public function rating(int $realmId): array {
        $s = $this->activeSeason();
        $row = $this->CI->db->get_where('arena_ratings',['realm_id'=>$realmId,'season_id'=>$s['id']])->row_array();
        if (!$row) {
            $row = ['realm_id'=>$realmId,'season_id'=>$s['id'],'elo'=>1000,'wins'=>0,'losses'=>0,'draws'=>0,'last_match_at'=>0,'updated_at'=>0];
            $this->CI->db->insert('arena_ratings',$row);
        }
        return $row;
    }

    public function enqueue(int $realmId): int {
        $s = $this->activeSeason();
        // avoid double enqueue
        $exists = $this->CI->db->get_where('arena_queue',['season_id'=>$s['id'],'realm_id'=>$realmId])->row_array();
        if ($exists) return (int)$exists['id'];
        $r = $this->rating($realmId);
        $this->CI->db->insert('arena_queue',[
            'season_id'=>$s['id'],'realm_id'=>$realmId,'rating_snapshot'=>$r['elo'],'enqueued_at'=>time()
        ]);
        $id = (int)$this->CI->db->insert_id();
        $this->log('queue',$s['id'],$realmId,null,['rating'=>$r['elo']]);
        return $id;
    }

    public function dequeue(int $realmId): void {
        $s = $this->activeSeason();
        $this->CI->db->delete('arena_queue',['season_id'=>$s['id'],'realm_id'=>$realmId]);
    }

    public function matchmake(): int {
        $s = $this->activeSeason();
        $rows = $this->CI->db->order_by('enqueued_at','ASC')->get_where('arena_queue',['season_id'=>$s['id']])->result_array();
        $n = 0;
        $now = time();
        for ($i=0; $i < count($rows); $i++) {
            $a = $rows[$i];
            if (!$a) continue;
            // compute delta for A based on waiting time
            $delta = (int)($this->CI->config->item('arena')['search_delta'] ?? 200);
            $expand = (int)($this->CI->config->item('arena')['search_expand_sec'] ?? 60);
            $step = (int)($this->CI->config->item('arena')['expand_step'] ?? 50);
            $elapsed = max(0, $now - (int)$a['enqueued_at']);
            $delta += (int)floor($elapsed / max(1,$expand)) * $step;

            // find best B not equal A
            $best = null; $bestGap = 1e9; $bestIdx = -1;
            for ($j=$i+1; $j < count($rows); $j++) {
                $b = $rows[$j];
                if (!$b) continue;
                $gap = abs((int)$a['rating_snapshot'] - (int)$b['rating_snapshot']);
                if ($gap <= $delta && $gap < $bestGap) {
                    $best = $b; $bestGap = $gap; $bestIdx = $j;
                }
            }
            if ($best) {
                // create match immediately and resolve
                $matchId = $this->createAndResolveMatch($s['id'], $a, $best);
                // remove both from queue
                $this->CI->db->delete('arena_queue',['id'=>$a['id']]);
                $this->CI->db->delete('arena_queue',['id'=>$best['id']]);
                $rows[$i] = null; $rows[$bestIdx] = null;
                $n++;
            }
        }
        return $n;
    }

    $this->CI->observability->inc('arena.queue_pulled');
    private function createAndResolveMatch(int $seasonId, array $a, array $b): int {
        $this->CI->db->insert('arena_matches',[
            'season_id'=>$seasonId,'realm_a'=>$a['realm_id'],'realm_b'=>$b['realm_id'],
            'elo_a'=>$a['rating_snapshot'],'elo_b'=>$b['rating_snapshot'],'created_at'=>time()
        ]);
        $mid = (int)$this->CI->db->insert_id();

        // Build armies (very simple snapshot): if there is 'armies' table, take top N; else use default stub by buildings
        $armyA = $this->snapshotArmy((int)$a['realm_id']);
        $armyB = $this->snapshotArmy((int)$b['realm_id']);

        $res = $this->CI->engine->duel((int)$a['realm_id'], (int)$b['realm_id'], $armyA, $armyB);
        $winner = $res['result']; // 'A','B','draw'

        // ELO
        $new = $this->applyElo($seasonId, (int)$a['realm_id'], (int)$b['realm_id'], $winner, $a['rating_snapshot'], $b['rating_snapshot']);

        // rewards (optional)
        $rew = $this->CI->config->item('arena')['reward'] ?? [];
        if ($winner==='A') {
            if (!empty($rew['gold'])) $this->CI->wallet->add((int)$a['realm_id'], 'gold', (int)$rew['gold'], 'arena_win', 'arena', $mid);
            if (!empty($rew['mana'])) $this->CI->wallet->add((int)$a['realm_id'], 'mana', (int)$rew['mana'], 'arena_win', 'arena', $mid);
        } elseif ($winner==='B') {
            if (!empty($rew['gold'])) $this->CI->wallet->add((int)$b['realm_id'], 'gold', (int)$rew['gold'], 'arena_win', 'arena', $mid);
            if (!empty($rew['mana'])) $this->CI->wallet->add((int)$b['realm_id'], 'mana', (int)$rew['mana'], 'arena_win', 'arena', $mid);
        }

        $this->CI->db->update('arena_matches',[
            'result'=>$winner,'score_a'=>$res['scoreA'],'score_b'=>$res['scoreB'],'resolved_at'=>time()
        ], ['id'=>$mid]);
        $this->log('match',$seasonId,null,$mid,['result'=>$winner,'a'=>$a['realm_id'],'b'=>$b['realm_id'],'scores'=>[$res['scoreA'],$res['scoreB']]]);
        return $mid;
    }

    private function snapshotArmy(int $realmId): array {
        // If armies table exists, uses top 3 unit types by qty; else derive from buildings as stub
        if ($this->CI->db->table_exists('armies')) {
            $rows = $this->CI->db->order_by('qty','DESC')->limit(3)->get_where('armies',['realm_id'=>$realmId])->result_array();
            $units = [];
            foreach ($rows as $r) {
                $def = $this->CI->db->get_where('unit_def',['id'=>$r['unit_id']])->row_array();
                $units[] = ['id'=>$r['unit_id'],'atk'=>(int)($def['atk'] ?? 10),'def'=>(int)($def['def'] ?? 5),'qty'=>(int)$r['qty']];
            }
            if ($units) return ['units'=>$units];
        }
        // fallback: buildings -> basic militia
        $b = $this->CI->db->get_where('buildings',['realm_id'=>$realmId,'building_id'=>'farm'])->row_array();
        $qty = (int)($b['qty'] ?? 5) * 10;
        return ['units'=>[['id'=>'militia','atk'=>8,'def'=>6,'qty'=>$qty]]];
    }

    private function applyElo(int $seasonId, int $ra, int $rb, string $winner, int $eloA, int $eloB): array {
        $K = (int)($this->CI->config->item('arena')['k_factor'] ?? 32);
        $ea = 1.0 / (1 + pow(10, ($eloB - $eloA)/400));
        $eb = 1.0 - $ea;
        $sa = ($winner==='A') ? 1 : (($winner==='draw') ? 0.5 : 0);
        $sb = 1 - $sa;
        $na = (int)round($eloA + $K * ($sa - $ea));
        $nb = (int)round($eloB + $K * ($sb - $eb));

        $this->upsertRating($seasonId, $ra, $na, $winner==='A', $winner==='B', $winner==='draw');
        $this->upsertRating($seasonId, $rb, $nb, $winner==='B', $winner==='A', $winner==='draw');

        $this->log('elo',$seasonId,$ra,null,['from'=>$eloA,'to'=>$na]);
        $this->log('elo',$seasonId,$rb,null,['from'=>$eloB,'to'=>$nb]);
        return ['a'=>$na,'b'=>$nb];
    }

    private function upsertRating(int $seasonId, int $realmId, int $elo, bool $win, bool $loss, bool $draw): void {
        $row = $this->CI->db->get_where('arena_ratings',['realm_id'=>$realmId,'season_id'=>$seasonId])->row_array();
        if ($row) {
            $this->CI->db->where(['realm_id'=>$realmId,'season_id'=>$seasonId])->update('arena_ratings',[
                'elo'=>$elo,
                'wins'=>$row['wins'] + ($win?1:0),
                'losses'=>$row['losses'] + ($loss?1:0),
                'draws'=>$row['draws'] + ($draw?1:0),
                'last_match_at'=>time(),'updated_at'=>time()
            ]);
        } else {
            $this->CI->db->insert('arena_ratings',[
                'realm_id'=>$realmId,'season_id'=>$seasonId,'elo'=>$elo,
                'wins'=>($win?1:0),'losses'=>($loss?1:0),'draws'=>($draw?1:0),
                'last_match_at'=>time(),'updated_at'=>time()
            ]);
        }
    }

    public function leaderboard(int $limit=50): array {
        $s = $this->activeSeason();
        return $this->CI->db->order_by('elo','DESC')->limit($limit)->get_where('arena_ratings',['season_id'=>$s['id']])->result_array();
    }

    public function history(int $realmId, int $limit=50): array {
        $s = $this->activeSeason();
        return $this->CI->db->order_by('created_at','DESC')->limit($limit)
            ->get_where('arena_matches', "season_id={$s['id']} AND (realm_a={$realmId} OR realm_b={$realmId})")->result_array();
    }

    private function log(string $type, int $seasonId, ?int $realmId, ?int $matchId, array $payload): void {
        $this->CI->db->insert('arena_logs',[
            'type'=>$type,'season_id'=>$seasonId,'realm_id'=>$realmId,'match_id'=>$matchId,
            'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE),'created_at'=>time()
        ]);
    }
}
