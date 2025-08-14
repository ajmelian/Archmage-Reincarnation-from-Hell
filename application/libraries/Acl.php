<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Acl {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function hasRole(int $userId, string $name): bool {
        $r = $this->CI->db->get_where('roles',['name'=>$name])->row_array();
        if (!$r) return false;
        $ur = $this->CI->db->get_where('user_roles',['user_id'=>$userId,'role_id'=>$r['id']])->row_array();
        return (bool)$ur;
    }

    public function grant(int $userId, string $name): void {
        $r = $this->CI->db->get_where('roles',['name'=>$name])->row_array();
        if (!$r) { $this->CI->db->insert('roles',['name'=>$name,'created_at'=>time()]); $r = $this->CI->db->get_where('roles',['name'=>$name])->row_array(); }
        $exists = $this->CI->db->get_where('user_roles',['user_id'=>$userId,'role_id'=>$r['id']])->row_array();
        if ($exists) return;
        $this->CI->db->insert('user_roles',['user_id'=>$userId,'role_id'=>$r['id'],'granted_at'=>time()]);
    }

    public function revoke(int $userId, string $name): void {
        $r = $this->CI->db->get_where('roles',['name'=>$name])->row_array();
        if (!$r) return;
        $this->CI->db->delete('user_roles',['user_id'=>$userId,'role_id'=>$r['id']]);
    }
}
