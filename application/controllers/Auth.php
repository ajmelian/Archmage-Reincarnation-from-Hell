<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form','security']);
        $this->load->library(['EmailService','session']);
    }

    private function hasColumn($table, $column): bool {
        $q = $this->db->query('SHOW COLUMNS FROM `'.$table.'` LIKE '.$this->db->escape($column));
        return $q->row_array() ? true : false;
    }

    public function request_reset() {
        if ($this->input->method(TRUE) !== 'POST') {
            $this->load->view('auth/reset_request');
            return;
        }
        $email = strtolower(trim($this->input->post('email', TRUE)));
        if (!$email) { $this->session->set_flashdata('err','Email requerido'); redirect('auth/request_reset'); }
        // Buscar usuario
        $user = $this->db->get_where('users', ['email'=>$email])->row_array();
        $userId = $user['id'] ?? null;
        $token = bin2hex(random_bytes(32));
        $this->db->insert('password_resets',[
            'user_id'=>$userId,'email'=>$email,'token'=>$token,
            'ip'=>$this->input->ip_address(),'ua'=>substr($this->input->user_agent(),0,250),
            'created_at'=>time()
        ]);
        $this->emailservice->sendPasswordReset($email, $token);
        $this->load->view('auth/reset_request', ['sent'=>true]);
    }

    public function reset($token=null) {
        if (!$token) show_404();
        $row = $this->db->get_where('password_resets',['token'=>$token,'used_at'=>NULL])->row_array();
        if (!$row) show_error('Token inválido o usado.');
        $this->load->view('auth/reset_form', ['token'=>$token]);
    }

    public function reset_submit() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $token = $this->input->post('token', TRUE);
        $pass1 = $this->input->post('password', TRUE);
        $pass2 = $this->input->post('password2', TRUE);
        if (!$token || !$pass1 || $pass1 !== $pass2) { $this->session->set_flashdata('err','Datos inválidos'); redirect('auth/reset/'.$token); }
        $row = $this->db->get_where('password_resets',['token'=>$token,'used_at'=>NULL])->row_array();
        if (!$row) show_error('Token inválido.');
        // Actualiza password si existe columna users.password_hash
        $user = $this->db->get_where('users',['email'=>$row['email']])->row_array();
        if (!$user) show_error('Usuario no encontrado para ese email.');
        $hash = password_hash($pass1, PASSWORD_BCRYPT);
        if ($this->hasColumn('users','password_hash')) {
            $this->db->update('users',['password_hash'=>$hash],['id'=>$user['id']]);
        } else if ($this->hasColumn('users','password')) {
            $this->db->update('users',['password'=>$hash],['id'=>$user['id']]);
        }
        $this->db->update('password_resets',['used_at'=>time()],['id'=>$row['id']]);
        $this->load->view('auth/reset_success');
    }

    public function verify($token=null) {
        if (!$token) show_404();
        $row = $this->db->get_where('email_verifications',['token'=>$token,'verified_at'=>NULL])->row_array();
        if (!$row) show_error('Token inválido.');
        $this->db->update('email_verifications',['verified_at'=>time()],['id'=>$row['id']]);
        if ($this->hasColumn('users','email_verified_at')) {
            $this->db->update('users',['email_verified_at'=>time()],['id'=>$row['user_id']]);
        }
        $this->load->view('auth/verify_success');
    }
}
