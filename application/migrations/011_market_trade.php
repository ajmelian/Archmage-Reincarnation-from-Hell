<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Market_trade extends CI_Migration {
    public function up() {
        // market_listings
        if (!$this->db->table_exists('market_listings')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'item_id' => ['type'=>'VARCHAR','constraint'=>64],
                'qty' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'price_per_unit' => ['type'=>'INT','constraint'=>11,'default'=>1],
                'currency' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'gold'],
                'tax_rate' => ['type'=>'FLOAT','default'=>0.05],
                'status' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'active'], // active|sold|canceled|expired|partial
                'sold_qty' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'buyer_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','item_id','status']);
            $this->dbforge->create_table('market_listings', TRUE);
        }

        // trade_offers (direct trade offer with confirmation)
        if (!$this->db->table_exists('trade_offers')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'from_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'to_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON: {items:[{item_id,qty}], gold:int}
                'status' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'pending'], // pending|accepted|declined|canceled|expired
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['from_realm_id','to_realm_id','status']);
            $this->dbforge->create_table('trade_offers', TRUE);
        }

        // market_logs
        if (!$this->db->table_exists('market_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>32], // listing|buy|cancel|expire|trade|error
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'ref_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE], // listing_id/offer_id/etc
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['type','realm_id']);
            $this->dbforge->create_table('market_logs', TRUE);
        }
    }

    public function down() {
        foreach (['market_listings','trade_offers','market_logs'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
