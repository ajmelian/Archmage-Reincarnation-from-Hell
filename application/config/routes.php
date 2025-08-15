
// S41 AdminContent
$route['admin/content'] = 'admincontent/index';
$route['admin/content/list/(:any)'] = 'admincontent/list/$1';
$route['admin/content/edit/(:any)'] = 'admincontent/edit/$1';
$route['admin/content/edit/(:any)/(:num)'] = 'admincontent/edit/$1/$2';
$route['admin/content/delete/(:any)/(:num)'] = 'admincontent/delete/$1/$2';
$route['admin/content/import'] = 'admincontent/import';
