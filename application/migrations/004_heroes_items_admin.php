<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Heroes_items_admin extends CI_Migration {
    public function up() {
        // users.role
        if (!$this->db->field_exists('role', 'users')) {
            $this->dbforge->add_column('users', [
                'role' => ['type'=>'VARCHAR','constraint'=>20,'default'=>'user']
            ]);
        }
        // hero_def add gold_cost
        if (!$this->db->field_exists('gold_cost', 'hero_def')) {
            $this->dbforge->add_column('hero_def', [
                'gold_cost' => ['type'=>'INT','constraint'=>11,'default'=>200]
            ]);
        }
        // item_def add allowed_classes JSON (optional)
        if (!$this->db->field_exists('allowed_classes', 'item_def')) {
            $this->dbforge->add_column('item_def', [
                'allowed_classes' => ['type'=>'JSON','null'=>TRUE]
            ]);
        }

        // realm_inventory (stackable items not yet equipped)
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'item_id' => ['type'=>'VARCHAR','constraint'=>64],
            'qty' => ['type'=>'INT','constraint'=>11,'default'=>0],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('realm_id');
        $this->dbforge->create_table('realm_inventory', TRUE);

        // realm_heroes (heroes owned by a realm)
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'hero_id' => ['type'=>'VARCHAR','constraint'=>64],
            'level' => ['type'=>'INT','constraint'=>11,'default'=>1],
            'stats' => ['type'=>'JSON','null'=>TRUE],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('realm_id');
        $this->dbforge->create_table('realm_heroes', TRUE);

        // hero_items (equipped items)
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'realm_hero_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'item_id' => ['type'=>'VARCHAR','constraint'=>64],
            'slot' => ['type'=>'VARCHAR','constraint'=>32],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('realm_hero_id');
        $this->dbforge->create_table('hero_items', TRUE);

        // seed a basic hero and item if empty
        $hasHero = $this->db->count_all_results('hero_def') > 0;
        if (!$hasHero) {
            $this->db->insert('hero_def', [
                'id'=>'commander','name'=>'Commander','class'=>'leader',
                'gold_cost'=>300,
                'base_stats'=>json_encode(['attack_bonus'=>0.05,'defense_bonus'=>0.05]),
            ]);
        }
        $hasItem = $this->db->count_all_results('item_def') > 0;
        if (!$hasItem) {
            $this->db->insert('item_def', [
                'id'=>'banner_of_valor','name'=>'Banner of Valor','slot'=>'trinket','cost'=>150,
                'modifiers'=>json_encode(['attack_bonus'=>0.10])
            ]);
            $this->db->insert('item_def', [
                'id'=>'tome_of_wisdom','name'=>'Tome of Wisdom','slot'=>'trinket','cost'=>120,
                'modifiers'=>json_encode(['research_bonus'=>0.10])
            ]);
        }
    }

    public function down() {
        $this->dbforge->drop_table('hero_items', TRUE);
        $this->dbforge->drop_table('realm_heroes', TRUE);
        $this->dbforge->drop_table('realm_inventory', TRUE);
        // columns remain
    }
}
