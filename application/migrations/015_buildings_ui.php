<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Buildings_ui extends CI_Migration {
    public function up() {
        // building_def
        if (!$this->db->table_exists('building_def')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'VARCHAR','constraint'=>64],
                'name' => ['type'=>'VARCHAR','constraint'=>128],
                'category' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'base_cost_gold' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'base_cost_mana' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'build_time_sec' => ['type'=>'INT','constraint'=>11,'default'=>60],
                'growth_rate' => ['type'=>'FLOAT','default'=>1.0], // multiplicador por unidad adicional
                'max_qty' => ['type'=>'INT','constraint'=>11,'default'=>0], // 0 = ilimitado
                'icon' => ['type'=>'VARCHAR','constraint'=>128,'null'=>TRUE],
                'description' => ['type'=>'TEXT','null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('building_def', TRUE);

            // seed minimal
            $this->db->insert_batch('building_def', [
                ['id'=>'farm','name'=>'Granja','category'=>'economy','base_cost_gold'=>50,'base_cost_mana'=>0,'build_time_sec'=>30,'growth_rate'=>1.05,'max_qty'=>0,'icon'=>null,'description'=>'Produce oro (vía fórmula).'],
                ['id'=>'mana_well','name'=>'Pozo de Maná','category'=>'economy','base_cost_gold'=>30,'base_cost_mana'=>10,'build_time_sec'=>40,'growth_rate'=>1.06,'max_qty'=>0,'icon'=>null,'description'=>'Aumenta la generación de maná.'],
                ['id'=>'lab','name'=>'Laboratorio','category'=>'research','base_cost_gold'=>80,'base_cost_mana'=>20,'build_time_sec'=>60,'growth_rate'=>1.07,'max_qty'=>0,'icon'=>null,'description'=>'Acelera investigación.']
            ]);
        }

        // building_logs
        if (!$this->db->table_exists('building_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>32], // queue|cancel|finish|demolish
                'building_id' => ['type'=>'VARCHAR','constraint'=>64],
                'qty' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','building_id','type']);
            $this->dbforge->create_table('building_logs', TRUE);
        }
    }

    public function down() {
        foreach (['building_def','building_logs'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
