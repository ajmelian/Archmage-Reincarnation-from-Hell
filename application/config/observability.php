<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['observability'] = [
    'counter_window_sec' => 60,   // ventana de agregación para contadores/summaries
    'retention_days' => 7,        // retención en BD (compactación)
    'prom_namespace' => 'archmage',
];
