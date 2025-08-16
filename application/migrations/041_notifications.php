<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Notifications extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('notifications')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'user_id'=>['type'=>'BIGINT','unsigned'=>TRUE],
                'type'=>['type'=>'VARCHAR','constraint'=>32], // battle|market|alliance|system
                'title'=>['type'=>'VARCHAR','constraint'=>190],
                'body'=>['type'=>'TEXT','null'=>TRUE],
                'url'=>['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
                'read_at'=>['type'=>'INT','unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['user_id','created_at']);
            $this->dbforge->create_table('notifications', TRUE);
        }
    }
    public function down() {
        if ($this->db->table_exists('notifications')) $this->dbforge->drop_table('notifications', TRUE);
    }
}
