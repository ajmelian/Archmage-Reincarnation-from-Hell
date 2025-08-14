<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Battle extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /** Muestra un replay sencillo del log de batalla guardado en la tabla `battles`. */
    public function view($id) {
        $b = $this->db->get_where('battles', ['id'=>$id])->row_array();
        if (!$b) show_404();
        $this->load->view('battle/view', ['b'=>$b]);
    }
}
