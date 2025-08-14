<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['arena'] = [
    'k_factor' => 32,            // ELO K
    'search_delta' => 200,       // Diferencia de ELO inicialmente aceptada
    'search_expand_sec' => 60,   // Cada N segundos expandimos rango
    'expand_step' => 50,         // +50 ELO por expansión
    'reward' => ['gold'=>50,'mana'=>10], // recompensa base por victoria (opcional)
];
