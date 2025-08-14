<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Configuración del motor de juego.
 * - Stacking y Caps de bonos
 * - Parámetros de combate y economía
 * Ajusta estos valores para balancear el juego sin tocar la lógica.
 */

$config['rng_seed_base'] = 1337;

// Reglas de stacking (cómo se acumulan los bonos del mismo tipo)
$config['stacking'] = [
    'attack_bonus'  => 'additive',   // additive | multiplicative
    'defense_bonus' => 'additive',
    'gold_bonus'    => 'additive',
    'mana_bonus'    => 'additive',
    'research_bonus'=> 'additive',
];

// Límites máximos (p. ej. 0.75 = +75% máximo)
$config['caps'] = [
    'attack_bonus'  => 0.75,
    'defense_bonus' => 0.75,
    'gold_bonus'    => 2.00,
    'mana_bonus'    => 2.00,
    'research_bonus'=> 2.00,
];

// Parámetros de combate
$config['combat'] = [
    'damage_scale' => 0.15,    // factor sobre (Atk - Def)
    'min_damage'   => 0,       // suelo tras escalar
    'spread'       => 2,       // jitter determinista (no usado en esta versión)
    'targeting'    => 'proportional', // proportional | focus_low_hp | focus_high_hp
    'hp_per_unit'  => 1        // por defecto si la unidad no define hp
];

// Economía (placeholder ampliable)
$config['economy'] = [
    'land_explore_ratio' => 1.0 // +1 tierra por punto de explorar (simplificado)
];
