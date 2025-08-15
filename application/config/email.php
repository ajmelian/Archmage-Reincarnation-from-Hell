<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['email'] = [
    'protocol'  => 'smtp',
    'smtp_host' => 'smtp.example.com',
    'smtp_user' => 'user@example.com',
    'smtp_pass' => 'CHANGE_ME',
    'smtp_port' => 587,
    'smtp_crypto'=> 'tls',
    'mailtype'  => 'html',
    'newline'   => "\r\n",
    'crlf'      => "\r\n",
    'from_email'=> 'noreply@example.com',
    'from_name' => 'Archmage',
    'base_url'  => 'http://localhost', // para enlaces en correos
];
