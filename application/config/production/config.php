<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['csrf_protection'] = TRUE;
$config['csrf_regenerate'] = TRUE;
$config['csrf_expire'] = 300; // 5 minutos
$config['global_xss_filtering'] = FALSE; // usa escaping en vistas
