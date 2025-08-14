<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Research extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library(['ResearchService','Wallet']);
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms', ['user_id'=>$uid])->row_array();
    }

    public function index() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $defs = $this->researchservice->listDefs();
        $levels = [];
        foreach ($defs as $d) { $levels[$d['id']] = $this->researchservice->level((int)$r['id'], $d['id']); }
        $queue = $this->researchservice->queueList((int)$r['id']);
        $bal = $this->wallet->balance((int)$r['id']);
        $this->load->view('research/index', ['realm'=>$r,'defs'=>$defs,'levels'=>$levels,'queue'=>$queue,'bal'=>$bal]);
    }

    public function queue() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $rid = (string)$this->input->post('research_id', TRUE);
        $target = (int)$this->input->post('target_level', TRUE);
        try {
            $id = $this->researchservice->queue((int)$r['id'], $rid, $target);
            $this->session->set_flashdata('msg','Investigación en cola (#'+$id+')');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('research');
    }

    public function cancel($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        try {
            $this->researchservice->cancel((int)$r['id'], (int)$id);
            $this->session->set_flashdata('msg','Cola cancelada.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('research');
    }
}
