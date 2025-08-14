<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Performance_indexes extends CI_Migration {
    private function addIndexIfMissing($table, $name, $cols) {
        $exists = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ".$this->db->escape($name))->row_array();
        if (!$exists) {
            $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$cols})");
        }
    }
    public function up() {
        if ($this->db->table_exists('chat_messages')) {
            $this->addIndexIfMissing('chat_messages','idx_channel_id_id','`channel_id`,`id`');
            $this->addIndexIfMissing('chat_messages','idx_created_at','`created_at`');
        }
        if ($this->db->table_exists('dm_messages')) {
            $this->addIndexIfMissing('dm_messages','idx_to_read','`to_realm_id`,`is_read`,`id`');
            $this->addIndexIfMissing('dm_messages','idx_created_at','`created_at`');
        }
        if ($this->db->table_exists('arena_matches')) {
            $this->addIndexIfMissing('arena_matches','idx_created','`created_at`');
        }
        if ($this->db->table_exists('research_queue')) {
            $this->addIndexIfMissing('research_queue','idx_realm_finish','`realm_id`,`finish_at`');
        }
        if ($this->db->table_exists('rate_counters')) {
            $this->addIndexIfMissing('rate_counters','idx_action_window','`action`,`window_start`');
        }
    }
    public function down() { /* no-op for safety */ }
}
