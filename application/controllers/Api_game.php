<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api_game extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('Jwt');
        $this->load->model(['Realm_model']);
        $this->output->set_content_type('application/json');
    }

    private function auth(): ?int {
        $h = $this->input->get_request_header('Authorization');
        if (!$h || stripos($h, 'Bearer ') !== 0) return null;
        $token = trim(substr($h, 7));
        $p = $this->jwt->decode($token);
        if (!$p) return null;
        return (int)$p['uid'];
    }

    private function rate(string $route, int $uid): bool {
        // simple window of 60s, limit 60
        $win = (int)(time() / 60);
        $row = $this->db->get_where('api_rate', ['user_id'=>$uid,'route'=>$route,'window_start'=>$win])->row_array();
        if ($row && $row['count'] >= 60) return FALSE;
        if ($row) $this->db->where('id',$row['id'])->update('api_rate', ['count'=>$row['count']+1]);
        else $this->db->insert('api_rate', ['user_id'=>$uid,'route'=>$route,'window_start'=>$win,'count'=>1]);
        return TRUE;
    }

    public function state() {
        $uid = $this->auth();
        if (!$uid) { echo json_encode(['ok'=>false,'error'=>'auth']); return; }
        if (!$this->rate('state', $uid)) { echo json_encode(['ok'=>false,'error'=>'rate']); return; }
        $realm = $this->Realm_model->getOrCreate($uid);
        $state = $this->Realm_model->loadState($realm);
        echo json_encode(['ok'=>true,'realm'=>$realm,'state'=>$state]);
    }
}
