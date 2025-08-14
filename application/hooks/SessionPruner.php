<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SessionPruner {
    public function run() {
        $CI =& get_instance();
        $CI->load->database();
        $lifetime = (int)$CI->config->item('sess_expiration') ?: 300;
        $threshold = time() - $lifetime;
        // Elimina en pequeñas tandas para no impactar latencia
        $CI->db->limit(200)->where('timestamp <', $threshold)->delete('ci_sessions');
    }
}
