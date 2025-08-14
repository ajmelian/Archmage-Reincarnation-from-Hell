<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Inventories_wallets extends CI_Migration {
    public function up() {
        // wallets (oro/maná por reino)
        if (!$this->db->table_exists('wallets')) {
            $this->dbforge->add_field([
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'gold' => ['type'=>'BIGINT','constraint'=>20,'default'=>0],
                'mana' => ['type'=>'BIGINT','constraint'=>20,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('realm_id', TRUE);
            $this->dbforge->create_table('wallets', TRUE);
        }
        // inventories (stack por item)
        if (!$this->db->table_exists('inventories')) {
            $this->dbforge->add_field([
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'item_id' => ['type'=>'VARCHAR','constraint'=>64],
                'qty' => ['type'=>'BIGINT','constraint'=>20,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['realm_id','item_id'], TRUE);
            $this->dbforge->create_table('inventories', TRUE);
        }
        // logs
        if (!$this->db->table_exists('wallet_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'res' => ['type'=>'VARCHAR','constraint'=>8], // gold|mana
                'delta' => ['type'=>'BIGINT','constraint'=>20],
                'reason' => ['type'=>'VARCHAR','constraint'=>32],
                'ref_type' => ['type'=>'VARCHAR','constraint'=>32,'null'=>TRUE],
                'ref_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','res']);
            $this->dbforge->create_table('wallet_logs', TRUE);
        }
        if (!$this->db->table_exists('inventory_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'item_id' => ['type'=>'VARCHAR','constraint'=>64],
                'delta' => ['type'=>'BIGINT','constraint'=>20],
                'reason' => ['type'=>'VARCHAR','constraint'=>32],
                'ref_type' => ['type'=>'VARCHAR','constraint'=>32,'null'=>TRUE],
                'ref_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','item_id']);
            $this->dbforge->create_table('inventory_logs', TRUE);
        }
    }
    public function down() {
        foreach (['wallets','inventories','wallet_logs','inventory_logs'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
