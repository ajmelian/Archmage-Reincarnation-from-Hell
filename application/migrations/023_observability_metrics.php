<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Observability_metrics extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('metrics_counter')) {
            $this->dbforge->add_field([
                'name' => ['type'=>'VARCHAR','constraint'=>96],
                'labels' => ['type'=>'TEXT','null'=>TRUE], // JSON ordenado
                'window_start' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'count' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['name','window_start','labels(32)'], TRUE);
            $this->dbforge->create_table('metrics_counter', TRUE);
        }
        if (!$this->db->table_exists('metrics_summary')) {
            $this->dbforge->add_field([
                'name' => ['type'=>'VARCHAR','constraint'=>96],
                'labels' => ['type'=>'TEXT','null'=>TRUE],
                'window_start' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'count' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'default'=>0],
                'sum_ms' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'default'=>0],
                'min_ms' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE,'default'=>2147483647],
                'max_ms' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE,'default'=>0],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key(['name','window_start','labels(32)'], TRUE);
            $this->dbforge->create_table('metrics_summary', TRUE);
        }
    }
    public function down() {
        if ($this->db->table_exists('metrics_counter')) $this->dbforge->drop_table('metrics_counter', TRUE);
        if ($this->db->table_exists('metrics_summary')) $this->dbforge->drop_table('metrics_summary', TRUE);
    }
}
