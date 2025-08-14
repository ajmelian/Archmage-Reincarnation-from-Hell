<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api_auth extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('Jwt');
        $this->load->model('User_model');
        $this->output->set_content_type('application/json');
    }

    public function login() {
        $email = trim((string)$this->input->post('email', TRUE));
        $pass  = (string)$this->input->post('password', TRUE);
        $user = $this->User_model->findByEmail($email);
        if ($user && password_verify($pass, $user['pass_hash'])) {
            $token = $this->jwt->encode(['uid'=>(int)$user['id']]);
            echo json_encode(['ok'=>true,'token'=>$token]);
        } else {
            echo json_encode(['ok'=>false,'error'=>'invalid']);
        }
    }
}
