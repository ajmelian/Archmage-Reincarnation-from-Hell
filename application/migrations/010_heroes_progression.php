<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Heroes_progression extends CI_Migration {
    public function up() {
        // talent_def
        if (!$this->db->table_exists('talent_def')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'VARCHAR','constraint'=>64],
                'hero_class' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'name' => ['type'=>'VARCHAR','constraint'=>128],
                'description' => ['type'=>'TEXT','null'=>TRUE],
                'max_rank' => ['type'=>'INT','constraint'=>11,'default'=>3],
                'effects' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('talent_def', TRUE);
        }

        // hero_progress
        if (!$this->db->table_exists('hero_progress')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'hero_id' => ['type'=>'VARCHAR','constraint'=>64],
                'level' => ['type'=>'INT','constraint'=>11,'default'=>1],
                'xp' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'talent_points' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','hero_id']);
            $this->dbforge->create_table('hero_progress', TRUE);
        }

        // hero_talents
        if (!$this->db->table_exists('hero_talents')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'hero_id' => ['type'=>'VARCHAR','constraint'=>64],
                'talent_id' => ['type'=>'VARCHAR','constraint'=>64],
                'rank' => ['type'=>'INT','constraint'=>11,'default'=>0],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','hero_id','talent_id']);
            $this->dbforge->create_table('hero_talents', TRUE);
        }

        // item_def extensions
        if ($this->db->table_exists('item_def')) {
            if (!$this->db->field_exists('rarity', 'item_def')) {
                $this->dbforge->add_column('item_def', [
                    'rarity' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'common']
                ]);
            }
            if (!$this->db->field_exists('set_id', 'item_def')) {
                $this->dbforge->add_column('item_def', [
                    'set_id' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE]
                ]);
            }
        }

        // item_set_def
        if (!$this->db->table_exists('item_set_def')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'VARCHAR','constraint'=>64],
                'name' => ['type'=>'VARCHAR','constraint'=>128],
                'bonuses' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON por conteo equipados
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('item_set_def', TRUE);
        }

        // drop tables
        if (!$this->db->table_exists('drop_table_def')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'VARCHAR','constraint'=>64],
                'name' => ['type'=>'VARCHAR','constraint'=>128],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('drop_table_def', TRUE);
        }
        if (!$this->db->table_exists('drop_table_entry')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'table_id' => ['type'=>'VARCHAR','constraint'=>64],
                'item_id' => ['type'=>'VARCHAR','constraint'=>64],
                'weight' => ['type'=>'INT','constraint'=>11,'default'=>1],
                'min_qty' => ['type'=>'INT','constraint'=>11,'default'=>1],
                'max_qty' => ['type'=>'INT','constraint'=>11,'default'=>1],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['table_id']);
            $this->dbforge->create_table('drop_table_entry', TRUE);
        }

        if (!$this->db->table_exists('drop_logs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'source' => ['type'=>'VARCHAR','constraint'=>64],
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('drop_logs', TRUE);
        }
    }

    public function down() {
        foreach (['hero_talents','hero_progress','talent_def','item_set_def','drop_table_entry','drop_table_def','drop_logs'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
        // no se eliminan columnas añadidas en item_def para no romper datos
    }
}
