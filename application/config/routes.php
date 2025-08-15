
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
