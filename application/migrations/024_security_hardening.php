<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Security_hardening extends CI_Migration {
    public function up() {
        if ($this->db->table_exists('users')) {
            $fields = $this->db->list_fields('users');
            if (!in_array('totp_secret', $fields)) {
                $this->db->query("ALTER TABLE `users` 
                    ADD `totp_secret` VARCHAR(64) NULL AFTER `password_hash`,
                    ADD `totp_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `totp_secret`,
                    ADD `backup_codes` TEXT NULL AFTER `totp_enabled`,
                    ADD `last_2fa_at` INT(10) UNSIGNED NULL AFTER `backup_codes`,
                    ADD `login_attempts` INT(11) NOT NULL DEFAULT 0 AFTER `last_2fa_at`,
                    ADD `locked_until` INT(10) UNSIGNED NULL AFTER `login_attempts`");
            }
        }
        if (!$this->db->table_exists('roles')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'INT','constraint'=>11,'auto_increment'=>TRUE],
                'name' => ['type'=>'VARCHAR','constraint'=>32],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('name', TRUE);
            $this->dbforge->create_table('roles', TRUE);
            $this->db->insert_batch('roles', [['name'=>'gm','created_at'=>time()],['name'=>'moderator','created_at'=>time()]]);
        }
        if (!$this->db->table_exists('user_roles')) {
            $this->dbforge->add_field([
                'user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'role_id' => ['type'=>'INT','constraint'=>11],
                'granted_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['user_id','role_id'], TRUE);
            $this->dbforge->create_table('user_roles', TRUE);
        }
    }
    public function down() {
        if ($this->db->table_exists('user_roles')) $this->dbforge->drop_table('user_roles', TRUE);
        if ($this->db->table_exists('roles')) $this->dbforge->drop_table('roles', TRUE);
        // No revertimos columnas de users por compatibilidad
    }
}
