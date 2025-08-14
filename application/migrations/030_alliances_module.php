<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alliances_module extends CI_Migration {
    public function up() {
        // alliances
        if (!$this->db->table_exists('alliances')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'name' => ['type'=>'VARCHAR','constraint'=>64],
                'tag' => ['type'=>'VARCHAR','constraint'=>8],
                'description' => ['type'=>'TEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('name', TRUE);
            $this->dbforge->add_key('tag', TRUE);
            $this->dbforge->create_table('alliances', TRUE);
        }
        // alliance_members
        if (!$this->db->table_exists('alliance_members')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'role' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'member'], // leader|officer|member
                'joined_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['alliance_id','realm_id'], TRUE);
            $this->dbforge->create_table('alliance_members', TRUE);
        }
        // alliance_invites
        if (!$this->db->table_exists('alliance_invites')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'from_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'to_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'status' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'pending'], // pending|accepted|revoked|expired
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['to_realm_id','status']);
            $this->dbforge->create_table('alliance_invites', TRUE);
        }
        // alliance_logs (para auditoría mínima)
        if (!$this->db->table_exists('alliance_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>32],
                'data' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['alliance_id','created_at']);
            $this->dbforge->create_table('alliance_logs', TRUE);
        }
        // realms.alliance_id
        if ($this->db->table_exists('realms')) {
            $fields = $this->db->list_fields('realms');
            if (!in_array('alliance_id', $fields)) {
                $this->db->query("ALTER TABLE `realms` ADD `alliance_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER `user_id`");
                $this->db->query("ALTER TABLE `realms` ADD INDEX `idx_alliance_id` (`alliance_id`)");
            }
        }
    }

    public function down() {
        foreach (['alliance_logs','alliance_invites','alliance_members','alliances'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
        // no revertimos realms.alliance_id por seguridad
    }
}
