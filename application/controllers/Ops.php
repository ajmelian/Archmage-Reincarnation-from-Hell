<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ops extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->config('observability');
    }

    public function metrics() {
        $limitTs = time() - 3600; // última hora
        $topReq = $this->db->select('labels, SUM(count) as c')->from('metrics_counter')
            ->where('name','http_api_request_total')->where('window_start >=', $limitTs)
            ->group_by('labels')->order_by('c','DESC')->limit(10)->get()->result_array();
        $topHtml = $this->db->select('labels, SUM(count) as c')->from('metrics_counter')
            ->where('name','http_html_request_total')->where('window_start >=', $limitTs)
            ->group_by('labels')->order_by('c','DESC')->limit(10)->get()->result_array();
        $this->load->view('ops/metrics', ['topReq'=>$topReq,'topHtml'=>$topHtml]);
    }
}
