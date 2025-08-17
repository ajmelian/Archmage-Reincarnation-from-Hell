<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CLI demos de mecánicas de batalla/pre-battle.
 *
 * Uso:
 *   php public/index.php mechanicscli prebattle_demo
 *   php public/index.php mechanicscli resolve_demo
 */
class Mechanicscli extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Asegura que sólo se pueda invocar por CLI
        if (function_exists('is_cli')) {
            if (!is_cli()) { show_404(); }
        } else {
            // Fallback por si is_cli() no está disponible
            if (PHP_SAPI !== 'cli') { show_404(); }
        }
    }

    /**
     * Demostración de capa pre-battle:
     * - Barreras del defensor
     * - Resistencias por color
     * - Cálculo de modifier de botín en counter si NP_atk > 2× NP_def
     */
    public function prebattle_demo()
    {
        $this->load->library('PreBattleService'); // -> $this->prebattleservice
        $this->load->library('BattlePolicy');     // -> $this->battlepolicy

        $payload = [
            'battle_id'   => 123,
            'attacker'    => ['realm_id' => 1, 'np' => 20000],
            'defender'    => [
                'realm_id'      => 2,
                'np'            => 8000,
                'barrier_pct'   => 0.60,                 // 60% barrera defensiva
                'color_resists' => ['red' => 0.25],      // +25% vs rojo
            ],
            'attack_spell' => ['id' => 100, 'name' => 'Stun',  'color' => 'red', 'base_success' => 0.65],
            'attack_item'  => ['id' => 10,  'name' => 'Sunray','base_success' => 1.00],
            'is_counter'   => true
        ];

        try {
            $res = $this->prebattleservice->resolve($payload);

            // Modifier de botín según política (incluye regla loot=0 si NP_atk > 2× NP_def en counter)
            $res['loot_modifier'] = $this->battlepolicy->lootModifier(
                $payload['attacker']['np'],
                $payload['defender']['np'],
                true // is_counter
            );

            echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        } catch (Throwable $e) {
            // En caso de error, imprime un JSON de diagnóstico
            $err = [
                'ok'    => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine()
            ];
            echo json_encode($err, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
    }

    /**
     * Demostración de resolución de fase de daño:
     * - Ordenación de stacks
     * - Emparejamiento
     * - Cálculo de daño por fase con resistencias por tipo de ataque
     */
    public function resolve_demo()
    {
        $this->load->library(['Engine', 'BattlePolicy', 'BattleResults']);

        $att = [
            'realm_id' => 1, 'np' => 20000,
            'stacks'   => [
                ['id'=>1, 'type'=>'flying', 'attack_types'=>['ranged'], 'power'=>120, 'unit_resists'=>['ranged'=>0.10]],
                ['id'=>2, 'type'=>'melee',  'attack_types'=>['melee'],  'power'=>80,  'unit_resists'=>[]],
            ]
        ];
        $def = [
            'realm_id' => 2, 'np' => 15000,
            'stacks'   => [
                ['id'=>10, 'type'=>'melee',  'attack_types'=>['melee'],  'power'=>90,  'unit_resists'=>['melee'=>0.25]],
                ['id'=>11, 'type'=>'flying', 'attack_types'=>['ranged'], 'power'=>110, 'unit_resists'=>['ranged'=>0.20]],
            ]
        ];

        try {
            $att_order = $this->engine->stack_order($att['stacks']);
            $def_order = $this->engine->stack_order($def['stacks']);
            $pairs     = $this->engine->pairing($att_order, $def_order);
            $phase     = $this->engine->damage_phase($att_order, $def_order, $pairs);

            $out = [
                'attacker_order' => $att_order,
                'defender_order' => $def_order,
                'pairs'          => $pairs,
                'phase'          => $phase
            ];
            echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        } catch (Throwable $e) {
            $err = [
                'ok'    => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine()
            ];
            echo json_encode($err, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
    }
}
