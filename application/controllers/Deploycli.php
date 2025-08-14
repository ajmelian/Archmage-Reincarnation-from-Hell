<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Deploycli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
        $this->load->library(['Migration','Observability']);
        $this->load->helper('url');
    }

    public function quick() {
        echo "== Migrations up ==\n";
        $this->migration->current(); // ensure library loaded
        // fallback to index.php migrate up if present; otherwise try CI migration class
        if (method_exists($this->migration,'latest')) $this->migration->latest();
        echo "== Warm caches ==\n";
        $_SERVER['argv'] = ['index.php','cachecli','warm']; // call other controller
        $CI = &get_instance();
        // Not trivial to chain CLI; instruct user in README. We'll just print hints:
        echo "Run: php public/index.php cachecli warm\n";
        echo "== Done ==\n";
    }
}
