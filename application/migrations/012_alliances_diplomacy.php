<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alliances_diplomacy extends CI_Migration {
    public function up() {
        // alliances
        if (!$this->db->table_exists('alliances')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'name' => ['type'=>'VARCHAR','constraint'=>64],
                'tag'  => ['type'=>'VARCHAR','constraint'=>16],
                'description' => ['type'=>'TEXT','null'=>TRUE],
                'leader_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('tag');
            $this->dbforge->create_table('alliances', TRUE);
        }

        // alliance_members
        if (!$this->db->table_exists('alliance_members')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'role' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'member'], // member|officer|leader
                'joined_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['alliance_id','realm_id']);
            $this->dbforge->create_table('alliance_members', TRUE);
        }

        // alliance_invites
        if (!$this->db->table_exists('alliance_invites')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'from_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'to_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'status' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'pending'], // pending|accepted|declined|canceled|expired
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['alliance_id','to_realm_id','status']);
            $this->dbforge->create_table('alliance_invites', TRUE);
        }

        // alliance_bank
        if (!$this->db->table_exists('alliance_bank')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'gold' => ['type'=>'BIGINT','constraint'=>20,'default'=>0],
                'mana' => ['type'=>'BIGINT','constraint'=>20,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('alliance_id');
            $this->dbforge->create_table('alliance_bank', TRUE);
        }

        // alliance_logs
        if (!$this->db->table_exists('alliance_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>32], // join|leave|invite|promote|demote|bank|diplomacy|war_event
                'actor_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['alliance_id','type']);
            $this->dbforge->create_table('alliance_logs', TRUE);
        }

        // diplomacy (between alliances)
        if (!$this->db->table_exists('diplomacy')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'a1_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'a2_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'state' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'neutral'], // neutral|nap|allied|war
                'started_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'ends_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'terms' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON
                'last_changed_by' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'war_score_a' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'war_score_b' => ['type'=>'INT','constraint'=>11,'default'=>0],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['a1_id','a2_id']);
            $this->dbforge->create_table('diplomacy', TRUE);
        }

        // war_events (attached to diplomacy rows in state='war')
        if (!$this->db->table_exists('war_events')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'diplo_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'event_type' => ['type'=>'VARCHAR','constraint'=>32], // battle|raid|score_adjust
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['diplo_id','event_type']);
            $this->dbforge->create_table('war_events', TRUE);
        }
    }

    public function down() {
        foreach (['alliances','alliance_members','alliance_invites','alliance_bank','alliance_logs','diplomacy','war_events'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
