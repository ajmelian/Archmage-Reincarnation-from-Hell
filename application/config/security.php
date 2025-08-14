<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['security_ext'] = [
    'session' => [
        'expiration' => 300,   // 300s máximo
        'time_to_update' => 120,
        'regenerate_destroy' => TRUE,
        'bind_ip' => FALSE,
        'bind_user_agent' => TRUE,
    ],
    'login' => [
        'lock_minutes' => 10,
        'max_attempts' => 8,
        'rate_ip' => ['window_sec'=>60, 'max'=>30],
        'rate_user' => ['window_sec'=>60, 'max'=>20],
    ],
    'totp' => [
        'issuer' => 'Archmage',
        'digits' => 6,
        'period' => 30,
    ],
    'headers' => [
        'hsts' => 'max-age=31536000; includeSubDomains',
        'csp' => "default-src 'self' data: 'unsafe-inline' https:; img-src 'self' data: https:;",
        'frame_options' => 'DENY',
        'x_content_type' => 'nosniff',
        'referrer' => 'same-origin',
    ],
];
