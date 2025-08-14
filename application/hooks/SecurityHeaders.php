<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SecurityHeaders {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('security');
    }
    public function apply() {
        $cfg = $this->CI->config->item('security_headers') ?? [];
        if (!$cfg) return;
        // CSP
        if (!headers_sent() && !empty($cfg['csp'])) header("Content-Security-Policy: ".$cfg['csp']);
        // HSTS solo si HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && !headers_sent()) {
            $s = (int)($cfg['hsts_seconds'] ?? 0);
            if ($s>0) header("Strict-Transport-Security: max-age={$s}; includeSubDomains; preload");
        }
        if (!headers_sent()) {
            if (!empty($cfg['x_frame_options'])) header("X-Frame-Options: ".$cfg['x_frame_options']);
            if (!empty($cfg['x_content_type_options'])) header("X-Content-Type-Options: ".$cfg['x_content_type_options']);
            if (!empty($cfg['referrer_policy'])) header("Referrer-Policy: ".$cfg['referrer_policy']);
            if (!empty($cfg['permissions_policy'])) header("Permissions-Policy: ".$cfg['permissions_policy']);
        }
    }
}
