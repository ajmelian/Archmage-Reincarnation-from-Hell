<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Admin_liveops extends CI_Migration {
    public function up() {
        // users.is_admin
        if ($this->db->table_exists('users')) {
            $fields = $this->db->list_fields('users');
            if (!in_array('is_admin', $fields)) {
                $this->db->query("ALTER TABLE `users` ADD `is_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password_hash`");
            }
        }
        // gm_actions
        if (!$this->db->table_exists('gm_actions')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'admin_user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'action' => ['type'=>'VARCHAR','constraint'=>64],
                'target' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE], // realm_id|user_id|match_id etc
                'payload' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['admin_user_id','action']);
            $this->dbforge->create_table('gm_actions', TRUE);
        }
    }
    public function down() {
        if ($this->db->table_exists('gm_actions')) $this->dbforge->drop_table('gm_actions', TRUE);
        // Nota: no eliminamos la columna is_admin por seguridad
    }
}
