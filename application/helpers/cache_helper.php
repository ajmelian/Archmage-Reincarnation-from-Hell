<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('fragment_cache')) {
    function fragment_cache($key, $ttl, $callback) {
        $CI =& get_instance();
        $CI->load->library('Caching');
        $html = $CI->caching->get('frag:'.$key);
        if ($html) { echo $html; return; }
        ob_start();
        call_user_func($callback);
        $html = ob_get_clean();
        $CI->caching->set('frag:'.$key, $html, $ttl);
        echo $html;
    }
}
