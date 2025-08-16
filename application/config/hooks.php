<?php defined('BASEPATH') OR exit('No direct script access allowed');

$hook['pre_controller'][] = [
    'class'    => 'AntiCheatHook',
    'function' => 'log_session',
    'filename' => 'AntiCheatHook.php',
    'filepath' => 'hooks',
    'params'   => []
];
