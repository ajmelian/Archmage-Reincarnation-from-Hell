<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Seccli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
    }

    public function unlock($email) {
        $u = $this->db->get_where('users',['email'=>$email])->row_array();
        if (!$u) { echo "User not found\n"; return; }
        $this->db->update('users',['login_attempts'=>0,'locked_until'=>null],['id'=>$u['id']]);
        echo "Unlocked {$email}\n";
    }

    public function show($email) {
        $u = $this->db->get_where('users',['email'=>$email])->row_array();
        if (!$u) { echo "User not found\n"; return; }
        echo json_encode(['attempts'=>$u['login_attempts'],'locked_until'=>$u['locked_until'],'totp_enabled'=>$u['totp_enabled']], JSON_PRETTY_PRINT).PHP_EOL;
    }
}
