<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['cache'] = [
    'driver' => 'file', // file|apcu|redis
    'redis' => ['host'=>'127.0.0.1','port'=>6379,'timeout'=>1.0,'prefix'=>'archmage:'],
    'namespace' => 'archmage',
    'path' => APPPATH.'cache/archmage',
    'default_ttl' => 60,
    'ttls' => [
        'market:index'   => 60,
        'auctions:index' => 60,
        'alliances:index'=> 120,
        'api:export'     => 30,
    ],
];
