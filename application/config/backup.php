<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['backup'] = [
    // Ruta al directorio de backups; por defecto en la raíz del proyecto
    'dir' => APPPATH.'../backups',
    // Cantidad máxima de archivos a conservar (rotación)
    'keep' => 10,
    // Comprimir .sql en .gz
    'gzip' => true,
    // Tablas candidatas a exportar como seeds CSV (puedes añadir las tuyas)
    'seed_tables' => ['research_levels','buildings','realms','users'],
];
