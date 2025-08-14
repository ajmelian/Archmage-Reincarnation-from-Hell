<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url','security','form','language']);
        $this->load->model('User_model');
    }

    public function login() {
        if ($this->session->userdata('userId')) {
            return redirect('game');
        }
        if ($this->input->method(TRUE) === 'POST') {
            $email = trim((string)$this->input->post('email', TRUE));
            $pass  = (string)$this->input->post('password', TRUE);

            $user = $this->User_model->findByEmail($email);
            if ($user && password_verify($pass, $user['pass_hash'])) {
                // Regenerar sesión y setear
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata(['userId'=>(int)$user['id'], 'displayName'=>$user['display_name']]);
                return redirect('game');
            }
            $data = ['error'=>$this->lang->line('invalid_credentials')];
            return $this->load->view('auth/login', $data);
        }
        $this->load->view('auth/login');
    }

    public function register() {
        if ($this->session->userdata('userId')) {
            return redirect('game');
        }
        if ($this->input->method(TRUE) === 'POST') {
            $email = trim((string)$this->input->post('email', TRUE));
            $display = trim((string)$this->input->post('display_name', TRUE));
            $pass  = (string)$this->input->post('password', TRUE);
            $pass2 = (string)$this->input->post('password2', TRUE);

            if (!$email || !$pass || $pass !== $pass2) {
                return $this->load->view('auth/register', ['error'=>$this->lang->line('invalid_register')]);
            }
            if ($this->User_model->findByEmail($email)) {
                return $this->load->view('auth/register', ['error'=>$this->lang->line('email_exists')]);
            }

            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
            $hash = password_hash($pass, $algo);
            $userId = $this->User_model->create([
                'email'=>$email,
                'display_name'=>$display ?: 'Mage',
                'pass_hash'=>$hash,
                'created_at'=>time()
            ]);

            // Crear reino inicial
            $this->load->model('Realm_model');
            $realm = $this->Realm_model->getOrCreate((int)$userId);

            // Login directo
            $this->session->sess_regenerate(TRUE);
            $this->session->set_userdata(['userId'=>(int)$userId, 'displayName'=>$display ?: 'Mage']);
            return redirect('game');
        }
        $this->load->view('auth/register');
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
