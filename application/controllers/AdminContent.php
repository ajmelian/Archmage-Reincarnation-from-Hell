<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AdminContent extends MY_Controller {
    private $tables = ['units','spells','items','heroes'];

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ContentService','Importer']);
        $this->load->helper(['url','form']);
    }

    public function index() { $this->load->view('admin/content_index', ['tables'=>$this->tables]); }

    public function list($table) {
        if (!in_array($table, $this->tables, true)) show_404();
        $rows = $this->contentservice->list($table, 200, 0);
        $this->load->view('admin/content_list', ['table'=>$table,'rows'=>$rows]);
    }

    public function edit($table, $id=null) {
        if (!in_array($table, $this->tables, true)) show_404();
        if ($this->input->method(TRUE)==='POST') {
            $data = $this->input->post(NULL, TRUE);
            if ($id) $ok = $this->contentservice->update($table, $id, $data);
            else $id = $this->contentservice->create($table, $data);
            redirect('admin/content/list/'.$table);
        } else {
            $row = $id ? $this->contentservice->get($table, $id) : null;
            $this->load->view('admin/content_edit', ['table'=>$table,'row'=>$row]);
        }
    }

    public function delete($table, $id) {
        if (!in_array($table, $this->tables, true)) show_404();
        $this->contentservice->delete($table, $id);
        redirect('admin/content/list/'.$table);
    }

    public function import() {
        if ($this->input->method(TRUE)==='POST') {
            $table = (string)$this->input->post('table', TRUE);
            if (!in_array($table, $this->tables, true)) show_error('invalid table');
            if (!isset($_FILES['file'])) show_error('no file');
            $tmp = $_FILES['file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $dest = APPPATH.'cache/import_'.time().'.'.$ext;
            @move_uploaded_file($tmp, $dest);
            $res = $this->importer->import($dest, $table);
            @unlink($dest);
            $this->load->view('admin/content_import_result', ['table'=>$table,'res'=>$res]);
        } else {
            $this->load->view('admin/content_import', ['tables'=>$this->tables]);
        }
    }
}
