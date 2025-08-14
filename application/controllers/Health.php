<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Health extends CI_Controller {
    public function live() { $this->output->set_content_type('text/plain')->set_output("OK\n"); }
    public function ready() {
        $ok = true;
        $msg = 'OK';
        try {
            $this->load->database();
            $this->db->query('SELECT 1');
        } catch (Throwable $e) {
            $ok = false; $msg = 'DB down';
        }
        $this->output->set_status_header($ok?200:503)->set_content_type('text/plain')->set_output($msg."\n");
    }
}
