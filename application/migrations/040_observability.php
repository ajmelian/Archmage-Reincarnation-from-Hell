<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Observability extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('audit_log')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'user_id'=>['type'=>'BIGINT','unsigned'=>TRUE,'null'=>TRUE],
                'realm_id'=>['type'=>'BIGINT','unsigned'=>TRUE,'null'=>TRUE],
                'action'=>['type'=>'VARCHAR','constraint'=>64],
                'meta'=>['type'=>'TEXT','null'=>TRUE],
                'ip'=>['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['user_id','action']);
            $this->dbforge->create_table('audit_log', TRUE);
        }
        if (!$this->db->table_exists('metrics_counters')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'metric_key'=>['type'=>'VARCHAR','constraint'=>64],
                'day'=>['type'=>'INT','unsigned'=>TRUE], // YYYYMMDD
                'value'=>['type'=>'BIGINT','unsigned'=>TRUE,'default'=>0],
                'updated_at'=>['type'=>'INT','unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['metric_key','day'], TRUE);
            $this->dbforge->create_table('metrics_counters', TRUE);
        }
        if (!$this->db->table_exists('app_events')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'level'=>['type'=>'VARCHAR','constraint'=>16], // info|warning|error
                'source'=>['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'message'=>['type'=>'TEXT'],
                'meta'=>['type'=>'TEXT','null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('app_events', TRUE);
        }
    }
    public function down() {
        foreach (['audit_log','metrics_counters','app_events'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
