<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Content_schema extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('colors')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'INT','auto_increment'=>TRUE],
                'code'=>['type'=>'VARCHAR','constraint'=>16],
                'name'=>['type'=>'VARCHAR','constraint'=>64],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('code', TRUE);
            $this->dbforge->create_table('colors', TRUE);
            $this->db->insert_batch('colors',[
                ['code'=>'red','name'=>'Red'],
                ['code'=>'blue','name'=>'Blue'],
                ['code'=>'green','name'=>'Green'],
                ['code'=>'white','name'=>'White'],
                ['code'=>'black','name'=>'Black'],
            ]);
        }
        if (!$this->db->table_exists('rarities')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'INT','auto_increment'=>TRUE],
                'code'=>['type'=>'VARCHAR','constraint'=>16],
                'name'=>['type'=>'VARCHAR','constraint'=>64],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('code', TRUE);
            $this->dbforge->create_table('rarities', TRUE);
            $this->db->insert_batch('rarities',[
                ['code'=>'simple','name'=>'Simple'],
                ['code'=>'average','name'=>'Average'],
                ['code'=>'complex','name'=>'Complex'],
                ['code'=>'ultimate','name'=>'Ultimate'],
                ['code'=>'ancient','name'=>'Ancient'],
            ]);
        }
        if (!$this->db->table_exists('units')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'INT','auto_increment'=>TRUE],
                'code'=>['type'=>'VARCHAR','constraint'=>32],
                'name'=>['type'=>'VARCHAR','constraint'=>128],
                'type'=>['type'=>'VARCHAR','constraint'=>16],
                'attack_types'=>['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'power'=>['type'=>'INT','default'=>0],
                'res_melee'=>['type'=>'FLOAT','default'=>0],
                'res_ranged'=>['type'=>'FLOAT','default'=>0],
                'res_flying'=>['type'=>'FLOAT','default'=>0],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('code', TRUE);
            $this->dbforge->create_table('units', TRUE);
        }
        if (!$this->db->table_exists('spells')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'INT','auto_increment'=>TRUE],
                'code'=>['type'=>'VARCHAR','constraint'=>32],
                'name'=>['type'=>'VARCHAR','constraint'=>128],
                'color_id'=>['type'=>'INT'],
                'rarity_id'=>['type'=>'INT'],
                'base_success'=>['type'=>'FLOAT','default'=>1.0],
                'mana_cost'=>['type'=>'INT','default'=>0],
                'effect'=>['type'=>'TEXT','null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('code', TRUE);
            $this->dbforge->create_table('spells', TRUE);
        }
        if (!$this->db->table_exists('items')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'INT','auto_increment'=>TRUE],
                'code'=>['type'=>'VARCHAR','constraint'=>32],
                'name'=>['type'=>'VARCHAR','constraint'=>128],
                'rarity_id'=>['type'=>'INT','null'=>TRUE],
                'base_success'=>['type'=>'FLOAT','default'=>1.0],
                'effect'=>['type'=>'TEXT','null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('code', TRUE);
            $this->dbforge->create_table('items', TRUE);
        }
        if (!$this->db->table_exists('heroes')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'INT','auto_increment'=>TRUE],
                'code'=>['type'=>'VARCHAR','constraint'=>32],
                'name'=>['type'=>'VARCHAR','constraint'=>128],
                'class'=>['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'bonus'=>['type'=>'TEXT','null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('code', TRUE);
            $this->dbforge->create_table('heroes', TRUE);
        }
        if (!$this->db->table_exists('resistances')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'INT','auto_increment'=>TRUE],
                'scope'=>['type'=>'VARCHAR','constraint'=>16],
                'target_code'=>['type'=>'VARCHAR','constraint'=>32,'null'=>TRUE],
                'attack_type'=>['type'=>'VARCHAR','constraint'=>16,'null'=>TRUE],
                'color_id'=>['type'=>'INT','null'=>TRUE],
                'value'=>['type'=>'FLOAT','default'=>0],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('resistances', TRUE);
        }
    }
    public function down() {
        foreach (['resistances','heroes','items','spells','units','rarities','colors'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
