<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Alliances extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library('AllianceService');
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms', ['user_id'=>$uid])->row_array();
    }

    public function index() {
        $list = $this->db->order_by('created_at','DESC')->limit(100)->get('alliances')->result_array();
        $r = $this->currentRealm();
        $aid = $r ? $this->allianceservice->realmAllianceId((int)$r['id']) : null;
        $this->load->view('alliances/index', ['alliances'=>$list,'realm'=>$r,'myAllianceId'=>$aid]);
    }

    public function create() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        if ($this->input->method(TRUE) === 'POST') {
            $name = trim($this->input->post('name', TRUE));
            $tag  = trim($this->input->post('tag', TRUE));
            $desc = trim($this->input->post('description', TRUE));
            try {
                $id = $this->allianceservice->create((int)$r['id'], $name, $tag, $desc);
                $this->session->set_flashdata('msg','Alliance created #'.$id);
                redirect('alliances/view/'.$id);
            } catch (Throwable $e) {
                $this->session->set_flashdata('err',$e->getMessage());
                redirect('alliances');
            }
        } else {
            $this->load->view('alliances/create');
        }
    }

    public function view($id) {
        $r = $this->currentRealm();
        $a = $this->allianceservice->getAlliance((int)$id);
        if (!$a) show_404();
        $role = $r ? $this->allianceservice->role((int)$id, (int)$r['id']) : null;
        // diplomacy list
        $diplo = $this->db->where('a1_id', (int)$id)->or_where('a2_id', (int)$id)->order_by('started_at','DESC')->get('diplomacy')->result_array();
        $invites = [];
        if (in_array($role, ['leader','officer'], true)) {
            $invites = $this->db->order_by('created_at','DESC')->get_where('alliance_invites',['alliance_id'=>(int)$id])->result_array();
        }
        $this->load->view('alliances/view', ['a'=>$a,'role'=>$role,'realm'=>$r,'diplo'=>$diplo,'invites'=>$invites]);
    }

    public function invite() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $aid = (int)$this->input->post('alliance_id', TRUE);
        $to  = (int)$this->input->post('to_realm_id', TRUE);
        try {
            $this->allianceservice->invite((int)$r['id'], $aid, $to);
            $this->session->set_flashdata('msg','Invite sent.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('alliances/view/'.$aid);
    }

    public function accept_invite($id) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        try {
            $this->allianceservice->acceptInvite((int)$r['id'], (int)$id);
            $this->session->set_flashdata('msg','Invite accepted.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('alliances');
    }

    public function leave($aid) {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        try {
            $this->allianceservice->leave((int)$r['id']);
            $this->session->set_flashdata('msg','You left the alliance.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('alliances');
    }

    public function promote() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $aid = (int)$this->input->post('alliance_id', TRUE);
        $target = (int)$this->input->post('realm_id', TRUE);
        $role = (string)$this->input->post('role', TRUE);
        try {
            $this->allianceservice->promote((int)$r['id'], $target, $role);
            $this->session->set_flashdata('msg','Role updated');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('alliances/view/'.$aid);
    }

    public function bank_deposit() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $aid = (int)$this->input->post('alliance_id', TRUE);
        $res = (string)$this->input->post('res', TRUE);
        $amt = (int)$this->input->post('amount', TRUE);
        try {
            $this->allianceservice->bankDeposit($aid, (int)$r['id'], $res, $amt);
            $this->session->set_flashdata('msg','Deposit done.');
        } catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances/view/'.$aid);
    }

    public function bank_withdraw() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $aid = (int)$this->input->post('alliance_id', TRUE);
        $res = (string)$this->input->post('res', TRUE);
        $amt = (int)$this->input->post('amount', TRUE);
        try {
            $this->allianceservice->bankWithdraw($aid, (int)$r['id'], $res, $amt);
            $this->session->set_flashdata('msg','Withdraw done.');
        } catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances/view/'.$aid);
    }

    // Diplomacy
    public function declare($aid2) { $this->setDiplo($aid2, 'war'); }
    public function nap($aid2)     { $this->setDiplo($aid2, 'nap'); }
    public function ally($aid2)    { $this->setDiplo($aid2, 'allied'); }
    public function neutral($aid2) { $this->setDiplo($aid2, 'neutral'); }

    private function setDiplo($aid2, $state) {
        if ((int)$aid2===0) { $aid2 = (int)$this->input->post('aid2', TRUE); }
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $aid = $this->allianceservice->realmAllianceId((int)$r['id']);
        if (!$aid) { $this->session->set_flashdata('err','You are not in an alliance'); redirect('alliances'); return; }
        try {
            $this->allianceservice->setState((int)$r['id'], (int)$aid, (int)$aid2, $state);
            $this->session->set_flashdata('msg','Diplomacy updated: '.$state);
        } catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances/view/'.$aid);
    }
}
