<?php defined('BASEPATH') OR exit('No direct script access allowed');

class V1 extends MY_ApiController {
    public function __construct() {
        parent::__construct();
        $this->load->library(['ArenaService','ResearchService','Wallet','TalentTree','Engine','Caching','EconomyService']);
        $this->load->config('performance');
    }

    private function micro($key, $ttl, $cb) { return $this->caching->remember($key, $ttl, $cb); }

    private function currentRealm(): ?array {
        $u = $this->apiUser;
        if (!$u) return null;
        return $this->db->get_where('realms',['user_id'=>$u['id']])->row_array();
    }

    // GET /api/v1/me
    public function me() {
        $realm = $this->currentRealm();
        $ttl=(int)($this->config->item('performance')['api_ttl']['me'] ?? 5);
        $out = $this->micro('api:me:'.$this->apiUser['id'], $ttl, function(){ $r=$this->currentRealm(); return ['ok'=>true,'user'=>['id'=>(int)$this->apiUser['id'],'email'=>$this->apiUser['email']], 'realm'=>$r]; });
        $this->json($out);
    }

    // GET /api/v1/wallet
    public function wallet() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $ttl=(int)($this->config->item('performance')['api_ttl']['wallet'] ?? 3);
        $out = $this->micro('api:wallet:'.$realm['id'], $ttl, function() use ($realm){ return ['ok'=>true,'wallet'=>$this->wallet->balance((int)$realm['id'])]; });
        $this->json($out);
    }

    // GET /api/v1/buildings
    public function buildings() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $ttl=(int)($this->config->item('performance')['api_ttl']['buildings'] ?? 15);
        $out = $this->micro('api:buildings:'.$realm['id'], $ttl, function() use ($realm){ $rows=$this->db->get_where('buildings',['realm_id'=>$realm['id']])->result_array(); return ['ok'=>true,'buildings'=>$rows]; });
        $this->json($out);
    }

    // GET /api/v1/research
    public function research() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $ttl=(int)($this->config->item('performance')['api_ttl']['research'] ?? 20);
        $out = $this->micro('api:research:'.$realm['id'], $ttl, function() use ($realm){ $levels=$this->db->get_where('research_levels',['realm_id'=>$realm['id']])->result_array(); $queue=$this->db->order_by('finish_at','ASC')->get_where('research_queue',['realm_id'=>$realm['id']])->result_array(); $defs=$this->researchservice->listDefs(); return ['ok'=>true,'defs'=>$defs,'levels'=>$levels,'queue'=>$queue]; });
        $this->json($out);
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
        $ttl=(int)($this->config->item('performance')['api_ttl']['arena_leaderboard'] ?? 20);
        $out = $this->micro('api:arena:lb:'.$limit, $ttl, function() use($limit){ $rows=$this->arenaservice->leaderboard($limit); return ['ok'=>true,'leaderboard'=>$rows]; });
        $this->json($out);
    }

    // GET /api/v1/arena/history
    public function arena_history() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $ttl=(int)($this->config->item('performance')['api_ttl']['arena_history'] ?? 15);
        $out = $this->micro('api:arena:hist:'.$realm['id'], $ttl, function() use($realm){ $rows=$this->arenaservice->history((int)$realm['id']); return ['ok'=>true,'history'=>$rows]; });
        $this->json($out);
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



    // GET /api/v1/economy/preview
    public function economy_preview() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $p = $this->economyservice->preview((int)$realm['id']);
        $this->json(['ok'=>true,'preview'=>$p]);
    }

    // GET /api/v1/economy/params
    public function economy_params() {
        $rows = $this->db->order_by('key','ASC')->get('econ_params')->result_array();
        $this->json(['ok'=>true,'params'=>$rows]);
    }
