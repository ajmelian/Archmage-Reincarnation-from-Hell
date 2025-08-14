<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Security_twofa extends CI_Migration {
    public function up() {
        if ($this->db->table_exists('users')) {
            $fields = $this->db->list_fields('users');
            if (!in_array('twofa_secret', $fields)) {
                $this->db->query("ALTER TABLE `users` ADD `twofa_secret` VARCHAR(64) NULL DEFAULT NULL AFTER `password`");
            }
            if (!in_array('twofa_enabled', $fields)) {
                $this->db->query("ALTER TABLE `users` ADD `twofa_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `twofa_secret`");
                $this->db->query("ALTER TABLE `users` ADD INDEX `idx_twofa_enabled` (`twofa_enabled`)");
            }
            if (!in_array('last_login_at', $fields)) {
                $this->db->query("ALTER TABLE `users` ADD `last_login_at` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `twofa_enabled`");
            }
            if (!in_array('last_login_ip', $fields)) {
                $this->db->query("ALTER TABLE `users` ADD `last_login_ip` VARCHAR(45) NULL DEFAULT NULL AFTER `last_login_at`");
            }
        }
    }
    public function down() {
        // no revert por seguridad de datos
    }
}
