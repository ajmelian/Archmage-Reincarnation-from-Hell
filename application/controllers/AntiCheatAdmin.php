<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AntiCheatAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->lang->load('anticheat');
        $this->load->database();
        $this->load->library('AntiCheatService');
        $this->load->helper(['url','form']);
        // TODO: check admin role
    }

    public function events() {
        $rows = $this->db->order_by('id','DESC')->limit(200)->get('anticheat_events')->result_array();
        $this->load->view('admin/anticheat_events', ['rows'=>$rows]);
    }

    public function sanctions() {
        $rows = $this->db->order_by('id','DESC')->limit(200)->get('sanctions')->result_array();
        $this->load->view('admin/anticheat_sanctions', ['rows'=>$rows]);
    }

    public function impose() {
        if ($this->input->method(TRUE)!=='POST') show_404();
        $uid = (int)$this->input->post('user_id', TRUE);
        $type = $this->input->post('type', TRUE);
        $hours = (int)$this->input->post('hours', TRUE);
        $reason = $this->input->post('reason', TRUE);
        $id = $this->anticheatservice->imposeSanction($uid, $type, $hours ?: null, $reason ?: null);
        redirect('admin/anticheat/sanctions');
    }

    public function revoke($id) {
        $this->anticheatservice->revokeSanction((int)$id);
        redirect('admin/anticheat/sanctions');
    }
}
