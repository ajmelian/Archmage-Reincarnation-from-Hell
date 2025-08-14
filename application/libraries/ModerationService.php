<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ModerationService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('moderation');
        $this->CI->load->library(['AuditLog','Observability']);
    }

    public function report(int $reporterRealmId, string $type, string $reason, ?string $targetType=null, ?string $targetId=null): int {
        if (!($this->CI->config->item('moderation')['allow_user_reports'] ?? true)) throw new Exception('Reports disabled');
        $this->CI->db->insert('mod_flags',[
            'reporter_realm_id'=>$reporterRealmId, 'type'=>$type, 'reason'=>$reason,
            'target_type'=>$targetType,'target_id'=>$targetId,
            'status'=>'pending','created_at'=>time()
        ]);
        $id = (int)$this->CI->db->insert_id();
        $this->CI->auditlog->add('report.create','mod_flag',$id,['type'=>$type]);
        $this->CI->observability->inc('mod.report', ['type'=>$type], 1);
        return $id;
    }

    public function flags($status='pending', $limit=100): array {
        return $this->CI->db->order_by('created_at','DESC')->limit($limit)->get_where('mod_flags',['status'=>$status])->result_array();
    }

    public function resolve(int $modUserId, int $flagId, string $resolution, bool $reject=false): void {
        $now = time();
        $this->CI->db->update('mod_flags',[
            'status'=>$reject?'rejected':'resolved', 'mod_user_id'=>$modUserId, 'resolution'=>$resolution, 'resolved_at'=>$now
        ], ['id'=>$flagId]);
        $this->CI->auditlog->add('report.resolve','mod_flag',$flagId,['resolution'=>$resolution,'reject'=>$reject]);
    }

    public function sanction(int $modUserId, int $targetRealmId, string $action, int $minutes, string $reason, array $meta=[]): int {
        $maxMap = [
            'mute_chat' => (int)($this->CI->config->item('moderation')['max_mute_minutes'] ?? 1440),
            'suspend_market' => (int)($this->CI->config->item('moderation')['max_market_suspension_minutes'] ?? 10080),
            'ban_arena' => 7*24*60,
            'warn' => 0,
        ];
        $minutes = max(0, min($minutes, $maxMap[$action] ?? $minutes));
        $exp = $minutes>0 ? time()+$minutes*60 : null;
        $this->CI->db->insert('mod_actions',[
            'mod_user_id'=>$modUserId,'target_realm_id'=>$targetRealmId,'action'=>$action,
            'reason'=>$reason,'created_at'=>time(),'expires_at'=>$exp,'meta'=>json_encode($meta, JSON_UNESCAPED_UNICODE),
        ]);
        $id = (int)$this->CI->db->insert_id();
        $this->CI->auditlog->add('sanction.apply','mod_action',$id,['action'=>$action,'minutes'=>$minutes,'reason'=>$reason]);
        return $id;
    }

    public function activeSanctions(int $realmId): array {
        $now = time();
        $rows = $this->CI->db->where('target_realm_id',$realmId)
            ->where('(expires_at IS NULL OR expires_at >= '.$now.')', null, false)
            ->get('mod_actions')->result_array();
        $out = ['mute_chat'=>False,'suspend_market'=>False,'ban_arena'=>False];
        foreach ($rows as $r) {
            if (isset($out[$r['action']])) $out[$r['action']] = true;
        }
        return $out;
    }

    public function expire(): int {
        $now = time();
        // nada que hacer, se evalúa en consulta
        $n = $this->CI->db->where('expires_at IS NOT NULL', null, false)->where('expires_at <', $now)->count_all_results('mod_actions');
        return $n;
    }

    public function isMutedChat(int $realmId): bool { return $this->activeSanctions($realmId)['mute_chat'] ?? false; }
    public function canTrade(int $realmId): bool { return !($this->activeSanctions($realmId)['suspend_market'] ?? false); }
}
