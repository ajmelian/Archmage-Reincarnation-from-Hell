<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'game';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// API mínima para órdenes
$route['api/orders']['post'] = 'orders/submit';

$route['auth/login'] = 'auth/login';
$route['auth/register'] = 'auth/register';
$route['auth/logout'] = 'auth/logout';

$route['admin'] = 'admin/defs/index';
$route['admin/defs'] = 'admin/defs/index';
$route['admin/defs/edit/(:any)/(:any)'] = 'admin/defs/edit/$1/$2';
$route['admin/logs'] = 'admin/logs/index';
