<?php defined('BASEPATH') OR exit('No direct script access allowed');
$hook['pre_controller'][] = array(
    'class'    => 'LanguageLoader',
    'function' => 'initialize',
    'filename' => 'LanguageLoader.php',
    'filepath' => 'hooks'
);


$hook['pre_controller'][] = array(
    'class'    => 'SessionPruner',
    'function' => 'run',
    'filename' => 'SessionPruner.php',
    'filepath' => 'hooks'
);
