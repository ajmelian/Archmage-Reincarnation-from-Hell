<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_flow_tables extends CI_Migration {

    public function up() {
        // building_def
        $this->dbforge->add_field([
            'id' => ['type'=>'VARCHAR','constraint'=>64],
            'name' => ['type'=>'VARCHAR','constraint'=>120],
            'cost' => ['type'=>'INT','constraint'=>11,'default'=>0],
            'outputs' => ['type'=>'JSON','null'=>TRUE], // {"gold":10,"mana":0,"research":0,"land":0}
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('building_def', TRUE);

        // research_def
        $this->dbforge->add_field([
            'id' => ['type'=>'VARCHAR','constraint'=>64],
            'name' => ['type'=>'VARCHAR','constraint'=>120],
            'cost' => ['type'=>'INT','constraint'=>11,'default'=>100], // puntos de investigación necesarios
            'effect' => ['type'=>'JSON','null'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('research_def', TRUE);

        // Starter seed data
        $this->db->insert_batch('building_def', [
            ['id'=>'mine','name'=>'Mine','cost'=>50,'outputs'=>json_encode(['gold'=>10,'mana'=>0,'research'=>0,'land'=>0])],
            ['id'=>'lab','name'=>'Laboratory','cost'=>80,'outputs'=>json_encode(['gold'=>0,'mana'=>0,'research'=>5,'land'=>0])],
            ['id'=>'barracks','name'=>'Barracks','cost'=>60,'outputs'=>json_encode(['gold'=>0,'mana'=>0,'research'=>0,'land'=>0])],
            ['id'=>'mana_well','name'=>'Mana Well','cost'=>70,'outputs'=>json_encode(['gold'=>0,'mana'=>8,'research'=>0,'land'=>0])],
        ]);

        $this->db->insert_batch('research_def', [
            ['id'=>'warfare_1','name'=>'Warfare I','cost'=>100,'effect'=>json_encode(['attack_bonus'=>0.05])],
            ['id'=>'warding_1','name'=>'Warding I','cost'=>100,'effect'=>json_encode(['defense_bonus'=>0.05])],
            ['id'=>'alchemy_1','name'=>'Alchemy I','cost'=>100,'effect'=>json_encode(['gold_bonus'=>0.05])],
            ['id'=>'sorcery_1','name'=>'Sorcery I','cost'=>100,'effect'=>json_encode(['mana_bonus'=>0.05])],
        ]);
    }

    public function down() {
        $this->dbforge->drop_table('research_def', TRUE);
        $this->dbforge->drop_table('building_def', TRUE);
    }
}
