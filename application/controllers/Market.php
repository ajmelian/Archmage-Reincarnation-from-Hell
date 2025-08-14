<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Market extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('ModerationService');
        $this->load->database();
        $this->load->library(['MarketService']);
        $this->load->helper(['url','form']);
    }

    private function realmId(): int {
        $u = (int)$this->session->userdata('userId');
        if (!$u) redirect('auth/login');
        $r = $this->db->get_where('realms',['user_id'=>$u])->row_array();
        if (!$r) show_error('Realm not found', 404);
        return (int)$r['id'];
    }

    public function index() {
        // Cache de salida 60s (solo GET)
        if ($this->input->method(TRUE)==='GET') $this->output->cache(1);
        $item = $this->input->get('item', TRUE);
        if ($item) $this->db->where('item_id',$item);
        $rows = $this->db->order_by('price_per_unit','ASC')->get_where('market_listings',['status'=>0])->result_array();
        $this->load->view('market/index', ['rows'=>$rows]);
    }

    public function my() {
        $rid = $this->realmId();
        $rows = $this->db->order_by('created_at','DESC')->get_where('market_listings',['seller_realm_id'=>$rid])->result_array();
        $inv = $this->db->get_where('inventory',['realm_id'=>$rid])->result_array();
        $this->load->view('market/my', ['rows'=>$rows,'inv'=>$inv]);
    }

    public function create() {
        // Moderation: mercado suspendido?
        $ridCheck = $this->session->userdata('userId') ? ($this->db->get_where('realms',['user_id'=>(int)$this->session->userdata('userId')])->row_array()['id'] ?? null) : null;
        if ($ridCheck && !$this->moderationservice->canTrade((int)$ridCheck)) { $this->session->set_flashdata('err','Mercado suspendido por moderación.'); redirect('market'); return; }
        $rid = $this->realmId();
        if ($this->input->method(TRUE)==='GET') { $this->load->view('market/create'); return; }
        $item = (string)$this->input->post('item_id', TRUE);
        $qty  = (int)$this->input->post('qty', TRUE);
        $ppu  = (int)$this->input->post('ppu', TRUE);
        try {
            $id = $this->marketservice->listItem($rid, $item, $qty, $ppu);
            $this->session->set_flashdata('msg','Listado creado #'.$id);
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('market/my');
    }

    public function buy($id) {
        $ridCheck = $this->session->userdata('userId') ? ($this->db->get_where('realms',['user_id'=>(int)$this->session->userdata('userId')])->row_array()['id'] ?? null) : null;
        if ($ridCheck && !$this->moderationservice->canTrade((int)$ridCheck)) { $this->session->set_flashdata('err','Mercado suspendido por moderación.'); redirect('market'); return; }
        $rid = $this->realmId();
        try {
            $tradeId = $this->marketservice->buy($rid, (int)$id);
            $this->session->set_flashdata('msg','Compra realizada (trade #'.$tradeId.')');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('market');
    }

    public function cancel($id) {
        $rid = $this->realmId();
        try {
            $this->marketservice->cancel($rid, (int)$id);
            $this->session->set_flashdata('msg','Listado cancelado');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('market/my');
    }
}
