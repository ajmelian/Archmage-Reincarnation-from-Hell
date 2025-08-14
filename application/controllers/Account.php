<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library(['TwoFactor']);
    }

    private function user() {
        $uid = (int)$this->session->userdata('userId'); if (!$uid) redirect('auth/login');
        return $this->db->get_where('users',['id'=>$uid])->row_array();
    }

    public function security() {
        $u = $this->user();
        if ($this->input->method(TRUE)==='POST') {
            // enable or disable
            if ($this->input->post('action')==='enable') {
                $secret = $this->twofactor->generateSecret();
                $this->db->update('users',['totp_secret'=>$secret,'totp_enabled'=>0],['id'=>$u['id']]);
                $u['totp_secret']=$secret; $u['totp_enabled']=0;
            }
            if ($this->input->post('action')==='disable') {
                $this->db->update('users',['totp_enabled'=>0,'totp_secret'=>null,'backup_codes'=>null],['id'=>$u['id']]);
                $u = $this->user();
            }
            if ($this->input->post('action')==='confirm') {
                $code = (string)$this->input->post('code', TRUE);
                $ok = $this->twofactor->verify($u['totp_secret'], $code, 1);
                if ($ok) {
                    // generate 8 backup codes (6 digits)
                    $codes = []; for ($i=0;$i<8;$i++) $codes[] = str_pad((string)random_int(0,999999), 6, '0', STR_PAD_LEFT);
                    $this->db->update('users',['totp_enabled'=>1,'backup_codes'=>json_encode($codes),'last_2fa_at'=>time()],['id'=>$u['id']]);
                    $u = $this->user();
                    $this->session->set_flashdata('msg','2FA activado. Guarda tus códigos de respaldo.');
                } else {
                    $this->session->set_flashdata('err','Código inválido');
                }
            }
            if ($this->input->post('action')==='regen_backup') {
                if ((int)($u['totp_enabled'] ?? 0) !== 1) { $this->session->set_flashdata('err','Activa 2FA primero'); }
                else {
                    $codes = []; for ($i=0;$i<8;$i++) $codes[] = str_pad((string)random_int(0,999999), 6, '0', STR_PAD_LEFT);
                    $this->db->update('users',['backup_codes'=>json_encode($codes)],['id'=>$u['id']]);
                    $u = $this->user(); $this->session->set_flashdata('msg','Códigos regenerados.');
                }
            }
        }
        // build otpauth
        $otp = null;
        if (!empty($u['totp_secret'])) {
            $otp = $this->twofactor->otpauthUri($u['email'],$u['totp_secret']);
        }
        $this->load->view('account/security', ['user'=>$u,'otpauth'=>$otp]);
    }
}
