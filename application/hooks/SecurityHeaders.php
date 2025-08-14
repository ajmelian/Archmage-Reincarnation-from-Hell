<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SecurityHeaders {
    public function apply() {
        $CI =& get_instance();
        $CI->load->config('security');
        $h = $CI->config->item('security_ext')['headers'] ?? [];
        if (!headers_sent()) {
            if (!empty($h['hsts'])) header('Strict-Transport-Security: '.$h['hsts']);
            if (!empty($h['csp'])) header('Content-Security-Policy: '.$h['csp']);
            if (!empty($h['frame_options'])) header('X-Frame-Options: '.$h['frame_options']);
            if (!empty($h['x_content_type'])) header('X-Content-Type-Options: '.$h['x_content_type']);
            if (!empty($h['referrer'])) header('Referrer-Policy: '.$h['referrer']);
        }
    }
}
