<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['i18n'] = [
    'default_lang'   => 'es',
    'default_locale' => 'es_ES',
    'fallback_lang'  => 'en',
    'supported'      => ['es' => 'Español', 'en' => 'English'],
    'cookie_name'    => 'am_lang',
    'cookie_days'    => 365,
    'auto_detect'    => true, // Accept-Language si no hay preferencia del usuario
];
