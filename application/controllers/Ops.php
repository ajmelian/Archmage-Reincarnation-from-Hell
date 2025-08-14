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


    public function cache() {
        $this->load->config('cache_ext');
        $this->load->config('performance');
        $this->load->view('ops/cache', [
            'cache'=>$this->config->item('cache_ext'),
            'perf'=>$this->config->item('performance'),
        ]);
    }


    public function backups() {
        $this->load->library('AdminService');
        $admin = $this->adminservice->requireAdmin();
        $this->load->config('backup');
        $dir = rtrim($this->config->item('backup')['dir'] ?? (APPPATH.'../backups'), '/');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $files = glob($dir.'/*.sql*'); sort($files);
        $list = array_map(function($f){ return ['name'=>basename($f),'mtime'=>filemtime($f),'size'=>filesize($f)]; }, $files);
        $jobs = $this->db->order_by('created_at','DESC')->limit(50)->get('backup_jobs')->result_array();
        $this->load->view('ops/backups', ['files'=>$list,'jobs'=>$jobs,'dir'=>$dir]);
    }
