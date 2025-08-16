<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GdprService {
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); }

    private function hasTable($t){ return $this->CI->db->table_exists($t); }

    public function exportUserData($userId) {
        $out = ['generated_at'=>time(),'user_id'=>(int)$userId, 'sections'=>[]];
        // users
        if ($this->hasTable('users')) {
            $u = $this->CI->db->get_where('users',['id'=>(int)$userId])->row_array();
            if ($u) $out['sections']['user'] = $u;
        }
        // realms
        if ($this->hasTable('realms')) {
            $rs = $this->CI->db->get_where('realms',['user_id'=>(int)$userId])->result_array();
            $out['sections']['realms'] = $rs;
        }
        // notifications
        if ($this->hasTable('notifications')) {
            $ns = $this->CI->db->order_by('id','DESC')->limit(1000)->get_where('notifications',['user_id'=>(int)$userId])->result_array();
            $out['sections']['notifications'] = $ns;
        }
        // anticheat events/sanctions
        if ($this->hasTable('anticheat_events')) {
            $ev = $this->CI->db->order_by('id','DESC')->limit(1000)->get_where('anticheat_events',['user_id'=>(int)$userId])->result_array();
            $out['sections']['anticheat_events'] = $ev;
        }
        if ($this->hasTable('sanctions')) {
            $sn = $this->CI->db->order_by('id','DESC')->limit(1000)->get_where('sanctions',['user_id'=>(int)$userId])->result_array();
            $out['sections']['sanctions'] = $sn;
        }
        // battles participated (si existen columnas realm_id->user)
        if ($this->hasTable('battles') && $this->hasTable('realms')) {
            $rids = $this->CI->db->select('id')->get_where('realms',['user_id'=>(int)$userId])->result_array();
            $ids = array_map(function($r){ return (int)$r['id']; }, $rids);
            if ($ids) {
                $this->CI->db->group_start()->where_in('att_realm_id',$ids)->or_where_in('def_realm_id',$ids)->group_end();
                $bt = $this->CI->db->order_by('id','DESC')->limit(1000)->get('battles')->result_array();
                $out['sections']['battles_related'] = $bt;
            }
        }
        return $out;
    }

    public function anonymizeUser($userId) {
        $this->CI->db->trans_start();
        // users: email anon, password random, role 'deleted'
        if ($this->CI->db->table_exists('users')) {
            $anonEmail = 'deleted+'.(int)$userId.'@example.invalid';
            $randPass = bin2hex(random_bytes(24));
            $hash = password_hash($randPass, PASSWORD_BCRYPT);
            $this->CI->db->update('users',[
                'email'=>$anonEmail, 'password_hash'=>$hash, 'role'=>'deleted', 'email_verified_at'=>NULL
            ],['id'=>(int)$userId]);
        }
        // realms: soltar propiedad y renombrar
        if ($this->CI->db->table_exists('realms')) {
            $this->CI->db->set('name', "CONCAT(name,' (ex)')", false)
                         ->set('user_id', NULL, false)
                         ->where('user_id', (int)$userId)->update('realms');
        }
        $this->CI->db->trans_complete();
        return $this->CI->db->trans_status();
    }
}
