<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Advanced_combat_api extends CI_Migration {
    public function up() {
        // Extend unit_def
        if (!$this->db->field_exists('type', 'unit_def')) {
            $this->dbforge->add_column('unit_def', [
                'type' => ['type'=>'VARCHAR','constraint'=>32,'default'=>'infantry'],
                'damage_type' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'physical'], // physical|magical
                'resist' => ['type'=>'JSON','null'=>TRUE], // {"physical":0.1,"magical":0.0}
                'speed' => ['type'=>'INT','constraint'=>11,'default'=>1],
                'morale' => ['type'=>'INT','constraint'=>11,'default'=>100],
            ]);
        }

        // API rate limiting
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'route' => ['type'=>'VARCHAR','constraint'=>120],
            'window_start' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            'count' => ['type'=>'INT','constraint'=>11,'default'=>0],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key(['user_id','route','window_start']);
        $this->dbforge->create_table('api_rate', TRUE);
    }

    public function down() {
        $this->dbforge->drop_table('api_rate', TRUE);
    }
}
