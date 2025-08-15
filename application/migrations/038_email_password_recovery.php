<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Email_password_recovery extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('password_resets')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'user_id'=>['type'=>'BIGINT','unsigned'=>TRUE,'null'=>TRUE],
                'email'=>['type'=>'VARCHAR','constraint'=>190],
                'token'=>['type'=>'VARCHAR','constraint'=>64],
                'ip'=>['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'ua'=>['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
                'used_at'=>['type'=>'INT','unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('token', TRUE);
            $this->dbforge->create_table('password_resets', TRUE);
        }
        if (!$this->db->table_exists('email_verifications')) {
            $this->dbforge->add_field([
                'id'=>['type'=>'BIGINT','auto_increment'=>TRUE],
                'user_id'=>['type'=>'BIGINT','unsigned'=>TRUE],
                'email'=>['type'=>'VARCHAR','constraint'=>190],
                'token'=>['type'=>'VARCHAR','constraint'=>64],
                'created_at'=>['type'=>'INT','unsigned'=>TRUE],
                'verified_at'=>['type'=>'INT','unsigned'=>TRUE,'null'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('token', TRUE);
            $this->dbforge->create_table('email_verifications', TRUE);
        }
    }
    public function down() {
        foreach (['password_resets','email_verifications'] as $t) {
            if ($this->db->table_exists($t)) $this->dbforge->drop_table($t, TRUE);
        }
    }
}
