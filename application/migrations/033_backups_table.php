<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Backups_table extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('backups')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'filename' => ['type'=>'VARCHAR','constraint'=>255],
                'size_bytes' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'default'=>0],
                'checksum' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'type' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'db'], // db|files
                'note' => ['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
                'created_by_user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['type','created_at']);
            $this->dbforge->create_table('backups', TRUE);
        }
    }
    public function down() {
        if ($this->db->table_exists('backups')) $this->dbforge->drop_table('backups', TRUE);
    }
}
