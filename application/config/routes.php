<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'game';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/* Placeholders for existing modules (will be overwritten/merged if present) */
$route['battle/(:num)'] = 'battle/view/$1';
$route['api/login'] = 'api_auth/login';
$route['api/state'] = 'api_game/state';

$route['admin/import'] = 'admin/import/index';
$route['admin/import/run'] = 'admin/import/run';

$route['battle/json/(:num)'] = 'battle/json/$1';
