<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Logs extends MY_Controller {
    protected bool $requireLogin = TRUE;
    public function __construct() {
        parent::__construct();
        $this->load->model(['User_model']);
        $u = $this->User_model->findById((int)$this->session->userdata('userId'));
        if (!$u || ($u['role'] ?? 'user') !== 'admin') show_404();
        $this->load->database();
    }

    public function index() {
        $tick = (int)($this->input->get('tick') ?: 0);
        if ($tick > 0) {
            $battles = $this->db->get_where('battles', ['tick'=>$tick])->result_array();
            $spells  = $this->db->get_where('spell_logs', ['tick'=>$tick])->result_array();
        } else {
            $battles = $this->db->order_by('id','DESC')->limit(50)->get('battles')->result_array();
            $spells  = $this->db->order_by('id','DESC')->limit(50)->get('spell_logs')->result_array();
        }
        $this->load->view('admin/logs_index', ['battles'=>$battles, 'spells'=>$spells, 'tick'=>$tick]);
    }
}
