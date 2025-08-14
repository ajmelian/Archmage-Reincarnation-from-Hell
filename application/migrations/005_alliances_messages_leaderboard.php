<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alliances_messages_leaderboard extends CI_Migration {
    public function up() {
        // settings
        $this->dbforge->add_field([
            'key' => ['type'=>'VARCHAR','constraint'=>64],
            'value' => ['type'=>'TEXT','null'=>TRUE],
            'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('key', TRUE);
        $this->dbforge->create_table('settings', TRUE);
        $this->db->replace('settings', ['key'=>'tick_interval_seconds','value'=>'300','updated_at'=>time()]);
        $this->db->replace('settings', ['key'=>'next_tick_at','value'=>strval(time()+300),'updated_at'=>time()]);

        // alliances
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'name' => ['type'=>'VARCHAR','constraint'=>80],
            'tag' => ['type'=>'VARCHAR','constraint'=>10],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('alliances', TRUE);

        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'role' => ['type'=>'VARCHAR','constraint'=>20,'default'=>'member'], // leader|officer|member
            'joined_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('alliance_id');
        $this->dbforge->add_key('realm_id');
        $this->dbforge->create_table('alliance_members', TRUE);

        // messages (mailbox)
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'sender_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'receiver_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'subject' => ['type'=>'VARCHAR','constraint'=>120],
            'body' => ['type'=>'TEXT','null'=>TRUE],
            'is_read' => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('receiver_realm_id');
        $this->dbforge->create_table('messages', TRUE);

        // realm_reports (per turn)
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'tick' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE],
            'report' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('realm_id');
        $this->dbforge->add_key('tick');
        $this->dbforge->create_table('realm_reports', TRUE);

        // leaderboard cache
        $this->dbforge->add_field([
            'tick' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE],
            'data' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
            'generated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('tick', TRUE);
        $this->dbforge->create_table('leaderboard_cache', TRUE);
    }

    public function down() {
        $this->dbforge->drop_table('leaderboard_cache', TRUE);
        $this->dbforge->drop_table('realm_reports', TRUE);
        $this->dbforge->drop_table('messages', TRUE);
        $this->dbforge->drop_table('alliance_members', TRUE);
        $this->dbforge->drop_table('alliances', TRUE);
        $this->dbforge->drop_table('settings', TRUE);
    }
}
