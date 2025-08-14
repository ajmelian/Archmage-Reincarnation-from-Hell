<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Market_auctions extends CI_Migration {
    public function up() {
        // inventory (si falta)
        if (!$this->db->table_exists('inventory')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'item_id' => ['type'=>'VARCHAR','constraint'=>64],
                'qty' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','item_id'], TRUE);
            $this->dbforge->create_table('inventory', TRUE);
        }

        if (!$this->db->table_exists('market_listings')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'seller_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'item_id' => ['type'=>'VARCHAR','constraint'=>64],
                'qty' => ['type'=>'INT','constraint'=>11],
                'price_per_unit' => ['type'=>'INT','constraint'=>11], // en oro
                'deposit' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'tax_bps' => ['type'=>'INT','constraint'=>11,'default'=>250], // 2.5%
                'status' => ['type'=>'TINYINT','constraint'=>1,'default'=>0], // 0=active,1=sold,2=canceled,3=expired
                'buyer_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'trade_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['item_id','price_per_unit']);
            $this->dbforge->add_key(['seller_realm_id','status']);
            $this->dbforge->create_table('market_listings', TRUE);
        }

        if (!$this->db->table_exists('market_trades')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'listing_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'item_id' => ['type'=>'VARCHAR','constraint'=>64],
                'qty' => ['type'=>'INT','constraint'=>11],
                'price_per_unit' => ['type'=>'INT','constraint'=>11],
                'total_price' => ['type'=>'INT','constraint'=>11],
                'tax_paid' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'seller_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'buyer_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['item_id','created_at']);
            $this->dbforge->create_table('market_trades', TRUE);
        }

        if (!$this->db->table_exists('auctions')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'seller_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'item_id' => ['type'=>'VARCHAR','constraint'=>64],
                'qty' => ['type'=>'INT','constraint'=>11],
                'start_price' => ['type'=>'INT','constraint'=>11],
                'buyout_price' => ['type'=>'INT','constraint'=>11,'null'=>TRUE],
                'min_increment' => ['type'=>'INT','constraint'=>11],
                'deposit' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'tax_bps' => ['type'=>'INT','constraint'=>11,'default'=>250],
                'ends_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'status' => ['type'=>'TINYINT','constraint'=>1,'default'=>0], // 0=active,1=ended,2=canceled,3=expired
                'winner_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'final_price' => ['type'=>'INT','constraint'=>11,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['item_id','ends_at']);
            $this->dbforge->add_key(['seller_realm_id','status']);
            $this->dbforge->create_table('auctions', TRUE);
        }

        if (!$this->db->table_exists('auction_bids')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'auction_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'bidder_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'amount' => ['type'=>'INT','constraint'=>11],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['auction_id','amount']);
            $this->dbforge->create_table('auction_bids', TRUE);
        }
    }

    public function down() {
        foreach (['auction_bids','auctions','market_trades','market_listings'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
        // inventory se mantiene
    }
}
