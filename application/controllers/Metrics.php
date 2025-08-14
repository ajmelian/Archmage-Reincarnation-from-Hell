<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Metrics extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('Observability');
    }

    public function index() {
        $txt = $this->observability->exportPrometheus(600);
        $this->output->set_content_type('text/plain')->set_output($txt);
    }
}
