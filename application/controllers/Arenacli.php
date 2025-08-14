<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Arenacli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('ArenaService');
    }

    public function matchmake($loops=1, $sleep=5) {
        $n = max(1, (int)$loops);
        $s = max(1, (int)$sleep);
        for ($i=0;$i<$n;$i++) {
            $m = $this->arenaservice->matchmake();
            echo "Loop ".($i+1)."/$n: matches=".$m.PHP_EOL;
            if ($i<$n-1) sleep($s);
        }
    }
}
