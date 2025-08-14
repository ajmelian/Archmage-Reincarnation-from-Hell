<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ModerationService {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('moderation');
    }

    public function isMuted(int $realmId, string $scope): bool {
        $now = time();
        $this->CI->db->where('realm_id', $realmId);
        $this->CI->db->where_in('scope', [$scope, 'all']);
        $this->CI->db->where('expires_at >', $now);
        $row = $this->CI->db->get('moderation_mutes')->row_array();
        return (bool)$row;
    }

    public function assertCanChat(int $realmId, string $channelType): void {
        $scope = ($channelType==='alliance') ? 'chat_alliance' : 'chat_global';
        if ($this->isMuted($realmId, $scope)) throw new Exception('Muted');
    }

    public function assertCanDM(int $realmId): void {
        if ($this->isMuted($realmId, 'dm')) throw new Exception('Muted');
    }

    public function checkRate(int $realmId, string $action): void {
        $cfg = $this->CI->config->item('moderation');
        $rl = $cfg['rate_limits'][$action] ?? null;
        if (!$rl) return;
        $win = (int)$rl['window_sec'];
        $max = (int)$rl['max'];
        $now = time();
        $ws = (int)floor($now / $win) * $win;
        $key = ['realm_id'=>$realmId,'action'=>$action,'window_start'=>$ws];
        $row = $this->CI->db->get_where('rate_counters',$key)->row_array();
        if (!$row) {
            $this->CI->db->insert('rate_counters', $key + ['count'=>1,'updated_at'=>$now]);
            return;
        }
        if ((int)$row['count'] >= $max) throw new Exception('Rate limited');
        $this->CI->db->set('count','count+1',FALSE)->set('updated_at',$now)->where($key)->update('rate_counters');
    }

    public function filterText(string $text): array {
        $cfg = $this->CI->config->item('moderation');
        $rej = (bool)($cfg['reject_on_badword'] ?? true);
        $list = $cfg['badwords'] ?? [];
        // DB words
        $rows = $this->CI->db->get('moderation_badwords')->result_array();
        foreach ($rows as $r) $list[] = $r['token'];
        $bad = [];
        foreach ($list as $w) {
            if ($w==='' ) continue;
            if (stripos($text, $w) !== false) $bad[] = $w;
        }
        if ($bad) {
            if ($rej) return [false, 'Contenido no permitido'];
            // mask
            $masked = $text;
            foreach ($bad as $w) {
                $masked = preg_replace('/'+preg_quote($w, '/')+' /i', '****', $masked);
            }
            return [true, $masked];
        }
        return [true, $text];
    }

    public function block(int $blocker, int $blocked): void {
        if ($blocker===$blocked) return;
        $exists = $this->CI->db->get_where('moderation_blocks',['blocker_realm_id'=>$blocker,'blocked_realm_id'=>$blocked])->row_array();
        if ($exists) return;
        $this->CI->db->insert('moderation_blocks',['blocker_realm_id'=>$blocker,'blocked_realm_id'=>$blocked,'created_at'=>time()]);
    }

    public function unblock(int $blocker, int $blocked): void {
        $this->CI->db->delete('moderation_blocks',['blocker_realm_id'=>$blocker,'blocked_realm_id'=>$blocked]);
    }

    public function isBlocked(int $blocker, int $blocked): bool {
        $row = $this->CI->db->get_where('moderation_blocks',['blocker_realm_id'=>$blocker,'blocked_realm_id'=>$blocked])->row_array();
        return (bool)$row;
    }

    // If target (recipient) has blocked sender, DM should be prevented
    public function recipientBlocksSender(int $sender, int $recipient): bool {
        return $this->isBlocked($recipient, $sender);
    }

    public function report(int $reporterRealmId, string $targetType, int $targetId, string $reason=''): int {
        $this->CI->db->insert('moderation_reports',[
            'reporter_realm_id'=>$reporterRealmId,'target_type'=>$targetType,'target_id'=>$targetId,
            'reason'=>$reason ?: null,'status'=>'open','created_at'=>time()
        ]);
        return (int)$this->CI->db->insert_id();
    }

    // GM helpers via CLI or admin controller
    public function gmMute(int $realmId, string $scope, int $minutes, string $reason=''): int {
        $until = time() + max(1,$minutes)*60;
        $this->CI->db->insert('moderation_mutes',[
            'realm_id'=>$realmId,'scope'=>$scope,'reason'=>$reason ?: null,'expires_at'=>$until,'created_at'=>time()
        ]);
        return (int)$this->CI->db->insert_id();
    }
    public function gmUnmute(int $muteId): void {
        $this->CI->db->delete('moderation_mutes',['id'=>$muteId]);
    }
    public function gmListReports(string $status='open', int $limit=100): array {
        return $this->CI->db->order_by('created_at','DESC')->limit($limit)->get_where('moderation_reports',['status'=>$status])->result_array();
    }
    public function gmResolveReport(int $id, string $resolution, string $status='resolved'): void {
        $this->CI->db->update('moderation_reports',['status'=>$status,'resolution'=>$resolution,'resolved_at'=>time()],[ 'id'=>$id ]);
    }

    public function cleanup(): array {
        $now = time();
        $this->CI->db->where('expires_at <', $now)->delete('moderation_mutes');
        $m = $this->CI->db->affected_rows();
        // rate counters older than 1 day
        $limit = $now - 86400;
        $this->CI->db->where('updated_at <', $limit)->delete('rate_counters');
        $r = $this->CI->db->affected_rows();
        return ['mutes_deleted'=>$m, 'rate_deleted'=>$r];
    }
}
