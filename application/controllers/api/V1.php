<?php defined('BASEPATH') OR exit('No direct script access allowed');

class V1 extends MY_ApiController {
    public function __construct() {
        parent::__construct();
        $this->load->library(['ArenaService','ResearchService','Wallet','TalentTree','Engine','Caching','EconomyService','MarketService','AuctionService','AllianceService','ModerationService','AuditLog','ExportService','RateLimiter']);
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



    // GET /api/v1/market/listings?item=iron_ore&limit=50
    public function market_listings() {
        $item = $this->input->get('item', TRUE);
        if ($item) $this->db->where('item_id',$item);
        $limit = (int)$this->input->get('limit', TRUE) ?: 50;
        $rows = $this->db->order_by('price_per_unit','ASC')->limit($limit)->get_where('market_listings',['status'=>0])->result_array();
        $this->json(['ok'=>true,'listings'=>$rows]);
    }

    // POST /api/v1/market/list  (scopes: write)
    public function market_list() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken, 'write');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $item = (string)$this->input->post('item_id', TRUE);
        $qty  = (int)$this->input->post('qty', TRUE);
        $ppu  = (int)$this->input->post('ppu', TRUE);
        try {
            $id = $this->marketservice->listItem((int)$realm['id'], $item, $qty, $ppu);
            $this->json(['ok'=>true,'id'=>$id]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'error'=>$e->getMessage()], 400);
        }
    }

    // POST /api/v1/market/buy
    public function market_buy() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $id = (int)$this->input->post('listing_id', TRUE);
        try {
            $tid = $this->marketservice->buy((int)$realm['id'], $id);
            $this->json(['ok'=>true,'trade_id'=>$tid]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'error'=>$e->getMessage()], 400);
        }
    }

    // GET /api/v1/auctions/active?item=...&limit=50
    public function auctions_active() {
        $item = $this->input->get('item', TRUE);
        if ($item) $this->db->where('item_id',$item);
        $limit = (int)$this->input->get('limit', TRUE) ?: 50;
        $rows = $this->db->order_by('ends_at','ASC')->limit($limit)->get_where('auctions',['status'=>0])->result_array();
        $this->json(['ok'=>true,'auctions'=>$rows]);
    }

    // POST /api/v1/auctions/create (scopes: write)
    public function auctions_create() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken, 'write');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $item = (string)$this->input->post('item_id', TRUE);
        $qty  = (int)$this->input->post('qty', TRUE);
        $start= (int)$this->input->post('start_price', TRUE);
        $buy  = $this->input->post('buyout_price', TRUE); $buy = ($buy===''? null : (int)$buy);
        $min  = (int)$this->input->post('minutes', TRUE);
        try {
            $id = $this->auctionservice->create((int)$realm['id'], $item, $qty, $start, $buy, $min);
            $this->json(['ok'=>true,'id'=>$id]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'error'=>$e->getMessage()], 400);
        }
    }

    // POST /api/v1/auctions/bid
    public function auctions_bid() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $id = (int)$this->input->post('auction_id', TRUE);
        $amount = (int)$this->input->post('amount', TRUE);
        try {
            $this->auctionservice->bid((int)$realm['id'], $id, $amount);
            $this->json(['ok'=>true]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'error'=>$e->getMessage()], 400);
        }
    }



    // GET /api/v1/alliance/me
    public function alliance_me() {
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $a = $this->allianceservice->allianceOfRealm((int)$realm['id']);
        if (!$a) $this->json(['ok'=>true,'alliance'=>null]);
        $members = $this->allianceservice->members((int)$a['id']);
        $this->json(['ok'=>true,'alliance'=>$a,'members'=>$members,'chat_channel'=>$this->allianceservice->chatChannelId((int)$a['id'])]);
    }

    // POST /api/v1/alliance/create
    public function alliance_create() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken,'write');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $name = (string)$this->input->post('name', TRUE);
        $tag = (string)$this->input->post('tag', TRUE);
        $desc = (string)$this->input->post('description', TRUE);
        try { $id = $this->allianceservice->create((int)$realm['id'],$name,$tag,$desc); $this->json(['ok'=>true,'id'=>$id]); }
        catch (Throwable $e) { $this->json(['ok'=>false,'error'=>$e->getMessage()], 400); }
    }

    // POST /api/v1/alliance/invite
    public function alliance_invite() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken,'write');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $to = (int)$this->input->post('to_realm_id', TRUE);
        try { $id = $this->allianceservice->invite((int)$realm['id'], $to); $this->json(['ok'=>true,'invite_id'=>$id]); }
        catch (Throwable $e) { $this->json(['ok'=>false,'error'=>$e->getMessage()], 400); }
    }

    // POST /api/v1/alliance/accept
    public function alliance_accept() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $inv = (int)$this->input->post('invite_id', TRUE);
        try { $this->allianceservice->accept((int)$realm['id'], $inv); $this->json(['ok'=>true]); }
        catch (Throwable $e) { $this->json(['ok'=>false,'error'=>$e->getMessage()], 400); }
    }

    // POST /api/v1/alliance/leave
    public function alliance_leave() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        try { $this->allianceservice->leave((int)$realm['id']); $this->json(['ok'=>true]); }
        catch (Throwable $e) { $this->json(['ok'=>false,'error'=>$e->getMessage()], 400); }
    }

    // POST /api/v1/alliance/promote
    public function alliance_promote() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken,'write');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $target = (int)$this->input->post('target_realm_id', TRUE);
        try { $this->allianceservice->promote((int)$realm['id'], $target); $this->json(['ok'=>true]); }
        catch (Throwable $e) { $this->json(['ok'=>false,'error'=>$e->getMessage()], 400); }
    }

    // POST /api/v1/alliance/demote
    public function alliance_demote() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken,'write');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $target = (int)$this->input->post('target_realm_id', TRUE);
        try { $this->allianceservice->demote((int)$realm['id'], $target); $this->json(['ok'=>true]); }
        catch (Throwable $e) { $this->json(['ok'=>false,'error'=>$e->getMessage()], 400); }
    }

    // POST /api/v1/alliance/kick
    public function alliance_kick() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->apiauth->enforceScope($this->apiToken,'write');
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $target = (int)$this->input->post('target_realm_id', TRUE);
        try { $this->allianceservice->kick((int)$realm['id'], $target); $this->json(['ok'=>true]); }
        catch (Throwable $e) { $this->json(['ok'=>false,'error'=>$e->getMessage()], 400); }
    }



    // POST /api/v1/report
    public function report() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $realm = $this->currentRealm(); if (!$realm) $this->json(['ok'=>false,'error'=>'No realm'], 404);
        $type = (string)$this->input->post('type', TRUE);
        $reason = (string)$this->input->post('reason', TRUE);
        $tType = (string)$this->input->post('target_type', TRUE);
        $tId = (string)$this->input->post('target_id', TRUE);
        try {
            $id = $this->moderationservice->report((int)$realm['id'], $type, $reason, $tType ?: null, $tId ?: null);
            $this->json(['ok'=>true,'id'=>$id]);
        } catch (Throwable $e) {
            $this->json(['ok'=>false,'error'=>$e->getMessage()], 400);
        }
    }



    // GET /api/v1/export?module=market_trades&format=csv&since=...
    public function export() {
        list($ok,$reset) = $this->ratelimiter->check('api_export', 60, 60);
        if (!$ok) { $this->json(['ok'=>false,'error'=>'rate_limited','reset'=>$reset], 429); return; }
        $this->apiauth->enforceScope($this->apiToken, 'read');
        $module = (string)$this->input->get('module', TRUE);
        $format = (string)$this->input->get('format', TRUE) ?: 'csv';
        $filters = [];
        foreach (['since','realm_id','user_id','alliance_id','auction_id','item_id','status','target_realm_id'] as $k) {
            $v = $this->input->get($k, TRUE); if ($v!==null && $v!=='') $filters[$k] = $v;
        }
        $rows = $this->exportservice->fetch($module, $filters);
        if ($format==='json') {
            $payload = json_encode($rows, JSON_UNESCAPED_UNICODE);
            $this->output->set_content_type('application/json'); 
            $etag = md5($payload);
            $this->output->set_header('ETag: '.$etag);
            $this->output->set_header('Cache-Control: public, max-age=30');
            $this->output->set_output($payload);
        } else {
            $csv = $this->exportservice->toCsv($rows);
            $this->output->set_content_type('text/csv'); 
            $etag = md5($csv);
            $this->output->set_header('ETag: '.$etag);
            $this->output->set_header('Cache-Control: public, max-age=30');
            $this->output->set_output($csv);
        }
    }
}