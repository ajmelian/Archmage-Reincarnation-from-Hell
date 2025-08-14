<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Buildings extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library(['BuildingService','Wallet']);
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms', ['user_id'=>$uid])->row_array();
    }

    public function index() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $defs = $this->buildingservice->listDefs();
        $owned = $this->buildingservice->owned((int)$r['id']);
        $queue = $this->buildingservice->queueList((int)$r['id']);
        // map owned qty by id
        $map = []; foreach ($owned as $o) { $map[$o['building_id']] = (int)$o['qty']; }
        $bal = $this->wallet->balance((int)$r['id']);
        $this->load->view('buildings/index', ['realm'=>$r,'defs'=>$defs,'owned'=>$map,'queue'=>$queue,'bal'=>$bal]);
    }

    public function queue() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $bid = (string)$this->input->post('building_id', TRUE);
        $qty = (int)$this->input->post('qty', TRUE);
        try {
            $id = $this->buildingservice->queue((int)$r['id'], $bid, $qty);
            $this->session->set_flashdata('msg','Construcción en cola (#'+$id+')');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('buildings');
    }

    public function cancel($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        try {
            $this->buildingservice->cancel((int)$r['id'], (int)$id);
            $this->session->set_flashdata('msg','Cola cancelada.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('buildings');
    }

    public function demolish() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $bid = (string)$this->input->post('building_id', TRUE);
        $qty = (int)$this->input->post('qty', TRUE);
        try {
            $this->buildingservice->demolish((int)$r['id'], $bid, $qty);
            $this->session->set_flashdata('msg','Edificio demolido.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('buildings');
    }
}
