<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Anticheat_schema extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('session_log')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'user_id'=>['type'=>'BIGINT','unsigned'=>TRUE,'null'=>TRUE],
                'ip'=>['type'=>'VARCHAR','constraint'=>64],
                'ua'=>['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['user_id','ip']);
            $this->dbforge->create_table('session_log', TRUE);
        }
        if (!$this->db->table_exists('anticheat_events')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'user_id'=>['type'=>'BIGINT','unsigned'=>TRUE,'null'=>TRUE],
                'realm_id'=>['type'=>'BIGINT','unsigned'=>TRUE,'null'=>TRUE],
                'type'=>['type'=>'VARCHAR','constraint'=>32], // multi_ip | transfer_limit | cooldown_violation | manual
                'severity'=>['type'=>'TINYINT','unsigned'=>TRUE,'default'=>1], // 1-5
                'meta'=>['type'=>'TEXT','null'=>TRUE], // JSON
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('anticheat_events', TRUE);
        }
        if (!$this->db->table_exists('sanctions')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'user_id'=>['type'=>'BIGINT','unsigned'=>TRUE],
                'type'=>['type'=>'VARCHAR','constraint'=>32], // mute_market | temp_suspend | perm_ban
                'reason'=>['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
                'expires_at'=>['type'=>'INT','unsigned'=>TRUE,'null'=>TRUE],
                'revoked_at'=>['type'=>'INT','unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['user_id','type']);
            $this->dbforge->create_table('sanctions', TRUE);
        }
        if (!$this->db->table_exists('transfers_log')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'from_realm_id'=>['type'=>'BIGINT','unsigned'=>TRUE],
                'to_realm_id'=>['type'=>'BIGINT','unsigned'=>TRUE],
                'resource'=>['type'=>'VARCHAR','constraint'=>32], // gold|mana|item
                'amount'=>['type'=>'INT','unsigned'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['from_realm_id','to_realm_id']);
            $this->dbforge->create_table('transfers_log', TRUE);
        }
    }
    public function down() {
        foreach (['session_log','anticheat_events','sanctions','transfers_log'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
