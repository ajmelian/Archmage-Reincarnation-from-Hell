<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Marketcli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('MarketService');
    }
    public function cleanup() {
        $n = $this->marketservice->cleanupExpired();
        echo "Expired listings updated: $n\n";
    }
}
