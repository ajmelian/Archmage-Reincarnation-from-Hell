<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['cache_ext'] = [
    'adapter' => 'file',     // 'redis' o 'memcached' si está disponible
    'backup'  => 'file',
    'key_prefix' => 'am_',
    'default_ttl' => 60,     // segundos
];
