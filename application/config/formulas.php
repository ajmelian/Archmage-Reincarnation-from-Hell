<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Conjunto de fórmulas (paridad con el original).
 * Ajusta coeficientes aquí para evitar tocar lógica en runtime.
 */
$config['formulas'] = [
    'version'  => 'legacy',   // legacy | balanced | custom
    'enable'   => true,       // si true, Engine usará CombatFormula cuando proceda
    'tolerance'=> 0.0001,     // tolerancia para golden tests (float)

    'economy' => [
        // Producción base por edificio (multiplicadores)
        'gold_per_building' => 5.0,
        'mana_per_building' => 3.0,
        // Caps y stacking
        'gold_cap'          => 2.0,  // +200% máximo
        'mana_cap'          => 2.0,
        'research_cap'      => 2.0,
        // Diminishing returns
        'dr_gold'           => 0.0005,
        'dr_mana'           => 0.0005,
        'dr_research'       => 0.0004,
        // Investigación escalada
        'research_base_cost'=> 100,
        'research_growth'   => 1.12
    ],

    'combat' => [
        // Daño base (mantiene compatibilidad con Formula::baseDamage)
        'damage_scale'      => 0.15,
        'min_damage'        => 0,
        // Resistencias por escuela / tipo
        'resist_map'        => [
            'physical' => ['physical'=>0.10, 'magical'=>0.00],
            'magical'  => ['physical'=>0.00, 'magical'=>0.10]
        ],
        // Modificadores globales
        'school_bonus'      => [
            // ejemplo: 'pyromancy' => +0.10 daño mágico
        ],
        // Stacking (usa Formula.php caps/stacking)
    ],

    'spells' => [
        // Escalado de poder por nivel de investigación
        'power_per_level'   => 0.05,   // +5% por nivel
        // Costes
        'mana_cost_scale'   => 1.00,
        // Duraciones por defecto (ticks)
        'duration'          => [
            'buff'   => 3,
            'summon' => 0,
            'damage' => 0
        ],
        // Resistencias de objetivo por escuela (placeholder)
        'target_resist'     => [
            // 'undead' => ['magical'=>0.15]
        ]
    ]
];
