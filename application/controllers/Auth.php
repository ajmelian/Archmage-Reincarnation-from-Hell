<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct(){ parent::__construct(); $this->load->library(['Passwords','TwoFA']); $this->load->database(); }
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library(['SecurityService']);
    }

    public function login() {
        if ($this->input->method(TRUE)==='GET') { $this->load->view('auth/login'); return; }
        $email = (string)$this->input->post('email', TRUE);
        $pass  = (string)$this->input->post('password', TRUE);
        $ip = $this->input->ip_address();
        try { $this->securityservice->rateCheckLogin($email, $ip); } catch (Throwable $e) {
            $this->session->set_flashdata('err','Rate limited'); redirect('auth/login'); return;
        }
        $u = $this->db->get_where('users',['email'=>$email])->row_array();
        if (!$u) { $this->session->set_flashdata('err','Credenciales inválidas'); redirect('auth/login'); return; }
        if ($this->securityservice->isLocked($u)) { $this->session->set_flashdata('err','Cuenta bloqueada temporalmente'); redirect('auth/login'); return; }
        if (!password_verify($pass, $u['password_hash'])) {
            $this->securityservice->handleFailedLogin($u);
            $this->session->set_flashdata('err','Credenciales inválidas'); redirect('auth/login'); return;
        }
        // reset attempts
        $this->db->update('users',['login_attempts'=>0,'locked_until'=>null],['id'=>$u['id']]);
        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata('userId', (int)$u['id']);
        if ((int)($u['totp_enabled'] ?? 0) === 1) {
            $this->session->set_userdata('need2fa', 1);
            redirect('auth/second_factor');
        } else {
            redirect('home');
        }
    }

    public function second_factor() {
        $uid = (int)$this->session->userdata('userId');
        $need = (int)$this->session->userdata('need2fa');
        if (!$uid || !$need) redirect('auth/login');
        if ($this->input->method(TRUE)==='GET') { $this->load->view('auth/second_factor'); return; }
        $code = (string)$this->input->post('code', TRUE);
        $u = $this->db->get_where('users',['id'=>$uid])->row_array();
        $secret = $u['totp_secret'] ?? '';
        $this->load->library('TwoFactor');
        $ok = $this->twofactor->verify($secret, $code, 1);
        if (!$ok) {
            // check backup codes
            $b = json_decode($u['backup_codes'] ?? '[]', true) ?: [];
            $used = false;
            foreach ($b as $k=>$val) {
                if (hash_equals($val, $code)) { $used = true; unset($b[$k]); break; }
            }
            if ($used) {
                $this->db->update('users',['backup_codes'=>json_encode(array_values($b)),'last_2fa_at'=>time()],['id'=>$uid]);
            } else {
                $this->session->set_flashdata('err','Código inválido'); redirect('auth/second_factor'); return;
            }
        } else {
            $this->db->update('users',['last_2fa_at'=>time()],['id'=>$uid]);
        }
        $this->session->unset_userdata('need2fa');
        redirect('home');
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
