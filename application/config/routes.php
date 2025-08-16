
// S41 AdminContent
$route['admin/content'] = 'admincontent/index';
$route['admin/content/list/(:any)'] = 'admincontent/list/$1';
$route['admin/content/edit/(:any)'] = 'admincontent/edit/$1';
$route['admin/content/edit/(:any)/(:num)'] = 'admincontent/edit/$1/$2';
$route['admin/content/delete/(:any)/(:num)'] = 'admincontent/delete/$1/$2';
$route['admin/content/import'] = 'admincontent/import';

// S42 Language switcher
$route['lang/set/(:any)'] = 'language/set/$1';

// S43 Auth & Email
$route['auth/request_reset'] = 'auth/request_reset';
$route['auth/reset/(:any)'] = 'auth/reset/$1';
$route['auth/reset_submit'] = 'auth/reset_submit';
$route['email/verify/(:any)'] = 'auth/verify/$1';

// S44 Anti-cheat admin
$route['admin/anticheat/events'] = 'anticheatadmin/events';
$route['admin/anticheat/sanctions'] = 'anticheatadmin/sanctions';
$route['admin/anticheat/impose'] = 'anticheatadmin/impose';
$route['admin/anticheat/revoke/(:num)'] = 'anticheatadmin/revoke/$1';

// S45 Observability
$route['admin/observability'] = 'observabilityadmin/dashboard';
$route['admin/observability/metrics_json'] = 'observabilityadmin/metrics_json';

// S46 Notifications
$route['notifications'] = 'notifications/center';
$route['notifications/list.json'] = 'notifications/list_json';
$route['notifications/unread_badge.json'] = 'notifications/unread_badge';
$route['notifications/mark_read/(:num)'] = 'notifications/mark_read/$1';
$route['notifications/mark_all'] = 'notifications/mark_all';

// S49 Privacy/GDPR
$route['privacy'] = 'privacy/index';
$route['terms'] = 'privacy/terms';
$route['privacy/export'] = 'privacy/export';
$route['privacy/delete'] = 'privacy/delete_request';
$route['privacy/delete_confirm'] = 'privacy/delete_confirm';
