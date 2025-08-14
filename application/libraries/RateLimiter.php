<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RateLimiter {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('Caching');
    }
    private function key($scope) {
        $ip = $this->CI->input->ip_address();
        return "rl:{$scope}:{$ip}";
    }
    public function check($scope, $max, $windowSec) {
        $k = $this->key($scope);
        $entry = $this->CI->caching->get($k);
        $now = time();
        if (!$entry || $entry['reset'] <= $now) {
            $entry = ['count'=>1, 'reset'=>$now + $windowSec];
            $this->CI->caching->set($k, $entry, $windowSec);
            return [true, $entry['reset']];
        }
        if ($entry['count'] >= $max) return [false, $entry['reset']];
        $entry['count'] += 1;
        $this->CI->caching->set($k, $entry, $entry['reset'] - $now);
        return [true, $entry['reset']];
    }
}
