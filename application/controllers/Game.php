<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Game extends MY_Controller {
    public function index() {
        // TODO: cargar estado del reino del usuario autenticado.
        $data = [
            'currentTick' => 1
        ];
        $this->render('game/index', $data);
    }
}
