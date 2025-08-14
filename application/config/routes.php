<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'game';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// API mínima para órdenes
$route['api/orders']['post'] = 'orders/submit';
