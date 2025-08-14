<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Market extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library('MarketService');
        $this->load->config('market');
    }

    private function currentRealmId(): ?int {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        $r = $this->db->get_where('realms', ['user_id'=>$uid])->row_array();
        return $r ? (int)$r['id'] : null;
    }

    public function index() {
        $rid = $this->currentRealmId();
        $q = $this->db->order_by('created_at','DESC')->limit(100)->get_where('market_listings', ['status'=>'active'])->result_array();
        $mine = [];
        if ($rid) $mine = $this->db->order_by('created_at','DESC')->limit(100)->get_where('market_listings', ['realm_id'=>$rid])->result_array();
        $this->load->view('market/index', ['listings'=>$q,'mine'=>$mine,'cfg'=>$this->config->item('market'),'realmId'=>$rid]);
    }

    public function list_item() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $rid = $this->currentRealmId(); if (!$rid) show_error('No realm', 403);
        $item = (string)$this->input->post('item_id', TRUE);
        $qty  = (int)$this->input->post('qty', TRUE);
        $ppu  = (int)$this->input->post('price_per_unit', TRUE);
        try {
            $id = $this->marketservice->createListing($rid, $item, $qty, $ppu);
            $this->session->set_flashdata('msg','Listing created: #'.$id);
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('market');
    }

    public function buy($id) {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $rid = $this->currentRealmId(); if (!$rid) show_error('No realm', 403);
        $qty = (int)$this->input->post('qty', TRUE);
        try {
            $res = $this->marketservice->buy($rid, (int)$id, $qty);
            $this->session->set_flashdata('msg','Bought: '.$res['qty'].' units (pay='.$res['pay'].')');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('market');
    }

    public function cancel($id) {
        $rid = $this->currentRealmId(); if (!$rid) show_error('No realm', 403);
        try {
            $this->marketservice->cancel($rid, (int)$id);
            $this->session->set_flashdata('msg','Listing canceled.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('market');
    }
}
