<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['api'] = [
    'rate_limit' => ['window_sec'=>60, 'max'=>120],  // 120 req / 60s por token
    'cors' => [
        'enabled' => true,
        'allow_origin' => '*',       // ajusta en producción
        'allow_headers' => 'Authorization, Content-Type',
        'allow_methods' => 'GET, POST, OPTIONS',
    ]
];
