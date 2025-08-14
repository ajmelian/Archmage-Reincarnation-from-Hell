<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Battles_timeline extends CI_Migration {
    public function up() {
        // defensively try to add columns if table exists
        if ($this->db->table_exists('battles')) {
            $fields = [];
            if (!$this->db->field_exists('timeline', 'battles')) {
                $fields['timeline'] = ['type'=>'MEDIUMTEXT','null'=>TRUE];
            }
            if (!$this->db->field_exists('winner', 'battles')) {
                $fields['winner'] = ['type'=>'VARCHAR','constraint'=>8,'null'=>TRUE];
            }
            if (!$this->db->field_exists('lossesA', 'battles')) {
                $fields['lossesA'] = ['type'=>'MEDIUMTEXT','null'=>TRUE];
            }
            if (!$this->db->field_exists('lossesB', 'battles')) {
                $fields['lossesB'] = ['type'=>'MEDIUMTEXT','null'=>TRUE];
            }
            if ($fields) $this->dbforge->add_column('battles', $fields);
        }
    }
    public function down() {
        if ($this->db->table_exists('battles')) {
            if ($this->db->field_exists('timeline','battles')) $this->dbforge->drop_column('battles','timeline');
            if ($this->db->field_exists('winner','battles')) $this->dbforge->drop_column('battles','winner');
            if ($this->db->field_exists('lossesA','battles')) $this->dbforge->drop_column('battles','lossesA');
            if ($this->db->field_exists('lossesB','battles')) $this->dbforge->drop_column('battles','lossesB');
        }
    }
}
