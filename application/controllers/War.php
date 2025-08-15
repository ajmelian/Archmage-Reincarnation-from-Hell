<?php defined('BASEPATH') OR exit('No direct script access allowed');

class War extends MY_Controller {
    public function __construct() { parent::__construct(); $this->load->library('WarService'); }

    public function declare() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $a = (int)$this->input->post('alliance_a_id', TRUE);
        $b = (int)$this->input->post('alliance_b_id', TRUE);
        $id = $this->warservice->declareWar($a, $b);
        $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>true,'war_id'=>$id]));
    }

    public function scoreboard($id) {
        $res = $this->warservice->scoreboard((int)$id);
        if (!$res) show_404();
        $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }
}
