<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Battle extends MY_Controller { public function __construct(){ parent::__construct(); $this->load->database(); } public function view($id){ $b=$this->db->get_where('battles',['id'=>$id])->row_array(); if(!$b) show_404(); $this->load->view('battle/view',['b'=>$b]); } 
    public function json($id) {
        $b = $this->db->get_where('battles', ['id'=>$id])->row_array();
        if (!$b) show_404();
        $this->output->set_content_type('application/json');
        $timeline = [];
        if (!empty($b['timeline'])) {
            $decoded = json_decode($b['timeline'], true);
            if (is_array($decoded)) $timeline = $decoded;
        }
        echo json_encode([
            'id'=>(int)$b['id'],
            'tick'=>(int)($b['tick'] ?? 0),
            'winner'=>$b['winner'] ?? null,
            'lossesA'=>isset($b['lossesA'])?json_decode($b['lossesA'],true):null,
            'lossesB'=>isset($b['lossesB'])?json_decode($b['lossesB'],true):null,
            'timeline'=>$timeline,
            'log'=>$b['log'] ?? ''
        ], JSON_UNESCAPED_UNICODE);
    }

}
