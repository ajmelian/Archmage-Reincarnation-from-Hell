<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Twofa extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['TwoFA']);
        $this->load->helper(['url','form']);
        if (!$this->session->userdata('userId')) redirect('auth/login');
    }

    private function me() { return $this->db->get_where('users',['id'=>(int)$this->session->userdata('userId')])->row_array(); }

    public function index() {
        $u = $this->me();
        $this->load->view('twofa/index', ['u'=>$u]);
    }

    public function enable() {
        $u = $this->me();
        if ($u['twofa_enabled']) { redirect('twofa'); return; }
        // generar secreto y mostrar QR
        $secret = $this->twofa->randomSecret();
        $issuer = 'Archmage';
        $account = $u['email'] ?? ('user'.$u['id']);
        $uri = $this->twofa->otpauthURI($issuer, $account, $secret);
        $this->session->set_userdata('twofa_setup_secret', $secret);
        $this->load->view('twofa/enable', ['secret'=>$secret,'uri'=>$uri]);
    }

    public function verify_setup() {
        $u = $this->me();
        $secret = $this->session->userdata('twofa_setup_secret');
        if (!$secret) { redirect('twofa/enable'); return; }
        $code = (string)$this->input->post('code', TRUE);
        if ($this->twofa->verify($secret, $code)) {
            $this->db->update('users',['twofa_secret'=>$secret,'twofa_enabled'=>1],['id'=>$u['id']]);
            $this->session->unset_userdata('twofa_setup_secret');
            $this->session->set_flashdata('msg','2FA activado');
            redirect('twofa');
        } else {
            $this->session->set_flashdata('err','Código incorrecto, inténtalo de nuevo');
            redirect('twofa/enable');
        }
    }

    public function disable() {
        $u = $this->me();
        $this->db->update('users',['twofa_enabled'=>0,'twofa_secret'=>NULL],['id'=>$u['id']]);
        $this->session->set_flashdata('msg','2FA desactivado');
        redirect('twofa');
    }

    // Paso de verificación en login (si Auth.php lo llama)
    public function login_step() {
        if (!$this->session->userdata('twofa_pending_user')) redirect('auth/login');
        if ($this->input->method(TRUE)==='GET') { $this->load->view('twofa/verify', []); return; }
        $code = (string)$this->input->post('code', TRUE);
        $uid = (int)$this->session->userdata('twofa_pending_user');
        $u = $this->db->get_where('users',['id'=>$uid])->row_array();
        if ($u && $u['twofa_enabled'] && $u['twofa_secret'] && $this->twofa->verify($u['twofa_secret'], $code)) {
            // Completar login
            $this->session->unset_userdata('twofa_pending_user');
            // rotación de sesión recomendada
            session_regenerate_id(true);
            $this->session->set_userdata('userId', $u['id']);
            $this->db->update('users',['last_login_at'=>time(),'last_login_ip'=>$this->input->ip_address()],['id'=>$u['id']]);
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('err','Código inválido');
            redirect('twofa/login_step');
        }
    }
}
