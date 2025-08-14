<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Backup extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library(['BackupService','AdminService']);
        $this->admin = $this->adminservice->requireAdmin();
        $this->load->helper(['url','form']);
    }
    public function index() {
        $rows = $this->backupservice->listFiles();
        $this->load->view('backup/index', ['rows'=>$rows]);
    }
    public function create() {
        $note = (string)$this->input->post('note', TRUE);
        try {
            $fname = $this->backupservice->createDump($note);
            $this->session->set_flashdata('msg','Backup creado: '.$fname);
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('backup');
    }
    public function download($id) {
        $this->load->helper('download');
        $row = $this->db->get_where('backups',['id'=>(int)$id])->row_array();
        if (!$row) show_404();
        $full = $this->backupservice->filePath($row);
        if (!is_file($full)) show_404();
        force_download($row['filename'], file_get_contents($full), true);
    }
    public function delete($id) {
        $this->backupservice->delete((int)$id);
        redirect('backup');
    }
}
