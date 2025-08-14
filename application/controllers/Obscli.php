<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Obscli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('Observability');
    }

    public function cleanup() {
        $res = $this->observability->cleanup();
        echo "Cleanup: ".json_encode($res).PHP_EOL;
    }
}
