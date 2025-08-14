<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auctions extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['AuctionService']);
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
        $rows = $this->db->order_by('ends_at','ASC')->get_where('auctions',['status'=>0])->result_array();
        $this->load->view('auctions/index', ['rows'=>$rows]);
    }

    public function view($id) {
        $a = $this->db->get_where('auctions',['id'=>(int)$id])->row_array();
        if (!$a) show_404();
        $bids = $this->db->order_by('amount','DESC')->get_where('auction_bids',['auction_id'=>$a['id']])->result_array();
        $this->load->view('auctions/view', ['a'=>$a,'bids'=>$bids]);
    }

    public function create() {
        $rid = $this->realmId();
        if ($this->input->method(TRUE)==='GET') { $this->load->view('auctions/create'); return; }
        $item = (string)$this->input->post('item_id', TRUE);
        $qty  = (int)$this->input->post('qty', TRUE);
        $start= (int)$this->input->post('start_price', TRUE);
        $buy  = $this->input->post('buyout_price', TRUE); $buy = ($buy===''? null : (int)$buy);
        $min  = (int)$this->input->post('minutes', TRUE);
        try {
            $id = $this->auctionservice->create($rid, $item, $qty, $start, $buy, $min);
            $this->session->set_flashdata('msg','Subasta creada #'.$id);
            redirect('auctions/view/'.$id);
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage()); redirect('auctions/create');
        }
    }

    public function bid($id) {
        $rid = $this->realmId();
        $amount = (int)$this->input->post('amount', TRUE);
        try {
            $this->auctionservice->bid($rid, (int)$id, $amount);
            $this->session->set_flashdata('msg','Puja registrada');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('auctions/view/'.$id);
    }

    public function cancel($id) {
        $rid = $this->realmId();
        try {
            $this->auctionservice->cancel($rid, (int)$id);
            $this->session->set_flashdata('msg','Subasta cancelada');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('auctions');
    }
}
