<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    protected string $langCode = 'es';
    protected bool $requireLogin = TRUE;

    protected string $langCode = 'es';

    public function __construct() {
        parent::__construct();
        // Auth gate
        if ($this->requireLogin && !$this->session->userdata('userId')) {
            redirect('auth/login');
        }        

        parent::__construct();
        $this->langCode = $this->session->userdata('lang') ?: 'es';
    }

    protected function render(string $view, array $data = []) {
        $this->load->view('partials/header', $data);
        $this->load->view($view, $data);
        $this->load->view('partials/footer', $data);
    }
}
