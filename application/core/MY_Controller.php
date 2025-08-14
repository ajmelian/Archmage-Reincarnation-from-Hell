<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    protected $obsName = 'http_html_request';
    protected $obsLabels = [];

    public function __construct() {
        parent::__construct();
        $this->load->library(['Observability','LanguageService','Format']);
        $this->obsLabels = ['endpoint'=>$this->uri->uri_string()];
        $this->observability->beginRequest($this->obsName, $this->obsLabels);
        $this->langService = $this->languageservice; $this->fmt = $this->format;
        $this->load->helper('i18n');
    }

    public function __destruct() {
        if (function_exists('http_response_code')) {
            $status = http_response_code();
        } else {
            $status = 200;
        }
        $this->observability->endRequest((int)$status);
    }
}
