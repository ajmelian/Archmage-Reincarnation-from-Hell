<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Runtime_bonuses extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('compiled_bonuses')) {
            $this->dbforge->add_field([
                'realm_id'   => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'scope'      => ['type'=>'VARCHAR','constraint'=>16], // economy|combat
                'payload'    => ['type'=>'MEDIUMTEXT','null'=>TRUE],  // JSON
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['realm_id','scope'], TRUE);
            $this->dbforge->create_table('compiled_bonuses', TRUE);
        }
        if (!$this->db->table_exists('equipment')) {
            $this->dbforge->add_field([
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'hero_id'  => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE], // opcional
                'slot'     => ['type'=>'VARCHAR','constraint'=>32],
                'item_id'  => ['type'=>'VARCHAR','constraint'=>64],
                'set_id'   => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['realm_id','slot'], TRUE);
            $this->dbforge->create_table('equipment', TRUE);
        }
    }
    public function down() {
        foreach (['compiled_bonuses','equipment'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
