<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Moderation_logging extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('audit_log')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'action' => ['type'=>'VARCHAR','constraint'=>64],
                'target_type' => ['type'=>'VARCHAR','constraint'=>32,'null'=>TRUE],
                'target_id' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'ip' => ['type'=>'VARCHAR','constraint'=>45,'null'=>TRUE],
                'ua' => ['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
                'meta' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['user_id','created_at']);
            $this->dbforge->add_key(['realm_id','created_at']);
            $this->dbforge->create_table('audit_log', TRUE);
        }
        if (!$this->db->table_exists('mod_flags')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'reporter_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>16], // chat|profile|realm|market|other
                'target_type' => ['type'=>'VARCHAR','constraint'=>32,'null'=>TRUE],
                'target_id' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'reason' => ['type'=>'TEXT'],
                'status' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'pending'], // pending|resolved|rejected
                'mod_user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'resolution' => ['type'=>'TEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'resolved_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['status','created_at']);
            $this->dbforge->create_table('mod_flags', TRUE);
        }
        if (!$this->db->table_exists('mod_actions')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'mod_user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'target_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'action' => ['type'=>'VARCHAR','constraint'=>32], // mute_chat|suspend_market|ban_arena|warn
                'reason' => ['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'meta' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['target_realm_id','action']);
            $this->dbforge->create_table('mod_actions', TRUE);
        }
    }
    public function down() {
        foreach (['mod_actions','mod_flags','audit_log'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
