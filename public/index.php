<?php
declare(strict_types=1);

define('ENVIRONMENT', getenv('CI_ENV') ?: 'development');

$system_path = 'system';
$application_folder = 'application';

if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if (($_temp = realpath($system_path)) !== FALSE) {
    $system_path = $_temp.DIRECTORY_SEPARATOR;
} else {
    $system_path = strtr(
        rtrim($system_path, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
    ).DIRECTORY_SEPARATOR;
}

define('BASEPATH', str_replace("\\", "/", $system_path));
define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);
define('VIEWPATH', APPPATH.'views'.DIRECTORY_SEPARATOR);

require_once BASEPATH.'core/CodeIgniter.php';
