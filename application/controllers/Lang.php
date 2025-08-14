<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Lang extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('LanguageService');
    }

    public function set($code='') {
        if ($code) $this->languageservice->set($code);
        $ref = $this->input->server('HTTP_REFERER') ?: site_url('home');
        redirect($ref);
    }
}
