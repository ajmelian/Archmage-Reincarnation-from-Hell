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

$route['alliances'] = 'alliances/index';
$route['alliances/create'] = 'alliances/create';
$route['alliances/join/(:num)'] = 'alliances/join/$1';
$route['alliances/leave/(:num)'] = 'alliances/leave/$1';
$route['messages/inbox'] = 'messages/inbox';
$route['messages/send'] = 'messages/send';
$route['leaderboard'] = 'leaderboard/index';
$route['tick/set/(:num)'] = 'tick/set_interval/$1';
$route['tick/run-if-due'] = 'tick/run_if_due';
