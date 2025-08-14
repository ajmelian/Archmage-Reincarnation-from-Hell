<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Securitycli extends CI_Controller {
    public function __construct() { parent::__construct(); if (!is_cli()) show_404(); $this->load->library(['TwoFA']); $this->load->database(); $this->load->config('security'); }

    public function twofa_setup($userId) {
        $u = $this->db->get_where('users',['id'=>(int)$userId])->row_array();
        if (!$u) { echo "User not found\n"; return; }
        $secret = $this->twofa->randomSecret();
        $this->db->update('users',['twofa_secret'=>$secret,'twofa_enabled'=>0],['id'=>$u['id']]);
        $uri = $this->twofa->otpauthURI('Archmage', $u['email'] ?? ('user'.$u['id']), $secret);
        echo "Secret: {$secret}\nURI: {$uri}\n";
    }
    public function twofa_disable($userId) {
        $this->db->update('users',['twofa_secret'=>NULL,'twofa_enabled'=>0],['id'=>(int)$userId]);
        echo "2FA disabled for user {$userId}\n";
    }
    public function show_csp() {
        $cfg = $this->config->item('security_headers');
        echo "CSP: ".$cfg['csp']."\n";
    }
}
