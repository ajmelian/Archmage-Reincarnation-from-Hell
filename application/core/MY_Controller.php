<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(['url','language']);
        $default = $this->config->item('language') ?: 'english';
        $lang = $this->session->userdata('site_lang') ?: $default;
        $this->config->set_item('language', $lang);
        // Carga paquetes base
        $this->lang->load('common', $lang);
        $this->lang->load('ui', $lang);
    }
}
