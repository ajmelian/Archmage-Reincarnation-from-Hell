<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickcli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('TickRunner');
        $this->load->config('tick');
    }

    public function run($times=1) {
        $n = max(1, (int)$times);
        for ($i=0;$i<$n;$i++) {
            $res = $this->tickrunner->runOne();
            echo "Tick ".($i+1)."/$n: ".json_encode($res).PHP_EOL;
            if ($n>1) sleep((int)($this->config->item('tick')['period_sec'] ?? 1));
        }
    }
}
