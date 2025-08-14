<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sim extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('Engine');
    }

    public function duel($atk=100, $def=100, $runs=1000) {
        $sideA = [['id'=>'A','attack'=>$atk,'defense'=>$def,'hp'=>1,'qty'=>100]];
        $sideB = [['id'=>'B','attack'=>$def,'defense'=>$atk,'hp'=>1,'qty'=>100]];
        $winA=0; $winB=0;
        for ($i=0;$i<(int)$runs;$i++) {
            $r = $this->engine->resolveCombat($sideA, $sideB, 1000+$i);
            if (($r['winner'] ?? '') === 'A') $winA++; else $winB++;
        }
        echo "A wins: $winA B wins: $winB\n";
    }
}
