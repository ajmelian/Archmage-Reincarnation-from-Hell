<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('t')) {
    function t($key, $params = []) {
        $CI =& get_instance();
        $CI->load->library('LanguageService');
        return $CI->languageservice->line($key, $params);
    }
}

if (!function_exists('tp')) {
    function tp($key, $count, $params = []) {
        $params['count'] = $count;
        return t($key, $params);
    }
}
