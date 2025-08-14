<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['moderation'] = [
    'rate_limits' => [
        'chat_post' => ['window_sec'=>10, 'max'=>5],
        'dm_send'   => ['window_sec'=>60, 'max'=>10],
    ],
    'reject_on_badword' => true, // si false, reemplaza con ****
    // lista adicional (además de DB) — útil para hotfix sin migración
    'badwords' => ['troll','scam'],
];
