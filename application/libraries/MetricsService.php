<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MetricsService {
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); }

    private function day($ts=null){ return (int)date('Ymd', $ts ?: time()); }

    public function inc($key, $amount=1, $ts=null) {
        $d = $this->day($ts);
        // UPSERT
        $sql = "INSERT INTO metrics_counters (metric_key, day, value, updated_at) VALUES (?,?,?,?)
                ON DUPLICATE KEY UPDATE value=value+VALUES(value), updated_at=VALUES(updated_at)";
        $this->CI->db->query($sql, [$key, $d, (int)$amount, time()]);
    }

    public function get($key, $sinceDays=7) {
        $since = (int)date('Ymd', time()-$sinceDays*86400);
        return $this->CI->db->order_by('day','ASC')->get_where('metrics_counters', ['metric_key'=>$key])->result_array();
    }

    public function topToday($limit=10) {
        $d = $this->day();
        return $this->CI->db->order_by('value','DESC')->limit($limit)->get_where('metrics_counters',['day'=>$d])->result_array();
    }
}
