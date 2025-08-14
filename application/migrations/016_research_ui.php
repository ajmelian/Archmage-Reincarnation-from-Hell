<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Research_ui extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('research_def')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'VARCHAR','constraint'=>64],
                'name' => ['type'=>'VARCHAR','constraint'=>128],
                'category' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'description' => ['type'=>'TEXT','null'=>TRUE],
                'base_cost_research' => ['type'=>'INT','constraint'=>11,'default'=>10],
                'base_cost_gold' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'base_cost_mana' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'time_sec' => ['type'=>'INT','constraint'=>11,'default'=>60],
                'growth_rate' => ['type'=>'FLOAT','default'=>1.12],  // escalar por nivel
                'max_level' => ['type'=>'INT','constraint'=>11,'default'=>10],
                'prereqs' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON: { research_id: min_level, ... }
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('research_def', TRUE);

            // seed minimal
            $this->db->insert_batch('research_def', [
                ['id'=>'alchemy','name'=>'Alquimia','category'=>'economy','description'=>'Mejora producción de oro.','base_cost_research'=>20,'time_sec'=>60,'growth_rate'=>1.10,'max_level'=>10,'prereqs'=>null],
                ['id'=>'runecraft','name'=>'Rúnica','category'=>'mana','description'=>'Mejora producción de maná.','base_cost_research'=>20,'time_sec'=>60,'growth_rate'=>1.10,'max_level'=>10,'prereqs'=>null],
                ['id'=>'engineering','name'=>'Ingeniería','category'=>'tech','description'=>'Desbloquea mejoras avanzadas.','base_cost_research'=>30,'time_sec'=>90,'growth_rate'=>1.15,'max_level'=>10,'prereqs'=>json_encode(['alchemy'=>3])],
            ]);
        }

        if (!$this->db->table_exists('research_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>32], // queue|cancel|finish
                'research_id' => ['type'=>'VARCHAR','constraint'=>64],
                'level_target' => ['type'=>'INT','constraint'=>11,'default'=>1],
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','research_id','type']);
            $this->dbforge->create_table('research_logs', TRUE);
        }
    }

    public function down() {
        foreach (['research_def','research_logs'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
