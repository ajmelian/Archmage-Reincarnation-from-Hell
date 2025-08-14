<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Backups_tools extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('backup_jobs')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>32], // db_full|tables|seed_export|seed_import|restore
                'status' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'done'], // done|error
                'filename' => ['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
                'meta' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // JSON
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'finished_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('backup_jobs', TRUE);
        }
    }
    public function down() {
        if ($this->db->table_exists('backup_jobs')) $this->dbforge->drop_table('backup_jobs', TRUE);
    }
}
