<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Language extends MY_Controller {
    public function set($lang='english') {
        $allowed = ['english','spanish'];
        if (!in_array($lang, $allowed, true)) $lang = 'english';
        $this->session->set_userdata('site_lang', $lang);
        $back = $this->input->get('back') ?: '/';
        redirect($back);
    }
}
