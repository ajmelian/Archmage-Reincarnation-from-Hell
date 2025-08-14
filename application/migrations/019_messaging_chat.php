<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Messaging_chat extends CI_Migration {
    public function up() {
        // Direct messages
        if (!$this->db->table_exists('dm_messages')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'from_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'to_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'subject' => ['type'=>'VARCHAR','constraint'=>120,'null'=>TRUE],
                'body' => ['type'=>'MEDIUMTEXT'],
                'read_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'deleted_by_from' => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
                'deleted_by_to' => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['to_realm_id','created_at']);
            $this->dbforge->create_table('dm_messages', TRUE);
        }

        // Chat channels
        if (!$this->db->table_exists('chat_channels')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>16], // global|alliance|private
                'key' => ['type'=>'VARCHAR','constraint'=>64],  // 'global', 'ally-<id>', 'priv-<id>'
                'name' => ['type'=>'VARCHAR','constraint'=>64],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['type','key'], TRUE);
            $this->dbforge->create_table('chat_channels', TRUE);
            // seed global & trade
            $now = time();
            $this->db->insert_batch('chat_channels', [
                ['type'=>'global','key'=>'global','name'=>'Global','created_at'=>$now],
                ['type'=>'global','key'=>'trade','name'=>'Comercio','created_at'=>$now],
            ]);
        }

        // Chat messages
        if (!$this->db->table_exists('chat_messages')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'channel_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'text' => ['type'=>'TEXT'],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['channel_id','created_at']);
            $this->dbforge->create_table('chat_messages', TRUE);
        }

        // Chat members (para canales privados; alianza se valida por alliance_members)
        if (!$this->db->table_exists('chat_members')) {
            $this->dbforge->add_field([
                'channel_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'role' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'member'],
                'joined_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['channel_id','realm_id'], TRUE);
            $this->dbforge->create_table('chat_members', TRUE);
        }
    }

    public function down() {
        foreach (['dm_messages','chat_channels','chat_messages','chat_members'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
