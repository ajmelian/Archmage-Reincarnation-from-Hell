<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuditService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function log($action, $userId=null, $realmId=null, $meta=null) {
        $row = [
            'user_id' => $userId ? (int)$userId : null,
            'realm_id'=> $realmId ? (int)$realmId : null,
            'action'  => substr((string)$action,0,64),
            'meta'    => $meta ? (is_string($meta)?$meta:json_encode($meta)) : null,
            'ip'      => $this->CI->input->ip_address(),
            'created_at'=> time(),
        ];
        $this->CI->db->insert('audit_log', $row);
        return (int)$this->CI->db->insert_id();
    }

    public function recent($limit=100) {
        return $this->CI->db->order_by('id','DESC')->limit($limit)->get('audit_log')->result_array();
    }
}
