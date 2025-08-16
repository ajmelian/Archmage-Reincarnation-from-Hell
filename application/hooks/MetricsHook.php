<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MetricsHook {
    public function count_request() {
        $CI =& get_instance();
        $CI->load->library('MetricsService');
        $class = $CI->router->class;
        $method = $CI->router->method;
        $key = 'http.'.$class.'.'.$method;
        $CI->metricsservice->inc($key, 1);
    }
}
