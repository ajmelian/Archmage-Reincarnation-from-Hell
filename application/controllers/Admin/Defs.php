<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Defs extends MY_Controller {
    protected bool $requireLogin = TRUE;
    public function __construct() {
        parent::__construct();
        // Simple gate: user role must be admin
        $this->load->model(['User_model']);
        $u = $this->User_model->findById((int)$this->session->userdata('userId'));
        if (!$u || ($u['role'] ?? 'user') !== 'admin') show_404();
        $this->load->database();
    }

    public function index() {
        $tables = ['unit_def','hero_def','item_def','spell_def','building_def','research_def'];
        $data = [];
        foreach ($tables as $t) {
            $data[$t] = $this->db->get($t)->result_array();
        }
        $this->load->view('admin/defs_index', ['tables'=>$data]);
    }

    public function edit($table, $id) {
        $allowed = ['unit_def','hero_def','item_def','spell_def','building_def','research_def'];
        if (!in_array($table, $allowed, TRUE)) show_404();
        $row = $this->db->get_where($table, ['id'=>$id])->row_array();
        if (!$row) show_404();

        if ($this->input->method(TRUE) === 'POST') {
            $data = $this->input->post(NULL, TRUE);
            unset($data[$this->security->get_csrf_token_name()]);
            $this->db->where('id', $id)->update($table, $data);
            redirect('admin/defs');
        }
        $this->load->view('admin/defs_edit', ['table'=>$table,'row'=>$row]);
    }
}
