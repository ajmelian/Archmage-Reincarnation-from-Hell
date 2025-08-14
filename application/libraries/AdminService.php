<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AdminService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('admin');
        $this->CI->load->library(['ModerationService','Wallet']);
    }

    private function adminUser(): ?array {
        $uid = (int)$this->CI->session->userdata('userId');
        if (!$uid) return null;
        $u = $this->CI->db->get_where('users',['id'=>$uid])->row_array();
        if ($u && (int)($u['is_admin'] ?? 0) === 1) return $u;
        return null;
    }

    public function requireAdmin(): array {
        $u = $this->adminUser();
        if (!$u) show_error('Forbidden', 403);
        return $u;
    }

    private function logAction(int $adminUserId, string $action, ?string $target, array $payload): void {
        $this->CI->db->insert('gm_actions',[
            'admin_user_id'=>$adminUserId,'action'=>$action,'target'=>$target,
            'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE),'created_at'=>time()
        ]);
    }

    // Reports
    public function listReports(string $status='open', int $limit=100): array {
        return $this->CI->db->order_by('created_at','DESC')->limit($limit)->get_where('moderation_reports',['status'=>$status])->result_array();
    }
    public function resolveReport(int $adminId, int $id, string $resolution, string $status='resolved'): void {
        $this->CI->moderationservice->gmResolveReport($id, $resolution, $status);
        $this->logAction($adminId, 'resolve_report', (string)$id, ['status'=>$status,'resolution'=>$resolution]);
    }

    // Mutes
    public function listMutes(int $limit=100): array {
        return $this->CI->db->order_by('expires_at','DESC')->limit($limit)->get('moderation_mutes')->result_array();
    }
    public function addMute(int $adminId, int $realmId, string $scope, int $minutes, string $reason=''): int {
        $id = $this->CI->moderationservice->gmMute($realmId, $scope, $minutes, $reason);
        $this->logAction($adminId, 'mute', (string)$realmId, ['scope'=>$scope,'minutes'=>$minutes,'reason'=>$reason,'id'=>$id]);
        return $id;
    }
    public function delMute(int $adminId, int $muteId): void {
        $this->CI->moderationservice->gmUnmute($muteId);
        $this->logAction($adminId, 'unmute', (string)$muteId, []);
    }

    // Economy quick ops
    public function adjustWallet(int $adminId, int $realmId, string $resource, int $delta, string $reason): void {
        if ($delta===0) return;
        if ($delta>0) $this->CI->wallet->add($realmId, $resource, $delta, 'gm_credit', 'admin', null);
        else $this->CI->wallet->spend($realmId, $resource, abs($delta), 'gm_debit', 'admin', null);
        $this->logAction($adminId, 'wallet_adjust', (string)$realmId, ['resource'=>$resource,'delta'=>$delta,'reason'=>$reason]);
    }

    // Logs
    public function fetchLogs(string $table, int $limit=200): array {
        $allowed = array_keys($this->CI->config->item('admin')['logs_limits'] ?? []);
        if (!in_array($table, $allowed, true)) return [];
        return $this->CI->db->order_by('created_at','DESC')->limit($limit)->get($table)->result_array();
    }

    // Users
    public function searchUsers(string $q, int $limit=50): array {
        $this->CI->db->limit($limit);
        if (is_numeric($q)) {
            $this->CI->db->where('id', (int)$q);
        } else {
            $this->CI->db->like('email', $q);
        }
        return $this->CI->db->get('users')->result_array();
    }
    public function setAdmin(int $adminId, int $userId, bool $flag): void {
        $this->CI->db->update('users',['is_admin'=>($flag?1:0)],['id'=>$userId]);
        $this->logAction($adminId, $flag?'grant_admin':'revoke_admin', (string)$userId, []);
    }
}
