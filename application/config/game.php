<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['game'] = [];


$config['game']['combat'] = [
    // Banda de ataque por Net Power (NP)
    'attack_band' => ['min'=>0.80, 'max'=>2.00],
    // Excepción por counter
    'counters_ignore_band' => true,
    // Si atacante > 2x NP del defensor en counter -> botín 0
    'counter_loot_if_ratio_over' => 2.0,
    // Pairing
    'pair_min_ratio' => 0.10, // no golpear stacks minúsculos (<10% del atacante)
    'max_stacks' => 10, // máximo de stacks que entran
    // Multiplicadores de orden para el "stack power" (solo para ordenar)
    'stack_order_multipliers' => ['ranged'=>1.0, 'melee'=>1.5, 'flying'=>2.25],
];

$config['game']['protections'] = [
    'damage_threshold_percent_24h' => 30,
    'pillage_max_24h' => 10,
    'volcano_max_24h' => 10,
];


$config['game']['prebattle'] = [
    // Límite máximo efectivo de barrera (75%)
    'barrier_max' => 0.75,
    // Resistencias por color por defecto (se pueden sobrescribir por reino)
    'colors' => ['red','blue','green','white','black'],
    // Si un ítem no tiene color: solo aplica barrera
    // Probabilidad base si no se define en el hechizo/ítem
    'default_spell_base_success' => 1.0,
];


$config['game']['battle_phase'] = [
    // Eficiencia base de daño por tipo de ataque (ajustable a datos reales)
    'attack_efficiency' => ['melee'=>1.0, 'ranged'=>1.0, 'flying'=>1.0],
    // Tope de daño que puede infligirse a un stack en un solo asalto (proporción de su poder)
    'damage_cap_vs_stack' => 1.0, // 100% del 'power' del stack como máximo en la fase
    // Si un stack tiene ataques híbridos, prioriza golpear unidades NO voladoras con melee
    'hybrid_prefers_ground' => true,
];


$config['game']['battle_modes'] = [
    'regular' => ['turn_cost'=>2, 'mana_cost'=>0, 'loot_rate'=>0.15, 'land_percent'=>0.02],
    'siege'   => ['turn_cost'=>3, 'mana_cost'=>25, 'loot_rate'=>0.10, 'land_percent'=>0.03],
    'pillage' => ['turn_cost'=>1, 'mana_cost'=>0, 'loot_rate'=>0.25, 'land_percent'=>0.00],
];

$config['game']['loot'] = [
    'base_gold_per_np' => 1.0, // oro por NP perdido del defensor antes de multiplicadores
    'min_loot' => 0,
    'max_loot' => 1000000,
];

$config['game']['war'] = [
    'points_per_np' => 1,   // puntos de guerra por NP neto (def_loss - att_loss) a favor del ganador
    'points_per_land' => 10,
];
