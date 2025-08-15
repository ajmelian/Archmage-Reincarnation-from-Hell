<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('langx')) {
    function langx($line, $default='') {
        $CI =& get_instance();
        $str = $CI->lang->line($line);
        if ($str === FALSE || $str === '') return $default!=='' ? $default : $line;
        return $str;
    }
}
