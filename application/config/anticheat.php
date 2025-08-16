<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['anticheat'] = [
    // Multi-cuenta básica por IP
    'multi_account_ip_threshold' => 3,  // usuarios distintos por IP en 24h que disparan evento
    // Límites de transferencias
    'transfer_limits' => [
        'per_pair_daily_count' => 5,   // nº de transferencias de A->B por 24h
        'per_pair_daily_amount'=> 100000, // suma de recursos A->B en 24h
    ],
    // Cooldowns (seguridad genérica)
    'cooldowns' => [
        'market_post_seconds' => 30, // ejemplo para mercado
    ]
];
