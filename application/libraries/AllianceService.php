<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AllianceService {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function create(int $leaderRealmId, string $name, string $tag, string $desc=''): int {
        $now = time();
        $this->CI->db->insert('alliances', [
            'name'=>$name,'tag'=>$tag,'description'=>$desc,'leader_realm_id'=>$leaderRealmId,'created_at'=>$now
        ]);
        $aid = (int)$this->CI->db->insert_id();
        $this->CI->db->insert('alliance_members', [
            'alliance_id'=>$aid,'realm_id'=>$leaderRealmId,'role'=>'leader','joined_at'=>$now
        ]);
        $this->CI->db->insert('alliance_bank', ['alliance_id'=>$aid,'gold'=>0,'mana'=>0,'updated_at'=>$now]);
        $this->log($aid, 'create', $leaderRealmId, ['name'=>$name,'tag'=>$tag]);
        return $aid;
    }

    public function getAlliance(?int $allianceId): ?array {
        if (!$allianceId) return null;
        $a = $this->CI->db->get_where('alliances', ['id'=>$allianceId])->row_array();
        if (!$a) return null;
        $a['members'] = $this->CI->db->order_by('role','DESC')->get_where('alliance_members',['alliance_id'=>$a['id']])->result_array();
        $a['bank'] = $this->CI->db->get_where('alliance_bank',['alliance_id'=>$a['id']])->row_array();
        return $a;
    }

    public function realmAllianceId(int $realmId): ?int {
        $m = $this->CI->db->get_where('alliance_members', ['realm_id'=>$realmId])->row_array();
        return $m ? (int)$m['alliance_id'] : null;
    }

    public function role(int $allianceId, int $realmId): ?string {
        $m = $this->CI->db->get_where('alliance_members',['alliance_id'=>$allianceId,'realm_id'=>$realmId])->row_array();
        return $m ? (string)$m['role'] : null;
    }

    public function invite(int $actorRealmId, int $allianceId, int $toRealmId): int {
        $role = $this->role($allianceId, $actorRealmId);
        if (!in_array($role, ['leader','officer'], true)) throw new Exception('Insufficient role');
        $now = time();
        $exp = $now + 7*24*3600;
        $this->CI->db->insert('alliance_invites', [
            'alliance_id'=>$allianceId,'from_realm_id'=>$actorRealmId,'to_realm_id'=>$toRealmId,
            'status'=>'pending','created_at'=>$now,'expires_at'=>$exp
        ]);
        $id = (int)$this->CI->db->insert_id();
        $this->log($allianceId, 'invite', $actorRealmId, ['to'=>$toRealmId,'invite_id'=>$id]);
        return $id;
    }

    public function acceptInvite(int $realmId, int $inviteId): void {
        $inv = $this->CI->db->get_where('alliance_invites',['id'=>$inviteId])->row_array();
        if (!$inv || (int)$inv['to_realm_id'] !== $realmId) throw new Exception('Invite not found');
        if ($inv['status']!=='pending' || $inv['expires_at']<time()) throw new Exception('Invite not valid');
        $aid = (int)$inv['alliance_id'];
        // leave current alliance if any
        $this->leaveCurrent($realmId);
        // join
        $this->CI->db->insert('alliance_members', ['alliance_id'=>$aid,'realm_id'=>$realmId,'role'=>'member','joined_at'=>time()]);
        $this->CI->db->where('id', $inviteId)->update('alliance_invites', ['status'=>'accepted']);
        $this->log($aid, 'join', $realmId, []);
    }

    public function leave(int $realmId): void {
        $aid = $this->realmAllianceId($realmId);
        if (!$aid) return;
        $m = $this->CI->db->get_where('alliance_members',['alliance_id'=>$aid,'realm_id'=>$realmId])->row_array();
        if (!$m) return;
        if ($m['role']==='leader') {
            // ensure there is another leader or disband?
            $others = $this->CI->db->where('alliance_id',$aid)->where('realm_id !=',$realmId)->count_all_results('alliance_members');
            if ($others>0) {
                // promote first officer or member to leader
                $cand = $this->CI->db->order_by("FIELD(role,'officer','member')", '', FALSE)->limit(1)->get_where('alliance_members',['alliance_id'=>$aid,'realm_id !='=>$realmId])->row_array();
                if ($cand) $this->CI->db->where('id',$cand['id'])->update('alliance_members',['role'=>'leader']);
                $this->CI->db->where('id',$aid)->update('alliances',['leader_realm_id'=>$cand ? $cand['realm_id'] : null]);
            } else {
                // disband alliance
                $this->CI->db->delete('alliances',['id'=>$aid]);
                $this->CI->db->delete('alliance_members',['alliance_id'=>$aid]);
                $this->CI->db->delete('alliance_bank',['alliance_id'=>$aid]);
                $this->log($aid, 'disband', $realmId, []);
            }
        }
        $this->CI->db->delete('alliance_members',['id'=>$m['id']]);
        $this->log($aid, 'leave', $realmId, []);
    }

    private function leaveCurrent(int $realmId): void {
        $m = $this->CI->db->get_where('alliance_members',['realm_id'=>$realmId])->row_array();
        if ($m) $this->leave($realmId);
    }

    public function promote(int $actorRealmId, int $realmId, string $toRole): void {
        $aid = $this->realmAllianceId($actorRealmId);
        if (!$aid) throw new Exception('No alliance');
        $role = $this->role($aid, $actorRealmId);
        if ($role!=='leader') throw new Exception('Only leader can set roles');
        if (!in_array($toRole, ['member','officer','leader'], true)) throw new Exception('Invalid role');
        $m = $this->CI->db->get_where('alliance_members', ['alliance_id'=>$aid,'realm_id'=>$realmId])->row_array();
        if (!$m) throw new Exception('Target not in your alliance');
        $this->CI->db->where('id',$m['id'])->update('alliance_members',['role'=>$toRole]);
        if ($toRole==='leader') $this->CI->db->where('id',$aid)->update('alliances',['leader_realm_id'=>$realmId]);
        $this->log($aid, 'promote', $actorRealmId, ['target'=>$realmId,'role'=>$toRole]);
    }

    public function bankDeposit(int $allianceId, int $realmId, string $res, int $amount): void {
        $this->guardMember($allianceId, $realmId);
        $row = $this->CI->db->get_where('alliance_bank',['alliance_id'=>$allianceId])->row_array();
        if (!$row) { $this->CI->db->insert('alliance_bank',['alliance_id'=>$allianceId,'gold'=>0,'mana'=>0,'updated_at'=>time()]); $row=['gold'=>0,'mana'=>0]; }
        $col = ($res==='mana') ? 'mana' : 'gold';
        $this->CI->db->set($col, "$col+$amount", FALSE)->set('updated_at', time())->where('alliance_id',$allianceId)->update('alliance_bank');
        $this->log($allianceId, 'bank', $realmId, ['op'=>'deposit','res'=>$col,'amount'=>$amount]);
        // TODO: subtract from player's realm resources
    }

    public function bankWithdraw(int $allianceId, int $actorRealmId, string $res, int $amount): void {
        $role = $this->role($allianceId, $actorRealmId);
        if (!in_array($role, ['leader','officer'], true)) throw new Exception('Officers or leader only');
        $col = ($res==='mana') ? 'mana' : 'gold';
        $this->CI->db->set($col, "$col-$amount", FALSE)->set('updated_at', time())->where('alliance_id',$allianceId)->update('alliance_bank');
        $this->log($allianceId, 'bank', $actorRealmId, ['op'=>'withdraw','res'=>$col,'amount'=>$amount]);
        // TODO: add to actor's realm resources
    }

    // Diplomacy
    public function setState(int $actorRealmId, int $a1, int $a2, string $state, array $terms=[]): int {
        $aid = $this->realmAllianceId($actorRealmId);
        if ($aid !== $a1) throw new Exception('Actor not in alliance A1');
        $role = $this->role($a1, $actorRealmId);
        if (!in_array($role, ['leader','officer'], true)) throw new Exception('Insufficient role');
        if (!in_array($state, ['neutral','nap','allied','war'], true)) throw new Exception('Invalid state');

        $pair = $this->fetchDiplo($a1,$a2);
        $now = time();
        if ($pair) {
            $this->CI->db->where('id',$pair['id'])->update('diplomacy',[
                'state'=>$state,'started_at'=>$now,'ends_at'=>null,
                'terms'=>json_encode($terms, JSON_UNESCAPED_UNICODE),
                'last_changed_by'=>$actorRealmId
            ]);
            $id = (int)$pair['id'];
        } else {
            $this->CI->db->insert('diplomacy',[
                'a1_id'=>$a1,'a2_id'=>$a2,'state'=>$state,'started_at'=>$now,'terms'=>json_encode($terms, JSON_UNESCAPED_UNICODE),'last_changed_by'=>$actorRealmId
            ]);
            $id = (int)$this->CI->db->insert_id();
        }
        $this->log($a1, 'diplomacy', $actorRealmId, ['with'=>$a2,'state'=>$state,'terms'=>$terms]);
        $this->log($a2, 'diplomacy', $actorRealmId, ['with'=>$a1,'state'=>$state,'terms'=>$terms]);
        return $id;
    }

    public function addWarScore(int $diploId, string $side, int $delta, array $event=[]): void {
        $d = $this->CI->db->get_where('diplomacy',['id'=>$diploId])->row_array();
        if (!$d || $d['state']!=='war') throw new Exception('Not a war');
        $a = (int)$d['a1_id']; $b = (int)$d['a2_id'];
        if ($side==='A') $this->CI->db->set('war_score_a', 'war_score_a+'.$delta, FALSE)->where('id',$diploId)->update('diplomacy');
        else $this->CI->db->set('war_score_b', 'war_score_b+'.$delta, FALSE)->where('id',$diploId)->update('diplomacy');
        $this->CI->db->insert('war_events', [
            'diplo_id'=>$diploId,'event_type'=>$event['type'] ?? 'score_adjust',
            'payload'=>json_encode($event ?: ['delta'=>$delta,'side'=>$side], JSON_UNESCAPED_UNICODE),'created_at'=>time()
        ]);
    }

    public function fetchDiplo(int $a1, int $a2): ?array {
        $pair = $this->CI->db->get_where('diplomacy', ['a1_id'=>min($a1,$a2),'a2_id'=>max($a1,$a2)])->row_array();
        return $pair ?: null;
    }

    private function guardMember(int $allianceId, int $realmId): void {
        $m = $this->CI->db->get_where('alliance_members',['alliance_id'=>$allianceId,'realm_id'=>$realmId])->row_array();
        if (!$m) throw new Exception('Not a member');
    }

    private function log(int $allianceId, string $type, ?int $actorRealmId, $payload): void {
        $this->CI->db->insert('alliance_logs',[
            'alliance_id'=>$allianceId,'type'=>$type,'actor_realm_id'=>$actorRealmId,
            'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE),'created_at'=>time()
        ]);
    }
}
