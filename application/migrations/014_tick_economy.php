<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tick_economy extends CI_Migration {
    public function up() {
        // tick_state
        if (!$this->db->table_exists('tick_state')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'INT','constraint'=>11,'auto_increment'=>TRUE],
                'tick_no' => ['type'=>'BIGINT','constraint'=>20,'default'=>0],
                'last_tick_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
                'locked_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('tick_state', TRUE);
            $this->db->insert('tick_state', ['tick_no'=>0,'last_tick_at'=>0,'locked_at'=>0]);
        }

        // buildings (por reino y tipo)
        if (!$this->db->table_exists('buildings')) {
            $this->dbforge->add_field([
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'building_id' => ['type'=>'VARCHAR','constraint'=>64],
                'level' => ['type'=>'INT','constraint'=>11,'default'=>0], // o cantidad si es stackable
                'qty' => ['type'=>'INT','constraint'=>11,'default'=>0],  // para minas/pozos
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['realm_id','building_id'], TRUE);
            $this->dbforge->create_table('buildings', TRUE);
        }

        // building_queue
        if (!$this->db->table_exists('building_queue')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'building_id' => ['type'=>'VARCHAR','constraint'=>64],
                'qty' => ['type'=>'INT','constraint'=>11,'default'=>1],
                'finish_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','finish_at']);
            $this->dbforge->create_table('building_queue', TRUE);
        }

        // research_levels
        if (!$this->db->table_exists('research_levels')) {
            $this->dbforge->add_field([
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'research_id' => ['type'=>'VARCHAR','constraint'=>64],
                'level' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['realm_id','research_id'], TRUE);
            $this->dbforge->create_table('research_levels', TRUE);
        }

        // research_queue
        if (!$this->db->table_exists('research_queue')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'research_id' => ['type'=>'VARCHAR','constraint'=>64],
                'level_target' => ['type'=>'INT','constraint'=>11,'default'=>1],
                'finish_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','finish_at']);
            $this->dbforge->create_table('research_queue', TRUE);
        }

        // active_effects (buffs/debuffs)
        if (!$this->db->table_exists('active_effects')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'effect_id' => ['type'=>'VARCHAR','constraint'=>64],
                'data' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','effect_id']);
            $this->dbforge->create_table('active_effects', TRUE);
        }

        // add research column to wallets if missing
        if ($this->db->table_exists('wallets') && !$this->db->field_exists('research', 'wallets')) {
            $this->dbforge->add_column('wallets', [
                'research' => ['type'=>'BIGINT','constraint'=>20,'default'=>0]
            ]);
        }
    }

    public function down() {
        foreach (['tick_state','buildings','building_queue','research_levels','research_queue','active_effects'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
        // keep wallets.research
    }
}
