<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AntiCheatService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('anticheat');
    }

    private function cfg() { return $this->CI->config->item('anticheat') ?? []; }

    private function hasColumn($table, $column): bool {
        $q = $this->CI->db->query('SHOW COLUMNS FROM `'.$table.'` LIKE '.$this->CI->db->escape($column));
        return $q->row_array() ? true : false;
    }

    public function logSession($userId, $ip, $ua) {
        if (!$userId) return;
        $this->CI->db->insert('session_log',[
            'user_id'=>(int)$userId,'ip'=>$ip,'ua'=>substr((string)$ua,0,250),'created_at'=>time()
        ]);
        $this->detectMultiAccount($userId, $ip);
    }

    public function detectMultiAccount($userId, $ip) {
        $since = time() - 24*3600;
        $rows = $this->CI->db->select('DISTINCT user_id')->where('ip',$ip)->where('created_at >=',$since)->get('session_log')->result_array();
        $unique = 0; foreach ($rows as $r) if ((int)$r['user_id']>0) $unique++;
        $thr = (int)($this->cfg()['multi_account_ip_threshold'] ?? 3);
        if ($unique >= $thr) {
            $this->CI->db->insert('anticheat_events',[
                'user_id'=>(int)$userId,'type'=>'multi_ip','severity'=>3,
                'meta'=>json_encode(['ip'=>$ip,'unique_users'=>$unique]),'created_at'=>time()
            ]);
        }
    }

    public function logTransfer($fromRealmId, $toRealmId, $resource, $amount) {
        $this->CI->db->insert('transfers_log',[
            'from_realm_id'=>(int)$fromRealmId,'to_realm_id'=>(int)$toRealmId,
            'resource'=>$resource,'amount'=>(int)$amount,'created_at'=>time()
        ]);
        return $this->checkTransferLimits($fromRealmId, $toRealmId);
    }

    public function checkTransferLimits($fromRealmId, $toRealmId) {
        $cfg = $this->cfg()['transfer_limits'] ?? [];
        $since = time() - 24*3600;
        $count = $this->CI->db->where('from_realm_id',(int)$fromRealmId)->where('to_realm_id',(int)$toRealmId)->where('created_at >=',$since)->count_all_results('transfers_log');
        $sum = $this->CI->db->select_sum('amount','s')->where('from_realm_id',(int)$fromRealmId)->where('to_realm_id',(int)$toRealmId)->where('created_at >=',$since)->get('transfers_log')->row_array();
        $sumv = (int)($sum['s'] ?? 0);
        $violations = [];
        if ($count > (int)($cfg['per_pair_daily_count'] ?? 5)) $violations[] = 'count';
        if ($sumv  > (int)($cfg['per_pair_daily_amount'] ?? 100000)) $violations[] = 'amount';
        if ($violations) {
            $this->CI->db->insert('anticheat_events',[
                'type'=>'transfer_limit','severity'=>2,'meta'=>json_encode(['from'=>$fromRealmId,'to'=>$toRealmId,'count'=>$count,'sum'=>$sumv,'violations'=>$violations]),'created_at'=>time()
            ]);
        }
        return ['count'=>$count,'sum'=>$sumv,'violations'=>$violations];
    }

    public function assertAllowed($userId, $action) {
        // Comprueba sanciones activas del tipo de acción
        $now = time();
        $row = $this->CI->db->where('user_id',(int)$userId)->where('type',$action)->where('revoked_at', NULL)->where("(expires_at IS NULL OR expires_at >= $now)", NULL, false)->get('sanctions')->row_array();
        if ($row) return [false, $row];
        return [true, null];
    }

    public function imposeSanction($userId, $type, $hours=null, $reason=null) {
        $exp = $hours ? time() + $hours*3600 : NULL;
        $this->CI->db->insert('sanctions',[
            'user_id'=>(int)$userId, 'type'=>$type, 'reason'=>$reason, 'created_at'=>time(), 'expires_at'=>$exp
        ]);
        return (int)$this->CI->db->insert_id();
    }

    public function revokeSanction($id) {
        $this->CI->db->update('sanctions',['revoked_at'=>time()],['id'=>(int)$id]);
        return $this->CI->db->affected_rows()>=0;
    }
}
