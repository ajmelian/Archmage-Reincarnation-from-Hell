<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuditLog {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }
    public function add($action, $targetType=null, $targetId=null, array $meta=[]) {
        $uid = (int)($this->CI->session->userdata('userId') ?? 0) ?: null;
        $realm = null;
        if ($uid) {
            $r = $this->CI->db->get_where('realms',['user_id'=>$uid])->row_array();
            if ($r) $realm = (int)$r['id'];
        }
        $ip = $this->CI->input->ip_address();
        $ua = substr((string)$this->CI->input->user_agent(),0,255);
        $this->CI->db->insert('audit_log',[
            'user_id'=>$uid,'realm_id'=>$realm,'action'=>$action,'target_type'=>$targetType,'target_id'=>$targetId,
            'ip'=>$ip,'ua'=>$ua,'meta'=>json_encode($meta, JSON_UNESCAPED_UNICODE),'created_at'=>time()
        ]);
    }
}
