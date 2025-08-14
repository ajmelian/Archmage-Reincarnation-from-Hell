<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Trade extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms', ['user_id'=>$uid])->row_array();
    }

    public function index() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $in  = $this->db->order_by('created_at','DESC')->get_where('trade_offers', ['to_realm_id'=>$r['id']])->result_array();
        $out = $this->db->order_by('created_at','DESC')->get_where('trade_offers', ['from_realm_id'=>$r['id']])->result_array();
        $this->load->view('trade/index', ['inbox'=>$in,'outbox'=>$out,'realm'=>$r]);
    }

    public function offer() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $to   = (int)$this->input->post('to_realm_id', TRUE);
        $gold = (int)$this->input->post('gold', TRUE);
        $item = (string)$this->input->post('item_id', TRUE);
        $qty  = (int)$this->input->post('qty', TRUE);

        $payload = ['gold'=>$gold,'items'=>[]];
        if ($item && $qty>0) $payload['items'][] = ['item_id'=>$item,'qty'=>$qty];

        $now = time();
        $exp = $now + 48*3600;
        $this->db->insert('trade_offers', [
            'from_realm_id'=>$r['id'],'to_realm_id'=>$to,
            'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status'=>'pending','created_at'=>$now,'expires_at'=>$exp
        ]);
        $this->session->set_flashdata('msg','Offer sent.');
        redirect('trade');
    }

    public function accept($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $o = $this->db->get_where('trade_offers', ['id'=>(int)$id])->row_array();
        if (!$o || (int)$o['to_realm_id'] !== (int)$r['id']) show_error('Forbidden', 403);
        if ($o['status']!=='pending') { $this->session->set_flashdata('err','Offer not pending'); redirect('trade'); }
        // TODO: transfer payload
        $this->db->where('id', $o['id'])->update('trade_offers', ['status'=>'accepted']);
        $this->db->insert('market_logs', ['type'=>'trade','realm_id'=>$r['id'],'ref_id'=>$o['id'],'payload'=>$o['payload'],'created_at'=>time()]);
        $this->session->set_flashdata('msg','Trade accepted.');
        redirect('trade');
    }

    public function decline($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $o = $this->db->get_where('trade_offers', ['id'=>(int)$id])->row_array();
        if (!$o || (int)$o['to_realm_id'] !== (int)$r['id']) show_error('Forbidden', 403);
        if ($o['status']!=='pending') { $this->session->set_flashdata('err','Offer not pending'); redirect('trade'); }
        $this->db->where('id', $o['id'])->update('trade_offers', ['status'=>'declined']);
        $this->session->set_flashdata('msg','Trade declined.');
        redirect('trade');
    }

    public function cancel($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $o = $this->db->get_where('trade_offers', ['id'=>(int)$id])->row_array();
        if (!$o || (int)$o['from_realm_id'] !== (int)$r['id']) show_error('Forbidden', 403);
        if ($o['status']!=='pending') { $this->session->set_flashdata('err','Offer not pending'); redirect('trade'); }
        $this->db->where('id', $o['id'])->update('trade_offers', ['status'=>'canceled']);
        $this->session->set_flashdata('msg','Trade canceled.');
        redirect('trade');
    }
}
