<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Arena_pvp extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('arena_seasons')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'INT','constraint'=>11,'auto_increment'=>TRUE],
                'name' => ['type'=>'VARCHAR','constraint'=>64],
                'starts_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'ends_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'active' => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('arena_seasons', TRUE);
            $now = time();
            $this->db->insert('arena_seasons', ['name'=>'Temporada 1','starts_at'=>$now-86400,'ends_at'=>$now+30*86400,'active'=>1]);
        }
        if (!$this->db->table_exists('arena_ratings')) {
            $this->dbforge->add_field([
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'season_id' => ['type'=>'INT','constraint'=>11],
                'elo' => ['type'=>'INT','constraint'=>11,'default'=>1000],
                'wins' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'losses' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'draws' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'last_match_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
            ]);
            $this->dbforge->add_key(['realm_id','season_id'], TRUE);
            $this->dbforge->create_table('arena_ratings', TRUE);
        }
        if (!$this->db->table_exists('arena_queue')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'season_id' => ['type'=>'INT','constraint'=>11],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'rating_snapshot' => ['type'=>'INT','constraint'=>11,'default'=>1000],
                'enqueued_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['season_id','enqueued_at']);
            $this->dbforge->create_table('arena_queue', TRUE);
        }
        if (!$this->db->table_exists('arena_matches')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'season_id' => ['type'=>'INT','constraint'=>11],
                'realm_a' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'realm_b' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'elo_a' => ['type'=>'INT','constraint'=>11,'default'=>1000],
                'elo_b' => ['type'=>'INT','constraint'=>11,'default'=>1000],
                'result' => ['type'=>'VARCHAR','constraint'=>8,'null'=>TRUE], // A|B|draw
                'score_a' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'score_b' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'resolved_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['season_id','created_at']);
            $this->dbforge->create_table('arena_matches', TRUE);
        }
        if (!$this->db->table_exists('arena_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>16], // queue|match|elo
                'season_id' => ['type'=>'INT','constraint'=>11],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'match_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['season_id','type']);
            $this->dbforge->create_table('arena_logs', TRUE);
        }
    }

    public function down() {
        foreach (['arena_seasons','arena_ratings','arena_queue','arena_matches','arena_logs'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
