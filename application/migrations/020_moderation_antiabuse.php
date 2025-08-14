<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Moderation_antiabuse extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('moderation_mutes')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'scope' => ['type'=>'VARCHAR','constraint'=>16], // chat_global|chat_alliance|dm|all
                'reason' => ['type'=>'VARCHAR','constraint'=>128,'null'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','scope']);
            $this->dbforge->create_table('moderation_mutes', TRUE);
        }
        if (!$this->db->table_exists('moderation_blocks')) {
            $this->dbforge->add_field([
                'blocker_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'blocked_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['blocker_realm_id','blocked_realm_id'], TRUE);
            $this->dbforge->create_table('moderation_blocks', TRUE);
        }
        if (!$this->db->table_exists('moderation_reports')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'reporter_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'target_type' => ['type'=>'VARCHAR','constraint'=>16], // chat|dm|realm
                'target_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'reason' => ['type'=>'VARCHAR','constraint'=>128,'null'=>TRUE],
                'status' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'open'], // open|resolved|dismissed
                'resolution' => ['type'=>'VARCHAR','constraint'=>128,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'resolved_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['target_type','target_id']);
            $this->dbforge->create_table('moderation_reports', TRUE);
        }
        if (!$this->db->table_exists('rate_counters')) {
            $this->dbforge->add_field([
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'action' => ['type'=>'VARCHAR','constraint'=>32], // chat_post|dm_send
                'window_start' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'count' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['realm_id','action','window_start'], TRUE);
            $this->dbforge->create_table('rate_counters', TRUE);
        }
        if (!$this->db->table_exists('moderation_badwords')) {
            $this->dbforge->add_field([
                'token' => ['type'=>'VARCHAR','constraint'=>64],
            ]);
            $this->dbforge->add_key('token', TRUE);
            $this->dbforge->create_table('moderation_badwords', TRUE);
            $this->db->insert_batch('moderation_badwords', [
                ['token'=>'spamlink'], ['token'=>'ofensa'], ['token'=>'insulto']
            ]);
        }
    }
    public function down() {
        foreach (['moderation_mutes','moderation_blocks','moderation_reports','rate_counters','moderation_badwords'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
