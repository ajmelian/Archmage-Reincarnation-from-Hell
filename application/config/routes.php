<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Rutas personalizadas
| -------------------------------------------------------------------------
| Añade aquí las rutas necesarias. CI3 evalúa de arriba a abajo.
*/

$route['default_controller'] = 'game';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/* --- Admin --- */
$route['admin'] = 'admin/defs/index';
$route['admin/defs'] = 'admin/defs/index';
$route['admin/defs/edit/(:any)/(:any)'] = 'admin/defs/edit/$1/$2';
$route['admin/logs'] = 'admin/logs/index';

/* --- Juego / sistemas sociales --- */
$route['alliances'] = 'alliances/index';
$route['alliances/create'] = 'alliances/create';
$route['alliances/join/(:num)'] = 'alliances/join/$1';
$route['alliances/leave/(:num)'] = 'alliances/leave/$1';

$route['messages/inbox'] = 'messages/inbox';
$route['messages/send']  = 'messages/send';

$route['leaderboard'] = 'leaderboard/index';

/* --- Turnos y ticks --- */
$route['tick/set/(:num)']     = 'tick/set_interval/$1';
$route['tick/run-if-due']     = 'tick/run_if_due';

/* --- Torneos --- */
$route['tournaments'] = 'tournaments/index';
$route['tournaments/create'] = 'tournaments/create';
$route['tournaments/season/(:num)'] = 'tournaments/season/$1';

/* --- Battle replay --- */
$route['battle/(:num)'] = 'battle/view/$1';

/* --- API --- */
$route['api/login'] = 'api_auth/login';
$route['api/state'] = 'api_game/state';
