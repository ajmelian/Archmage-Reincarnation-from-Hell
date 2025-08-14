<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Api_v1 extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('api_tokens')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'token_hash' => ['type'=>'VARCHAR','constraint'=>64], // sha256 hex
                'name' => ['type'=>'VARCHAR','constraint'=>64,'null'=>TRUE],
                'scopes' => ['type'=>'VARCHAR','constraint'=>128,'null'=>TRUE], // p.ej. 'read,write,arena'
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
                'last_used_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'revoked' => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['user_id','token_hash']);
            $this->dbforge->create_table('api_tokens', TRUE);
        }
    }
    public function down() {
        if ($this->db->table_exists('api_tokens')) $this->dbforge->drop_table('api_tokens', TRUE);
    }
}
