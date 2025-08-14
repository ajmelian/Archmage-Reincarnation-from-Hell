<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['moderation'] = [
    'default_mute_minutes' => 60,
    'default_market_suspension_minutes' => 120,
    'max_mute_minutes' => 24*60,
    'max_market_suspension_minutes' => 7*24*60,
    'allow_user_reports' => true,
];
