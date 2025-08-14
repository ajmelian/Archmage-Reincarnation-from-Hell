<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tick extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
    }

    public function set_interval($seconds = 300) {
        $seconds = max(60, (int)$seconds);
        $this->db->replace('settings', ['key'=>'tick_interval_seconds','value'=>strval($seconds),'updated_at'=>time()]);
        $next = time() + $seconds;
        $this->db->replace('settings', ['key'=>'next_tick_at','value'=>strval($next),'updated_at'=>time()]);
        echo "Tick interval set to $seconds s. Next tick at $next\n";
    }

    public function run_if_due() {
        $interval = (int)($this->get('tick_interval_seconds') ?: 300);
        $next = (int)($this->get('next_tick_at') ?: 0);
        if (time() >= $next) {
            echo "Due. Running turn...\n";
            // forward to Turn::run
            $_SERVER['argv'] = ['index.php','turn','run'];
            require(APPPATH.'controllers/Turn.php');
            $turn = new Turn();
            $turn->run();
            // schedule next
            $this->db->replace('settings', ['key'=>'next_tick_at','value'=>strval(time()+$interval),'updated_at'=>time()]);
        } else {
            echo "Not due. Next at $next\n";
        }
    }

    public function daemon() {
        $interval = (int)($this->get('tick_interval_seconds') ?: 300);
        while (true) {
            $this->run_if_due();
            sleep(10);
        }
    }

    private function get($key) {
        $row = $this->db->get_where('settings', ['key'=>$key])->row_array();
        return $row ? $row['value'] : null;
    }
}
