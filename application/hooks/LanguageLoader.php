<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LanguageLoader {
    public function initialize() {
        $CI =& get_instance();
        $CI->load->library('session');
        $CI->load->helper('language');

        // Forzar por querystring ?lang=es|en
        $lang = $CI->input->get('lang', TRUE);
        if ($lang && in_array($lang, ['es','en'], TRUE)) {
            $CI->session->set_userdata('lang', $lang);
        }

        $current = $CI->session->userdata('lang');
        if (!$current) {
            // Detectar por Accept-Language
            $header = $CI->input->server('HTTP_ACCEPT_LANGUAGE') ?: '';
            $current = (stripos($header, 'es') === 0) ? 'es' : 'en';
            $CI->session->set_userdata('lang', $current);
        }

        // Mapear al paquete CI (spanish/english)
        $ciLang = ($current === 'es') ? 'spanish' : 'english';
        $CI->config->set_item('language', $ciLang);

        // Cargar archivo de textos de juego
        $CI->lang->load('game', $current);
    }
}
