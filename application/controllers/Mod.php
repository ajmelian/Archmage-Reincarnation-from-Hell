<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mod extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library('ModerationService');
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms',['user_id'=>$uid])->row_array();
    }

    public function block($targetRealmId) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $this->moderationservice->block((int)$r['id'], (int)$targetRealmId);
        $this->session->set_flashdata('msg','Bloqueado.');
        redirect($this->input->server('HTTP_REFERER') ?: 'messages');
    }
    public function unblock($targetRealmId) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $this->moderationservice->unblock((int)$r['id'], (int)$targetRealmId);
        $this->session->set_flashdata('msg','Desbloqueado.');
        redirect($this->input->server('HTTP_REFERER') ?: 'messages');
    }

    public function report_chat($messageId) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $reason = (string)$this->input->get('r', TRUE) ?: '';
        $id = $this->moderationservice->report((int)$r['id'], 'chat', (int)$messageId, $reason);
        $this->session->set_flashdata('msg','Reportado (ID #'.$id.').');
        redirect($this->input->server('HTTP_REFERER') ?: 'chat');
    }

    public function report_dm($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $reason = (string)$this->input->get('r', TRUE) ?: '';
        $id = $this->moderationservice->report((int)$r['id'], 'dm', (int)$id, $reason);
        $this->session->set_flashdata('msg','Reportado (ID #'.$id.').');
        redirect($this->input->server('HTTP_REFERER') ?: 'messages');
    }
}
