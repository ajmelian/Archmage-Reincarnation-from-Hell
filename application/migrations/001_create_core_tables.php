<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_core_tables extends CI_Migration {

    public function up() {
        // ci_sessions
        $this->dbforge->add_field([
            'id' => ['type'=>'VARCHAR','constraint'=>128,'null'=>FALSE],
            'ip_address' => ['type'=>'VARCHAR','constraint'=>45,'null'=>FALSE],
            'timestamp' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'default'=>0],
            'data' => ['type'=>'BLOB','null'=>FALSE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('ci_sessions', TRUE);

        // users
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'email' => ['type'=>'VARCHAR','constraint'=>120,'unique'=>TRUE],
            'pass_hash' => ['type'=>'VARCHAR','constraint'=>255],
            'display_name' => ['type'=>'VARCHAR','constraint'=>80],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users', TRUE);

        // realms (reinos)
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'name' => ['type'=>'VARCHAR','constraint'=>100],
            'race' => ['type'=>'VARCHAR','constraint'=>50, 'null'=>TRUE],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            'state' => ['type'=>'JSON','null'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->create_table('realms', TRUE);

        // definitions: units, heroes, items, spells
        $this->dbforge->add_field([
            'id' => ['type'=>'VARCHAR','constraint'=>64],
            'name' => ['type'=>'VARCHAR','constraint'=>120],
            'cost' => ['type'=>'INT','constraint'=>11,'default'=>0],
            'attack' => ['type'=>'INT','constraint'=>11,'default'=>0],
            'defense' => ['type'=>'INT','constraint'=>11,'default'=>0],
            'hp' => ['type'=>'INT','constraint'=>11,'default'=>1],
            'tags' => ['type'=>'JSON','null'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('unit_def', TRUE);

        $this->dbforge->add_field([
            'id' => ['type'=>'VARCHAR','constraint'=>64],
            'name' => ['type'=>'VARCHAR','constraint'=>120],
            'class' => ['type'=>'VARCHAR','constraint'=>64, 'null'=>TRUE],
            'base_stats' => ['type'=>'JSON','null'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('hero_def', TRUE);

        $this->dbforge->add_field([
            'id' => ['type'=>'VARCHAR','constraint'=>64],
            'name' => ['type'=>'VARCHAR','constraint'=>120],
            'slot' => ['type'=>'VARCHAR','constraint'=>32, 'null'=>TRUE],
            'modifiers' => ['type'=>'JSON','null'=>TRUE],
            'cost' => ['type'=>'INT','constraint'=>11,'default'=>0],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('item_def', TRUE);

        $this->dbforge->add_field([
            'id' => ['type'=>'VARCHAR','constraint'=>64],
            'name' => ['type'=>'VARCHAR','constraint'=>120],
            'school' => ['type'=>'VARCHAR','constraint'=>64, 'null'=>TRUE],
            'cost' => ['type'=>'INT','constraint'=>11,'default'=>0],
            'effect' => ['type'=>'JSON','null'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('spell_def', TRUE);

        // orders
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'tick' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE],
            'idempotency_key' => ['type'=>'CHAR','constraint'=>32],
            'payload' => ['type'=>'JSON','null'=>FALSE],
            'status' => ['type'=>"ENUM('pending','applied','rejected')",'default'=>'pending'],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->add_key('tick');
        $this->dbforge->add_key('idempotency_key', TRUE);
        $this->dbforge->create_table('orders', TRUE);

        // turns
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'tick' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE],
            'resolved_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            'notes' => ['type'=>'VARCHAR','constraint'=>255,'null'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('tick');
        $this->dbforge->create_table('turns', TRUE);

        // battles (registro simple)
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'tick' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE],
            'attacker_user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'defender_user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'log' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('tick');
        $this->dbforge->create_table('battles', TRUE);
    }

    public function down() {
        $this->dbforge->drop_table('battles', TRUE);
        $this->dbforge->drop_table('turns', TRUE);
        $this->dbforge->drop_table('orders', TRUE);
        $this->dbforge->drop_table('spell_def', TRUE);
        $this->dbforge->drop_table('item_def', TRUE);
        $this->dbforge->drop_table('hero_def', TRUE);
        $this->dbforge->drop_table('unit_def', TRUE);
        $this->dbforge->drop_table('realms', TRUE);
        $this->dbforge->drop_table('users', TRUE);
        $this->dbforge->drop_table('ci_sessions', TRUE);
    }
}
