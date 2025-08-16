<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EmailService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('email');
        $this->CI->load->config('email');
        $this->CI->lang->load('auth');
        $this->email->initialize($this->CI->config->item('email'));
    }

    private function from() {
        $cfg = $this->CI->config->item('email');
        return [$cfg['from_email'] ?? 'noreply@example.com', $cfg['from_name'] ?? 'Archmage'];
    }

    public function sendPasswordReset($toEmail, $token) {
        list($fromEmail, $fromName) = $this->from();
        $base = $this->CI->config->item('email')['base_url'] ?? base_url();
        $link = rtrim($base,'/').'/index.php/auth/reset/'.$token;
        $this->email->from($fromEmail, $fromName);
        $this->email->to($toEmail);
        $this->email->subject($this->CI->lang->line('auth.email.reset_subject') ?: 'Password reset');
        $body = $this->CI->load->view('email/password_reset', ['link'=>$link], TRUE);
        $this->email->message($body);
        return $this->email->send();
    }

    public function sendEmailVerification($toEmail, $token) {
        list($fromEmail, $fromName) = $this->from();
        $base = $this->CI->config->item('email')['base_url'] ?? base_url();
        $link = rtrim($base,'/').'/index.php/email/verify/'.$token;
        $this->email->from($fromEmail, $fromName);
        $this->email->to($toEmail);
        $this->email->subject($this->CI->lang->line('auth.email.verify_subject') ?: 'Verify your email');
        $body = $this->CI->load->view('email/verify_email', ['link'=>$link], TRUE);
        $this->email->message($body);
        return $this->email->send();
    }
}
