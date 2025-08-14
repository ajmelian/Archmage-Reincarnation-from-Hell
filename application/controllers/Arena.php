<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Arena extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library('ArenaService');
    }

    private function currentRealm(): ?array {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        return $this->db->get_where('realms',['user_id'=>$uid])->row_array();
    }

    public function index() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $rating = $this->arenaservice->rating((int)$r['id']);
        $lb = $this->arenaservice->leaderboard(20);
        $hist = $this->arenaservice->history((int)$r['id']);
        $this->load->view('arena/index', ['realm'=>$r,'rating'=>$rating,'lb'=>$lb,'hist'=>$hist]);
    }

    public function queue() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        try {
            $this->arenaservice->enqueue((int)$r['id']);
            $this->session->set_flashdata('msg','Te has unido a la cola.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('err',$e->getMessage());
        }
        redirect('arena');
    }

    public function cancel() {
        $r = $this->currentRealm(); if (!$r) show_error('No realm', 403);
        $this->arenaservice->dequeue((int)$r['id']);
        $this->session->set_flashdata('msg','Has salido de la cola.');
        redirect('arena');
    }
}
