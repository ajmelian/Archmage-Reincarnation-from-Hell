<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admincli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
    }

    public function grant($email) {
        $u = $this->db->get_where('users',['email'=>$email])->row_array();
        if (!$u) { echo "User not found\n"; return; }
        $this->db->update('users',['is_admin'=>1],['id'=>$u['id']]);
        echo "Granted admin to {$email} (id={$u['id']})\n";
    }

    public function revoke($email) {
        $u = $this->db->get_where('users',['email'=>$email])->row_array();
        if (!$u) { echo "User not found\n"; return; }
        $this->db->update('users',['is_admin'=>0],['id'=>$u['id']]);
        echo "Revoked admin from {$email} (id={$u['id']})\n";
    }
}
