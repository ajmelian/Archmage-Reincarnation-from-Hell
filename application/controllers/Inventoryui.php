<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Inventoryui extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url']);
        $this->load->library(['Wallet','Inventory']);
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms', ['user_id'=>$uid])->row_array();
    }

    public function index() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $bal = $this->wallet->balance((int)$r['id']);
        $items = $this->db->order_by('item_id','ASC')->get_where('inventories', ['realm_id'=>(int)$r['id']])->result_array();
        $this->load->view('inventory/index', ['realm'=>$r,'bal'=>$bal,'items'=>$items]);
    }
}
