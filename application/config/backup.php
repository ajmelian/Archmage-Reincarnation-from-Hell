<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['backup'] = [
    // Carpeta donde se guardan los dumps (asegúrate de que sea escribible)
    'path' => FCPATH.'backups',
    // Formato: 'gzip' o 'zip' (usa DB Utility de CI3)
    'format' => 'gzip',
    // Retención
    'keep_last' => 10,          // como mínimo, guarda los últimos N
    'max_total_mb' => 2048,     // si el total supera, elimina los más antiguos
    'filename_prefix' => 'archmage_db_',
];
