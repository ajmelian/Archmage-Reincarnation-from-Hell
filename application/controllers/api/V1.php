<?php defined('BASEPATH') OR exit('No direct script access allowed');

class V1 extends MY_ApiController {
    public function __construct() {
        parent::__construct();
        $this->load->library(['ArenaService','ResearchService','Wallet','TalentTree','Engine']);
    }

    private function currentRealm(): ?array {
        $u = $this->apiUser;
        if (!$u) return null;
        return $this->db->get_where('realms',['user_id'=>$u['id']])->row_array();
    }

    // GET /api/v1/me
    public function me() {
        $realm = $this->currentRealm();
        $this->json(['ok'=>true,'user'=>['id'=>(int)$this->apiUser['id'],'email'=>$this->apiUser['email']],'realm'=>$realm]);
    }

    // GET /api/v1/wallet
    public function wallet() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $bal = $this->wallet->balance((int)$realm['id']);
        $this->json(['ok'=>true,'wallet'=>$bal]);
    }

    // GET /api/v1/buildings
    public function buildings() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $rows = $this->db->get_where('buildings',['realm_id'=>$realm['id']])->result_array();
        $this->json(['ok'=>true,'buildings'=>$rows]);
    }

    // GET /api/v1/research
    public function research() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $levels = $this->db->get_where('research_levels',['realm_id'=>$realm['id']])->result_array();
        $queue  = $this->db->order_by('finish_at','ASC')->get_where('research_queue',['realm_id'=>$realm['id']])->result_array();
        $defs   = $this->researchservice->listDefs();
        $this->json(['ok'=>true,'defs'=>$defs,'levels'=>$levels,'queue'=>$queue]);
    }

    // POST /api/v1/research/queue
    public function research_queue() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken, 'write');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $rid = (string)$this->input->post('research_id', TRUE);
        $lvl = (int)$this->input->post('target_level', TRUE);
        try {
            $id = $this->researchservice->queue((int)$realm['id'], $rid, $lvl);
            $this->json(['ok'=>true,'queue_id'=>$id]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'error'=>$e->getMessage()], 400);
        }
    }

    // GET /api/v1/arena/leaderboard
    public function arena_leaderboard() {
        $limit = (int)$this->input->get('limit', TRUE) ?: 50;
        $rows = $this->arenaservice->leaderboard($limit);
        $this->json(['ok'=>true,'leaderboard'=>$rows]);
    }

    // GET /api/v1/arena/history
    public function arena_history() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $rows = $this->arenaservice->history((int)$realm['id']);
        $this->json(['ok'=>true,'history'=>$rows]);
    }

    // POST /api/v1/arena/queue
    public function arena_queue() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken, 'arena');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $this->arenaservice->enqueue((int)$realm['id']);
        $this->json(['ok'=>true]);
    }

    // POST /api/v1/arena/cancel
    public function arena_cancel() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken, 'arena');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $this->arenaservice->dequeue((int)$realm['id']);
        $this->json(['ok'=>true]);
    }

    // POST /api/v1/battle/simulate   body: armyA json, armyB json
    public function battle_simulate() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $a = json_decode($this->input->post('armyA'), true);
        $b = json_decode($this->input->post('armyB'), true);
        if (!$a || !$b) $this->json(['ok'=>false,'error'=>'Invalid armies'], 400);
        $realm = $this->currentRealm();
        $rid = $realm ? (int)$realm['id'] : 0;
        $res = $this->engine->duel($rid, $rid, $a, $b);
        $this->json(['ok'=>true,'result'=>$res]);
    }
}
