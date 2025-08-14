<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('migration');
    }

    public function up() {
        if ($this->migration->current() === FALSE) {
            echo $this->migration->error_string(), PHP_EOL;
            return;
        }
        echo "Migrations up to version: ".$this->config->item('migration_version').PHP_EOL;
    }
}
