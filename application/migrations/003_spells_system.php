<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Spells_system extends CI_Migration {
    public function up() {
        // Extend spell_def
        if (!$this->db->field_exists('type', 'spell_def')) {
            $this->dbforge->add_column('spell_def', [
                'type' => ['type'=>'VARCHAR','constraint'=>32,'null'=>TRUE],
                'target' => ['type'=>'VARCHAR','constraint'=>16,'null'=>TRUE], // self|enemy
                'power' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'duration' => ['type'=>'INT','constraint'=>11,'default'=>0],  // in ticks
                'mana_cost' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'research_cost' => ['type'=>'INT','constraint'=>11,'default'=>0],
                'params' => ['type'=>'JSON','null'=>TRUE],
            ]);
        }

        // Active effects on realms
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'spell_id' => ['type'=>'VARCHAR','constraint'=>64],
            'expires_tick' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE],
            'data' => ['type'=>'JSON','null'=>TRUE],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('realm_id');
        $this->dbforge->create_table('realm_effects', TRUE);

        // Spell logs
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'tick' => ['type'=>'INT','constraint'=>11,'unsigned'=>TRUE],
            'caster_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
            'target_realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE],
            'spell_id' => ['type'=>'VARCHAR','constraint'=>64],
            'log' => ['type'=>'MEDIUMTEXT','null'=>TRUE],
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('tick');
        $this->dbforge->create_table('spell_logs', TRUE);

        // Seed example spells
        $this->db->insert_batch('spell_def', [
            ['id'=>'summon_wolves','name'=>'Summon Wolves','type'=>'summon','target'=>'self','power'=>0,'duration'=>0,'mana_cost'=>30,'research_cost'=>80,'effect'=>json_encode(['summon'=>['unitId'=>'wolf','qty'=>10]])],
            ['id'=>'battle_fury','name'=>'Battle Fury','type'=>'buff_attack','target'=>'self','power'=>10,'duration'=>3,'mana_cost'=>40,'research_cost'=>100,'effect'=>json_encode(['attack_bonus'=>0.10])],
            ['id'=>'stone_skin','name'=>'Stone Skin','type'=>'buff_defense','target'=>'self','power'=>10,'duration'=>3,'mana_cost'=>40,'research_cost'=>100,'effect'=>json_encode(['defense_bonus'=>0.10])],
            ['id'=>'fire_blast','name'=>'Fire Blast','type'=>'damage_army','target'=>'enemy','power'=>50,'duration'=>0,'mana_cost'=>60,'research_cost'=>120,'effect'=>json_encode(['damage'=>50])],
        ]);
    }

    public function down() {
        $this->dbforge->drop_table('spell_logs', TRUE);
        $this->dbforge->drop_table('realm_effects', TRUE);
        // Columns remain for simplicity
    }
}
