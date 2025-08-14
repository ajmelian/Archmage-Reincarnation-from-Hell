<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cachecli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('Caching');
        $this->load->library(['ArenaService','ResearchService','Wallet']);
        $this->load->database();
        $this->load->config('performance');
    }

    public function clear_tag($tag) {
        $this->caching->invalidateTag($tag);
        echo "Invalidated tag {$tag}\n";
    }

    public function warm() {
        // Warm defs
        $defs = $this->researchservice->listDefs();
        echo "Warmed research defs: ".count($defs)." entries\n";
        // Warm leaderboard
        $this->arenaservice->leaderboard(50);
        echo "Warmed arena leaderboard\n";
        // Warm wallets for first 10 realms
        $rs = $this->db->limit(10)->get('realms')->result_array();
        foreach ($rs as $r) { $this->wallet->balance((int)$r['id']); }
        echo "Warmed wallets for ".count($rs)." realms\n";
    }

    public function purge($prefix='') {
        if ($prefix) { $this->caching->deleteByPrefix($prefix); echo "Purged prefix: {$prefix}\n"; }
        else { echo "Provide prefix to purge.\n"; }
    }

    public function warm_market() {
        // Warmers placeholder
        echo "Market warm placeholder\n";
    }
    public function warm_auctions() { echo "Auctions warm placeholder\n"; }
    public function warm_all() { $this->warm_market(); $this->warm_auctions(); echo "Warm done\n"; }
}
