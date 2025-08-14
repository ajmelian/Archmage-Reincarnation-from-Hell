<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mod extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ModerationService','AuditLog','AdminService']);
        // Requiere admin para simplicidad (puede adaptarse a rol "moderator")
        $this->admin = $this->adminservice->requireAdmin();
        $this->load->helper(['url','form']);
    }

    public function index() {
        $pending = $this->moderationservice->flags('pending', 200);
        $active = $this->db->order_by('created_at','DESC')->limit(200)->get('mod_actions')->result_array();
        $this->load->view('mod/index', ['pending'=>$pending,'active'=>$active]);
    }

    public function flag($id) {
        $f = $this->db->get_where('mod_flags',['id'=>(int)$id])->row_array();
        if (!$f) show_404();
        $this->load->view('mod/flag', ['f'=>$f]);
    }

    public function resolve($id) {
        $resolution = (string)$this->input->post('resolution', TRUE);
        $reject = (bool)$this->input->post('reject', TRUE);
        $this->moderationservice->resolve((int)$this->admin['user_id'], (int)$id, $resolution, $reject);
        redirect('mod');
    }

    public function sanction() {
        $target = (int)$this->input->post('target_realm_id', TRUE);
        $action = (string)$this->input->post('action', TRUE);
        $minutes = (int)$this->input->post('minutes', TRUE);
        $reason = (string)$this->input->post('reason', TRUE);
        try {
            $this->moderationservice->sanction((int)$this->admin['user_id'], $target, $action, $minutes, $reason);
            $this->session->set_flashdata('msg','Sanción aplicada');
        } catch (Throwable $e) { $this->session->set_flashdata('err',$e->getMessage()); }
        redirect('mod');
    }
}
