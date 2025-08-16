<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AntiCheatHook {
    public function log_session() {
        $CI =& get_instance();
        $CI->load->library(['session','AntiCheatService']);
        $uid = $CI->session->userdata('user_id');
        if ($uid) {
            $ip = $CI->input->ip_address();
            $ua = $CI->input->user_agent();
            $CI->anticheatservice->logSession($uid, $ip, $ua);
        }
    }
}
