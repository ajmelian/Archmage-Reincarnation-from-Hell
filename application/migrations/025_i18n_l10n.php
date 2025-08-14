<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_I18n_l10n extends CI_Migration {
    public function up() {
        if ($this->db->table_exists('users')) {
            $fields = $this->db->list_fields('users');
            if (!in_array('locale', $fields)) {
                $this->db->query("ALTER TABLE `users` ADD `locale` VARCHAR(10) NULL AFTER `email`");
            }
            if (!in_array('timezone', $fields)) {
                $this->db->query("ALTER TABLE `users` ADD `timezone` VARCHAR(64) NULL AFTER `locale`");
            }
        }
    }
    public function down() {
        // No revertimos por compatibilidad
    }
}
