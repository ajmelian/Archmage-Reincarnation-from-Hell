<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Privacy extends MY_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->library(['GdprService','session']);
        $this->load->helper(['url']);
        // TODO: auth middleware real
    }

    private function uid(){ return (int)$this->session->userdata('user_id'); }

    public function index(){ $this->load->view('static/privacy'); }
    public function terms(){ $this->load->view('static/terms'); }

    public function export(){
        $uid = $this->uid(); if (!$uid){ show_error('Login requerido', 401); }
        $data = $this->gdprservice->exportUserData($uid);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        $this->output->set_content_type('application/json');
        $this->output->set_header('Content-Disposition: attachment; filename="archmage_export_user_'+$uid+'.json"');
        $this->output->set_output($json);
    }

    public function delete_request(){
        $uid = $this->uid(); if (!$uid){ show_error('Login requerido', 401); }
        $this->load->view('privacy/delete_request');
    }

    public function delete_confirm(){
        $uid = $this->uid(); if (!$uid){ show_error('Login requerido', 401); }
        $ok = $this->gdprservice->anonymizeUser($uid);
        $this->session->sess_destroy();
        $this->load->view('privacy/delete_done', ['ok'=>$ok]);
    }
}
