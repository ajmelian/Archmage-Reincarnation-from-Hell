<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Mechanics_rules extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('pvp_damage')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'attacker_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'defender_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'damage_from_attacker_to_defender' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'damage_from_defender_to_attacker' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'battle_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['attacker_realm_id','defender_realm_id','created_at']);
            $this->dbforge->create_table('pvp_damage', TRUE);
        }
        if (!$this->db->table_exists('protections')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>24], // damage|council|pillage|volcano|meditation|novice|frozen|cemetery
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'data' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON adicional
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','type']);
            $this->dbforge->create_table('protections', TRUE);
        }
        if (!$this->db->table_exists('action_counters')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>24], // pillage_24h|volcano_24h
                'window_start' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'count' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','type']);
            $this->dbforge->create_table('action_counters', TRUE);
        }
    }
    public function down() {
        foreach (['action_counters','protections','pvp_damage'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
