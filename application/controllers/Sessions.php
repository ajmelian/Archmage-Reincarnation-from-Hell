<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sessions extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
        $this->load->config('config');
    }

    public function purge() {
        $lifetime = (int)$this->config->item('sess_expiration') ?: 300;
        $threshold = time() - $lifetime;
        $this->db->where('timestamp <', $threshold)->delete('ci_sessions');
        echo "Purged expired sessions older than {$lifetime}s.\n";
    }
}
