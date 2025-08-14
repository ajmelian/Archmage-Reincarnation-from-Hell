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

$route['heroes'] = 'heroes/index';
$route['heroes/allocate'] = 'heroes/allocate';

$route['market'] = 'market/index';
$route['market/list'] = 'market/list_item';
$route['market/buy/(:num)'] = 'market/buy/$1';
$route['market/cancel/(:num)'] = 'market/cancel/$1';
$route['trade'] = 'trade/index';
$route['trade/offer'] = 'trade/offer';
$route['trade/accept/(:num)'] = 'trade/accept/$1';
$route['trade/decline/(:num)'] = 'trade/decline/$1';
$route['trade/cancel/(:num)'] = 'trade/cancel/$1';

$route['alliances'] = 'alliances/index';
$route['alliances/create'] = 'alliances/create';
$route['alliances/view/(:num)'] = 'alliances/view/$1';
$route['alliances/invite'] = 'alliances/invite';
$route['alliances/accept_invite/(:num)'] = 'alliances/accept_invite/$1';
$route['alliances/leave/(:num)'] = 'alliances/leave/$1';
$route['alliances/promote'] = 'alliances/promote';
$route['alliances/bank/deposit'] = 'alliances/bank_deposit';
$route['alliances/bank/withdraw'] = 'alliances/bank_withdraw';
$route['alliances/declare'] = 'alliances/declare/' . $this->input->post('aid2');
$route['alliances/declare/(:num)'] = 'alliances/declare/$1';
$route['alliances/nap/(:num)'] = 'alliances/nap/$1';
$route['alliances/ally/(:num)'] = 'alliances/ally/$1';
$route['alliances/neutral/(:num)'] = 'alliances/neutral/$1';

$route['inventory'] = 'inventoryui/index';

$route['buildings'] = 'buildings/index';
$route['buildings/queue'] = 'buildings/queue';
$route['buildings/cancel/(:num)'] = 'buildings/cancel/$1';
$route['buildings/demolish'] = 'buildings/demolish';

$route['research'] = 'research/index';
$route['research/queue'] = 'research/queue';
$route['research/cancel/(:num)'] = 'research/cancel/$1';

$route['arena'] = 'arena/index';
$route['arena/queue'] = 'arena/queue';
$route['arena/cancel'] = 'arena/cancel';

$route['chat'] = 'chat/index';
$route['chat/global'] = 'chat/index/global';
$route['chat/alliance'] = 'chat/index/alliance';
$route['chat/poll'] = 'chat/poll';
$route['chat/post'] = 'chat/post';
$route['messages'] = 'messages/index';
$route['messages/compose'] = 'messages/compose';
$route['messages/send'] = 'messages/send';
$route['messages/read/(:num)'] = 'messages/read/$1';
$route['messages/delete/(:num)'] = 'messages/delete/$1';

$route['mod/block/(:num)'] = 'mod/block/$1';
$route['mod/unblock/(:num)'] = 'mod/unblock/$1';
$route['mod/report_chat/(:num)'] = 'mod/report_chat/$1';
$route['mod/report_dm/(:num)'] = 'mod/report_dm/$1';

$route['admin'] = 'admin/index';
$route['admin/reports'] = 'admin/reports/open';
$route['admin/reports/(.*)'] = 'admin/reports/$1';
$route['admin/resolve_report'] = 'admin/resolve_report';
$route['admin/mutes'] = 'admin/mutes';
$route['admin/mute_post'] = 'admin/mute_post';
$route['admin/unmute/(:num)'] = 'admin/unmute/$1';
$route['admin/economy'] = 'admin/economy';
$route['admin/economy_post'] = 'admin/economy_post';
$route['admin/logs'] = 'admin/logs/gm_actions';
$route['admin/logs/(.*)'] = 'admin/logs/$1';
$route['admin/users'] = 'admin/users';
$route['admin/user_admin/(:num)/(grant|revoke)'] = 'admin/user_admin/$1/$2';
