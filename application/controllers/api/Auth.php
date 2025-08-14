<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('ApiAuth');
    }

    // POST /api/auth/token  body: email, password, days(optional), name(optional), scopes(optional)
    public function token() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $email = (string)$this->input->post('email', TRUE);
        $pass  = (string)$this->input->post('password', TRUE);
        $days  = $this->input->post('days', TRUE);
        $name  = (string)$this->input->post('name', TRUE) ?: 'api-token';
        $sc    = (string)$this->input->post('scopes', TRUE) ?: 'read';
        if ($email==='' || $pass==='') $this->json(['ok'=>false,'error'=>'Missing params'], 400);
        $user = $this->db->get_where('users',['email'=>$email])->row_array();
        if (!$user) $this->json(['ok'=>false,'error'=>'Invalid credentials'], 401);
        if (!password_verify($pass, $user['password_hash'])) $this->json(['ok'=>false,'error'=>'Invalid credentials'], 401);
        $ttl = is_numeric($days) ? (int)$days : null;
        $res = $this->apiauth->mint((int)$user['id'], $ttl, $name, $sc);
        $this->json(['ok'=>true,'token'=>$res['token'],'expires_at'=>$res['expires_at']]);
    }

    private function json($data, int $status=200) {
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
    }
}
