<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Cabeceras de seguridad y CSP
$config['security_headers'] = [
    'hsts_seconds' => 15552000, // 180 días (solo si HTTPS)
    'x_frame_options' => 'SAMEORIGIN',
    'x_content_type_options' => 'nosniff',
    'referrer_policy' => 'strict-origin-when-cross-origin',
    'permissions_policy' => "geolocation=(), microphone=(), camera=()",
    // CSP base; añade los CDNs que uses en vistas (bootstrap, etc.)
    // Nota: puedes ajustar 'unsafe-inline' si generas nonces.
    'csp' => "default-src 'self'; img-src 'self' data: https://api.qrserver.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;",
];

// Opciones de hashing (rehash si cambia PASSWORD_DEFAULT)
$config['passwords'] = [
    'algo' => PASSWORD_DEFAULT,
    'options' => [], // p.ej. ['memory_cost'=>131072,'time_cost'=>4,'threads'=>2] para Argon2id
];
