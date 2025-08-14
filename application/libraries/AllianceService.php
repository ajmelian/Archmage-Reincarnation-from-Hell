<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AllianceService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->library(['Observability']);
        $this->CI->load->helper('security');
    }

    public function getRealm(int $userId) {
        return $this->CI->db->get_where('realms',['user_id'=>$userId])->row_array();
    }

    public function allianceOfRealm(int $realmId) {
        $r = $this->CI->db->select('a.*')->from('alliances a')->join('realms r','r.alliance_id=a.id','inner')->where('r.id',$realmId)->get()->row_array();
        return $r ?: null;
    }

    public function myAllianceWithRole(int $realmId) {
        $row = $this->CI->db->get_where('realms',['id'=>$realmId])->row_array();
        if (!$row || empty($row['alliance_id'])) return [null, null];
        $a = $this->CI->db->get_where('alliances',['id'=>(int)$row['alliance_id']])->row_array();
        $m = $this->CI->db->get_where('alliance_members',['alliance_id'=>$a['id'],'realm_id'=>$realmId])->row_array();
        return [$a, $m ? $m['role'] : null];
    }

    public function create(int $realmId, string $name, string $tag, string $description=''): int {
        $name = trim($name); $tag = trim($tag);
        if ($name==='' || $tag==='') throw new Exception('Name/tag required');
        if ($this->CI->db->get_where('alliances',['name'=>$name])->row_array()) throw new Exception('Name taken');
        if ($this->CI->db->get_where('alliances',['tag'=>$tag])->row_array()) throw new Exception('Tag taken');
        $this->CI->db->trans_start();
        $this->CI->db->insert('alliances',['name'=>$name,'tag'=>$tag,'description'=>$description,'created_at'=>time()]);
        $aid = (int)$this->CI->db->insert_id();
        $this->CI->db->insert('alliance_members',['alliance_id'=>$aid,'realm_id'=>$realmId,'role'=>'leader','joined_at'=>time()]);
        $this->CI->db->update('realms',['alliance_id'=>$aid],['id'=>$realmId]);
        $this->log($aid, 'create', ['leader_realm_id'=>$realmId]);
        $this->CI->db->trans_complete();
        if (!$this->CI->db->trans_status()) throw new Exception('DB error');
        return $aid;
    }

    public function invite(int $fromRealmId, int $toRealmId): int {
        [$a,$role] = $this->myAllianceWithRole($fromRealmId);
        if (!$a || !in_array($role, ['leader','officer'])) throw new Exception('Not allowed');
        $r = $this->CI->db->get_where('realms',['id'=>$toRealmId])->row_array();
        if (!$r) throw new Exception('Target realm not found');
        if (!empty($r['alliance_id'])) throw new Exception('Target already in alliance');
        $exp = time()+3*24*3600; // 3 días
        $this->CI->db->insert('alliance_invites',[
            'alliance_id'=>$a['id'],'from_realm_id'=>$fromRealmId,'to_realm_id'=>$toRealmId,'created_at'=>time(),'expires_at'=>$exp,'status'=>'pending'
        ]);
        $id = (int)$this->CI->db->insert_id();
        $this->log($a['id'],'invite',['from'=>$fromRealmId,'to'=>$toRealmId,'invite_id'=>$id]);
        return $id;
    }

    public function revokeInvite(int $fromRealmId, int $inviteId): void {
        [$a,$role] = $this->myAllianceWithRole($fromRealmId);
        if (!$a || !in_array($role, ['leader','officer'])) throw new Exception('Not allowed');
        $i = $this->CI->db->get_where('alliance_invites',['id'=>$inviteId,'alliance_id'=>$a['id']])->row_array();
        if (!$i || $i['status']!=='pending') throw new Exception('Invite not pending');
        $this->CI->db->update('alliance_invites',['status'=>'revoked'],['id'=>$inviteId]);
        $this->log($a['id'],'invite_revoked',['invite_id'=>$inviteId]);
    }

    public function myInvites(int $realmId): array {
        $now = time();
        // expire
        $this->CI->db->set('status','expired')->where(['status'=>'pending'])->where('expires_at IS NOT NULL', NULL, FALSE)->where('expires_at <', $now)->update('alliance_invites');
        return $this->CI->db->order_by('created_at','DESC')->get_where('alliance_invites',['to_realm_id'=>$realmId,'status'=>'pending'])->result_array();
    }

    public function accept(int $realmId, int $inviteId): void {
        $inv = $this->CI->db->get_where('alliance_invites',['id'=>$inviteId,'to_realm_id'=>$realmId,'status'=>'pending'])->row_array();
        if (!$inv) throw new Exception('Invite not found');
        $a = $this->CI->db->get_where('alliances',['id'=>$inv['alliance_id']])->row_array();
        if (!$a) throw new Exception('Alliance not found');
        $r = $this->CI->db->get_where('realms',['id'=>$realmId])->row_array();
        if (!$r) throw new Exception('Realm not found');
        if (!empty($r['alliance_id'])) throw new Exception('Already in alliance');
        $this->CI->db->trans_start();
        $this->CI->db->update('alliance_invites',['status'=>'accepted'],['id'=>$inviteId]);
        $this->CI->db->insert('alliance_members',['alliance_id'=>$a['id'],'realm_id'=>$realmId,'role'=>'member','joined_at'=>time()]);
        $this->CI->db->update('realms',['alliance_id'=>$a['id']],['id'=>$realmId]);
        $this->log($a['id'],'join',['realm_id'=>$realmId]);
        $this->CI->db->trans_complete();
        if (!$this->CI->db->trans_status()) throw new Exception('DB error');
    }

    public function leave(int $realmId): void {
        [$a,$role] = $this->myAllianceWithRole($realmId);
        if (!$a) throw new Exception('Not in alliance');
        // Si es líder y hay otros miembros, bloquear salida
        if ($role==='leader') {
            $count = $this->CI->db->where(['alliance_id'=>$a['id']])->count_all_results('alliance_members');
            if ($count>1) throw new Exception('Leader must transfer or disband');
        }
        $this->CI->db->trans_start();
        $this->CI->db->delete('alliance_members',['alliance_id'=>$a['id'],'realm_id'=>$realmId]);
        $this->CI->db->update('realms',['alliance_id'=>NULL],['id'=>$realmId]);
        // disband if last member
        $count = $this->CI->db->where(['alliance_id'=>$a['id']])->count_all_results('alliance_members');
        if ($count===0) {
            $this->CI->db->delete('alliances',['id'=>$a['id']]);
            $this->log($a['id'],'disband',[]);
        } else {
            $this->log($a['id'],'leave',['realm_id'=>$realmId]);
        }
        $this->CI->db->trans_complete();
    }

    public function promote(int $byRealmId, int $targetRealmId): void {
        [$a,$role] = $this->myAllianceWithRole($byRealmId);
        if (!$a || !in_array($role, ['leader'])) throw new Exception('Only leader can promote');
        $m = $this->CI->db->get_where('alliance_members',['alliance_id'=>$a['id'],'realm_id'=>$targetRealmId])->row_array();
        if (!$m) throw new Exception('Not in alliance');
        if ($m['role']==='officer') return;
        $this->CI->db->update('alliance_members',['role'=>'officer'],['id'=>$m['id']]);
        $this->log($a['id'],'promote',['target'=>$targetRealmId]);
    }

    public function demote(int $byRealmId, int $targetRealmId): void {
        [$a,$role] = $this->myAllianceWithRole($byRealmId);
        if (!$a || !in_array($role, ['leader'])) throw new Exception('Only leader can demote');
        $m = $this->CI->db->get_where('alliance_members',['alliance_id'=>$a['id'],'realm_id'=>$targetRealmId])->row_array();
        if (!$m) throw new Exception('Not in alliance');
        if ($m['role']==='member') return;
        // no permitir degradarse a sí mismo si queda sin líder
        $this->CI->db->update('alliance_members',['role'=>'member'],['id'=>$m['id']]);
        $this->log($a['id'],'demote',['target'=>$targetRealmId]);
    }

    public function transferLeadership(int $byRealmId, int $targetRealmId): void {
        [$a,$role] = $this->myAllianceWithRole($byRealmId);
        if (!$a || $role!=='leader') throw new Exception('Only leader can transfer');
        $m = $this->CI->db->get_where('alliance_members',['alliance_id'=>$a['id'],'realm_id'=>$targetRealmId])->row_array();
        if (!$m) throw new Exception('Target not in alliance');
        $this->CI->db->trans_start();
        $this->CI->db->update('alliance_members',['role'=>'officer'],['alliance_id'=>$a['id'],'realm_id'=>$byRealmId]);
        $this->CI->db->update('alliance_members',['role'=>'leader'],['id'=>$m['id']]);
        $this->log($a['id'],'transfer_leader',['from'=>$byRealmId,'to'=>$targetRealmId]);
        $this->CI->db->trans_complete();
    }

    public function kick(int $byRealmId, int $targetRealmId): void {
        [$a,$role] = $this->myAllianceWithRole($byRealmId);
        if (!$a || !in_array($role, ['leader','officer'])) throw new Exception('Not allowed');
        $m = $this->CI->db->get_where('alliance_members',['alliance_id'=>$a['id'],'realm_id'=>$targetRealmId])->row_array();
        if (!$m) throw new Exception('Target not in alliance');
        if ($m['role']==='leader') throw new Exception('Cannot kick leader');
        $this->CI->db->trans_start();
        $this->CI->db->delete('alliance_members',['id'=>$m['id']]);
        $this->CI->db->update('realms',['alliance_id'=>NULL],['id'=>$targetRealmId]);
        $this->log($a['id'],'kick',['target'=>$targetRealmId,'by'=>$byRealmId]);
        $this->CI->db->trans_complete();
    }

    public function members(int $allianceId): array {
        return $this->CI->db->select('m.*, r.user_id')->from('alliance_members m')->join('realms r','r.id=m.realm_id','left')->where('m.alliance_id',$allianceId)->order_by('m.role','ASC')->get()->result_array();
    }

    public function log(int $allianceId, string $type, array $data): void {
        $this->CI->db->insert('alliance_logs',['alliance_id'=>$allianceId,'type'=>$type,'data'=>json_encode($data),'created_at'=>time()]);
    }

    // Id de canal de chat de alianza (reutiliza chat_messages.channel_id)
    public function chatChannelId(int $allianceId): string { return 'ally_'.$allianceId; }
}
