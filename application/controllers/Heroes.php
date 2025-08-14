<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Heroes extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library(['HeroProgress','TalentTree']);
    }

    private function currentRealmId(): ?int {
        $uid = (int)$this->session->userdata('userId');
        if (!$uid) return null;
        $r = $this->db->get_where('realms', ['user_id'=>$uid])->row_array();
        return $r ? (int)$r['id'] : null;
    }

    public function index() {
        $rid = $this->currentRealmId(); if (!$rid) show_error('No realm', 403);
        // List heroes del reino (asumimos tabla realm_heroes con hero_id)
        $heroes = $this->db->get_where('realm_heroes', ['realm_id'=>$rid])->result_array();
        $rows = [];
        foreach ($heroes as $h) {
            $hp = $this->db->get_where('hero_progress', ['realm_id'=>$rid,'hero_id'=>$h['hero_id']])->row_array();
            if (!$hp) { $hp = ['level'=>1,'xp'=>0,'talent_points'=>0]; }
            $talents = $this->talenttree->heroTalents($rid, $h['hero_id']);
            $rows[] = ['hero_id'=>$h['hero_id'],'level'=>$hp['level'] ?? 1,'xp'=>$hp['xp'] ?? 0,'talent_points'=>$hp['talent_points'] ?? 0,'talents'=>$talents];
        }
        $defs = $this->db->get('talent_def')->result_array();
        $this->load->view('heroes/progression', ['rows'=>$rows,'defs'=>$defs]);
    }

    public function allocate() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $rid = $this->currentRealmId(); if (!$rid) show_error('No realm', 403);
        $hero = (string)$this->input->post('hero_id', TRUE);
        $tal  = (string)$this->input->post('talent_id', TRUE);
        try {
            $res = $this->heroprogress->allocate_talent($rid, $hero, $tal);
            $this->session->set_flashdata('msg', 'Talent upgraded: '.$tal.' → rank '.$res['new_rank']);
        } catch (Throwable $e) {
            $this->session->set_flashdata('err', $e->getMessage());
        }
        redirect('heroes');
    }
}
