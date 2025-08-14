<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Marketcli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library(['MarketService','AuctionService']);
    }
    public function expire() {
        $n1 = $this->marketservice->expireOld();
        $n2 = $this->auctionservice->finalizeExpired();
        echo "Expired listings: {$n1}; Finalized auctions: {$n2}\n";
    }
}
