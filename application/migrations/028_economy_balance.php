<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Economy_balance extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('econ_params')) {
            $this->dbforge->add_field([
                'key' => ['type'=>'VARCHAR','constraint'=>64],
                'value' => ['type'=>'TEXT'],
                'updated_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('key', TRUE);
            $this->dbforge->create_table('econ_params', TRUE);
        }
        if (!$this->db->table_exists('econ_modifiers')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'null'=>TRUE], // null = global
                'key' => ['type'=>'VARCHAR','constraint'=>64], // e.g. gold_mul, mana_add
                'value' => ['type'=>'DOUBLE'],
                'scope' => ['type'=>'VARCHAR','constraint'=>32,'null'=>TRUE], // e.g. pvp, pve, all
                'reason' => ['type'=>'VARCHAR','constraint'=>128,'null'=>TRUE],
                'expires_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE,'null'=>TRUE],
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','key']);
            $this->dbforge->create_table('econ_modifiers', TRUE);
        }
        if (!$this->db->table_exists('economy_history')) {
            $this->dbforge->add_field([
                'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
                'realm_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
                'resource' => ['type'=>'VARCHAR','constraint'=>16], // gold|mana|research
                'gross' => ['type'=>'INT','constraint'=>11],
                'upkeep' => ['type'=>'INT','constraint'=>11],
                'modifiers' => ['type'=>'INT','constraint'=>11],
                'net' => ['type'=>'INT','constraint'=>11],
                'snapshot' => ['type'=>'MEDIUMTEXT'], // JSON con detalle
                'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key(['realm_id','created_at']);
            $this->dbforge->create_table('economy_history', TRUE);
        }

        // Seed default params
        $defaults = [
            'base.gold' => 50,
            'base.mana' => 30,
            'base.research' => 20,
            // función de producción: out = max * (1 - exp(-k * x)), x ~ poder normalizado (0..inf)
            'curve.k' => 0.15,
            'curve.max_mul' => 6.0,  // multiplicador máximo sobre base al acercarse a infinito
            // anti-snowball: (factor según percentil; top penaliza, bottom bonifica)
            'snowball.top_percent' => 0.1,    // top 10% penalizado
            'snowball.top_penalty' => -0.15,  // -15%
            'snowball.bottom_percent' => 0.25,// bottom 25% bonificado
            'snowball.bottom_bonus' => 0.15,  // +15%
            // upkeep
            'upkeep.unit' => 1,          // oro por unidad
            'upkeep.building' => 0,      // oro por nivel de edificio (si se desea)
            // caps suaves por tick (anti-exploit)
            'cap.per_tick.gold' => 5000,
            'cap.per_tick.mana' => 4000,
            'cap.per_tick.research' => 2500,
        ];
        foreach ($defaults as $k=>$v) {
            $this->db->replace('econ_params', ['key'=>$k,'value'=>json_encode($v),'updated_at'=>time()]);
        }
    }

    public function down() {
        if ($this->db->table_exists('economy_history')) $this->dbforge->drop_table('economy_history', TRUE);
        if ($this->db->table_exists('econ_modifiers')) $this->dbforge->drop_table('econ_modifiers', TRUE);
        if ($this->db->table_exists('econ_params')) $this->dbforge->drop_table('econ_params', TRUE);
    }
}
