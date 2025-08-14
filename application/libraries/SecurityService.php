<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SecurityService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('security');
    }

    private function cfg($path, $default=null) {
        $cfg = $this->CI->config->item('security_ext') ?? [];
        $val = $cfg;
        foreach (explode('.', $path) as $p) {
            $val = $val[$p] ?? null;
            if ($val === null) return $default;
        }
        return $val;
    }

    private function rateBump($subjectKey, $windowSec, $max) {
        $now = time();
        $ws = (int)floor($now / $windowSec) * $windowSec;
        $row = $this->CI->db->get_where('rate_counters',['realm_id'=>crc32($subjectKey),'action'=>'auth','window_start'=>$ws])->row_array();
        if (!$row) {
            $this->CI->db->insert('rate_counters',['realm_id'=>crc32($subjectKey),'action'=>'auth','window_start'=>$ws,'count'=>1,'updated_at'=>$now]);
            return;
        }
        if ((int)$row['count'] >= $max) throw new Exception('Rate limited');
        $this->CI->db->set('count','count+1',FALSE)->set('updated_at',$now)->where(['realm_id'=>crc32($subjectKey),'action'=>'auth','window_start'=>$ws])->update('rate_counters');
    }

    public function rateCheckLogin(string $email, string $ip) {
        $ri = $this->cfg('login.rate_ip.window_sec',60);
        $mi = $this->cfg('login.rate_ip.max',30);
        $ru = $this->cfg('login.rate_user.window_sec',60);
        $mu = $this->cfg('login.rate_user.max',20);
        $this->rateBump('ip:'.$ip, $ri, $mi);
        $this->rateBump('user:'.$email, $ru, $mu);
    }

    public function handleFailedLogin(array $user): void {
        $max = (int)$this->cfg('login.max_attempts',8);
        $lockMinutes = (int)$this->cfg('login.lock_minutes',10);
        $now = time();
        $attempts = (int)($user['login_attempts'] ?? 0) + 1;
        $locked = null;
        if ($attempts >= $max) {
            $locked = $now + $lockMinutes*60;
            $attempts = 0;
        }
        $this->CI->db->update('users',[
            'login_attempts'=>$attempts,
            'locked_until'=>$locked
        ],['id'=>$user['id']]);
    }

    public function isLocked(array $user): bool {
        $lu = (int)($user['locked_until'] ?? 0);
        return ($lu && $lu > time());
    }
}
