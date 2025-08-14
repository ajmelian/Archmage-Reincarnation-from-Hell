<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['market'] = [
    'tax_rate' => 0.05,          // 5% impuesto sobre precio total
    'min_price_floor' => 1,      // precio mínimo por unidad
    'listing_lifetime' => 72*3600, // segundos (3 días)
    'max_daily_sell_listings' => 50,
    'max_daily_buy_operations' => 200,
    'currencies' => ['gold'],    // se puede extender a ['gold','mana']
];
