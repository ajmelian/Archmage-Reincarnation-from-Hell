<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['tick'] = [
    'period_sec' => 60,          // un tick por minuto (ajústalo para producción)
    'batch_size' => 100,         // nº de reinos por pasada
    'upkeep' => [
        'gold_per_unit' => 0,    // si tienes 'armies' con qty, puedes activar coste fijo
        'mana_per_unit' => 0,
    ],
    'building_costs' => [
        // Opcional: cost models si quieres validar colas
    ]
];
