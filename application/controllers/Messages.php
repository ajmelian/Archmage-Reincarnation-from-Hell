<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Messages extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library('ChatService');
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms',['user_id'=>$uid])->row_array();
    }

    public function index() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $in = $this->chatservice->inbox((int)$r['id'], 50);
        $sent = $this->chatservice->sent((int)$r['id'], 20);
        $this->load->view('messages/inbox', ['realm'=>$r,'inbox'=>$in,'sent'=>$sent]);
    }

    public function compose() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $this->load->view('messages/compose', ['realm'=>$r]);
    }

    public function send() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $to = (int)$this->input->post('to_realm_id', TRUE);
        $sub = (string)$this->input->post('subject', TRUE);
        $body= (string)$this->input->post('body', TRUE);
        try {
            $id = $this->chatservice->sendDM((int)$r['id'], $to, $sub, $body);
            $this->session->set_flashdata('msg','Mensaje enviado.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('messages');
    }

    public function read($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        try {
            $msg = $this->chatservice->read((int)$r['id'], (int)$id);
            $this->load->view('messages/read', ['realm'=>$r,'msg'=>$msg]);
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
            redirect('messages');
        }
    }

    public function delete($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $this->chatservice->delete((int)$r['id'], (int)$id);
        $this->session->set_flashdata('msg','Mensaje eliminado.');
        redirect('messages');
    }
}
