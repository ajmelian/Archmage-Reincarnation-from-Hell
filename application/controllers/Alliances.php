<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Alliances extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('AllianceService');
        $this->load->database();
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
        $rid = $this->realmId();
        [$a,$role] = $this->allianceservice->myAllianceWithRole($rid);
        $invites = $this->allianceservice->myInvites($rid);
        $members = $a ? $this->allianceservice->members((int)$a['id']) : [];
        $this->load->view('alliances/index', ['a'=>$a,'role'=>$role,'invites'=>$invites,'members'=>$members]);
    }

    public function create() {
        $rid = $this->realmId();
        if ($this->input->method(TRUE)==='GET') { $this->load->view('alliances/create'); return; }
        $name = (string)$this->input->post('name', TRUE);
        $tag  = (string)$this->input->post('tag', TRUE);
        $desc = (string)$this->input->post('description', TRUE);
        try {
            $id = $this->allianceservice->create($rid, $name, $tag, $desc);
            $this->session->set_flashdata('msg','Alianza creada #'.$id);
        } catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances');
    }

    public function invite() {
        $rid = $this->realmId();
        $to  = (int)$this->input->post('to_realm_id', TRUE);
        try {
            $id = $this->allianceservice->invite($rid, $to);
            $this->session->set_flashdata('msg','Invitación enviada');
        } catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances');
    }

    public function accept($inviteId) {
        $rid = $this->realmId();
        try {
            $this->allianceservice->accept($rid, (int)$inviteId);
            $this->session->set_flashdata('msg','Te has unido a la alianza');
        } catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances');
    }

    public function leave() {
        $rid = $this->realmId();
        try {
            $this->allianceservice->leave($rid);
            $this->session->set_flashdata('msg','Has salido de la alianza');
        } catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances');
    }

    public function promote() {
        $rid = $this->realmId();
        $target = (int)$this->input->post('target_realm_id', TRUE);
        try { $this->allianceservice->promote($rid, $target); $this->session->set_flashdata('msg','Promoción aplicada'); }
        catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances');
    }

    public function demote() {
        $rid = $this->realmId();
        $target = (int)$this->input->post('target_realm_id', TRUE);
        try { $this->allianceservice->demote($rid, $target); $this->session->set_flashdata('msg','Degradación aplicada'); }
        catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances');
    }

    public function kick() {
        $rid = $this->realmId();
        $target = (int)$this->input->post('target_realm_id', TRUE);
        try { $this->allianceservice->kick($rid, $target); $this->session->set_flashdata('msg','Miembro expulsado'); }
        catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('alliances');
    }
}
