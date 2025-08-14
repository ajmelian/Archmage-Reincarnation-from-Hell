<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Apicli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
        $this->load->library('ApiAuth');
    }

    public function mint($email, $days='30', $name='cli', $scopes='read') {
        $u = $this->db->get_where('users',['email'=>$email])->row_array();
        if (!$u) { echo "User not found\n"; return; }
        $res = $this->apiauth->mint((int)$u['id'], (int)$days, $name, $scopes);
        echo "Token: {$res['token']} (expires_at=".( $res['expires_at'] ? date('c',$res['expires_at']) : 'never').")\n";
    }

    public function list($email) {
        $u = $this->db->get_where('users',['email'=>$email])->row_array();
        if (!$u) { echo "User not found\n"; return; }
        $rows = $this->db->order_by('created_at','DESC')->get_where('api_tokens',['user_id'=>$u['id']])->result_array();
        foreach ($rows as $r) {
            echo "#{$r['id']} name=".($r['name'] ?? '')." scopes=".($r['scopes'] ?? '')." revoked={$r['revoked']} expires=".($r['expires_at']?date('c',$r['expires_at']):'never')."\n";
        }
    }

    public function revoke($id) {
        $this->apiauth->revoke((int)$id);
        echo "Revoked token #{$id}\n";
    }
}
