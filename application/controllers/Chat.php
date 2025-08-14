<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends MY_Controller {
    public function __construct(){ parent::__construct(); $this->load->library('ModerationService'); }
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library('ChatService');
        $this->load->config('chat');
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms',['user_id'=>$uid])->row_array();
    }

    private function globalChannel(): array {
        return $this->chatservice->ensureChannel('global','global','Global');
    }

    public function index($scope='global') {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $channels = [];
        $g = $this->globalChannel(); $channels[] = $g;
        $ally = $this->chatservice->allianceChannelFor((int)$r['id']);
        if ($ally) $channels[] = $ally;
        $active = $g;
        if ($scope==='alliance' && $ally) $active = $ally;
        $this->load->view('chat/index', [
            'realm'=>$r,'channels'=>$channels,'active'=>$active,
            'ui_poll_ms'=>(int)$this->config->item('chat')['ui_poll_ms']
        ]);
    }

    public function post() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $cid = (int)$this->input->post('channel_id', TRUE);
        $text = (string)$this->input->post('text', TRUE);
        try {
            $mid = $this->chatservice->post((int)$r['id'], $cid, $text);
            $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>true,'id'=>$mid]));
        } catch (Throwable $e) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['ok'=>false,'error'=>$e->getMessage()]));
        }
    }

    public function poll() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $cid = (int)$this->input->get('channel_id', TRUE);
        $after = (int)$this->input->get('after_id', TRUE);
        $limit = (int)$this->input->get('limit', TRUE) ?: 50;
        try {
            $rows = $this->chatservice->poll((int)$r['id'], $cid, $after, $limit);
            $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>true,'rows'=>$rows]));
        } catch (Throwable $e) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['ok'=>false,'error'=>$e->getMessage()]));
        }
    }
}
