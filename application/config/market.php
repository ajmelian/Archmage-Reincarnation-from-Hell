<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['market'] = [
    'fee_bps' => 250,           // 2.5% fee al vendedor (sobre total)
    'deposit_bps' => 50,        // 0.5% depósito (se devuelve al vender/cancelar; se pierde al expirar)
    'listing_hours' => 24,      // duración por defecto (h)
    'max_active_listings' => 20,
    // límites de precio respecto al precio de referencia
    'min_factor' => 0.5,
    'max_factor' => 2.0,
    'allow_without_ref' => true, // si no hay precio de referencia, permite listar con floor 1 oro
    'soft_extend_seconds' => 30, // subastas: extender si se puja en los últimos X segundos
    'min_increment_bps' => 500,  // 5% del precio actual mínimo de incremento
    'auction_min_minutes' => 30,
    'auction_max_days' => 7,
    // referencias opcionales (override) por item_id => price_per_unit
    'ref_prices' => [
        // 'iron_ore' => 120,
        // 'mana_crystal' => 200,
    ],
    // rate-limit por ventana (reutiliza rate_counters)
    'rate' => [
        'listings' => ['window_sec'=>3600,'max'=>50], // publicar
        'bids'     => ['window_sec'=>60,'max'=>30],   // pujas
        'buys'     => ['window_sec'=>60,'max'=>30],   // compras
    ],
];
