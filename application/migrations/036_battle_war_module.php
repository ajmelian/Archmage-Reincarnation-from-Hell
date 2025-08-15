<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Battle_War_Module extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('battles')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'battle_key' => ['type'=>'VARCHAR','constraint'=>64],
                'seed' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>16], // regular|siege|pillage
                'is_counter' => ['type'=>'TINYINT','constraint'=>1,'unsigned'=>TRUE,'default'=>0],
                'attacker_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'defender_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'attacker_alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'defender_alliance_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'attacker_np_before' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'defender_np_before' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'attacker_np_loss' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'defender_np_loss' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'attacker_win' => ['type'=>'TINYINT','constraint'=>1,'unsigned'=>TRUE,'default'=>0],
                'loot_gold' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'loot_mana' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'land_taken' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'report' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'resolved_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('battle_key', TRUE);
            $this->dbforge->add_key(['attacker_realm_id','defender_realm_id']);
            $this->dbforge->create_table('battles', TRUE);
        }
        if (!$this->db->table_exists('wars')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'alliance_a_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'alliance_b_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'status' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'active'], // active|cease|ended
                'started_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'ended_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'score_a' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'score_b' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'land_delta_a' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'land_delta_b' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'battles_count' => ['type'=>'INT','constraint'=>11,'default'=>0],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['alliance_a_id','alliance_b_id']);
            $this->dbforge->create_table('wars', TRUE);
        }
        if (!$this->db->table_exists('war_battles')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'war_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'battle_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['war_id','battle_id']);
            $this->dbforge->create_table('war_battles', TRUE);
        }
    }
    public function down() {
        foreach (['war_battles','wars','battles'] as $t) if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
    }
}
