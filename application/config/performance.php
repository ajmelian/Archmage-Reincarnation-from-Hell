<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['performance'] = [
    'api_ttl' => [
        'me' => 5,             // microcaché por usuario
        'wallet' => 3,
        'buildings' => 15,
        'research' => 20,
        'arena_leaderboard' => 20,
        'arena_history' => 15,
    ],
];
