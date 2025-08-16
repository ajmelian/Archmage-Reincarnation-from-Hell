<?php defined('BASEPATH') OR exit('No direct script access allowed');

$hook['pre_controller'][] = [
    'class'    => 'AntiCheatHook',
    'function' => 'log_session',
    'filename' => 'AntiCheatHook.php',
    'filepath' => 'hooks',
    'params'   => []
];


$hook['post_controller'][] = [
    'class'    => 'MetricsHook',
    'function' => 'count_request',
    'filename' => 'MetricsHook.php',
    'filepath' => 'hooks',
    'params'   => []
];
