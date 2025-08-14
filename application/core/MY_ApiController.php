<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_ApiController extends CI_Controller {
    protected function etagCalc($payload){ return '"'.sha1($payload).'"'; }
    protected function canCache(){ return $this->input->method(TRUE)==='GET'; }
    protected $apiUser = null;
    protected $apiToken = null;

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->config('api');
        $this->load->library(['ApiAuth','Observability']);
        // CORS
        $cors = $this->config->item('api')['cors'] ?? [];
        if (!empty($cors['enabled'])) {
            header('Access-Control-Allow-Origin: '.$cors['allow_origin']);
            header('Access-Control-Allow-Headers: '.$cors['allow_headers']);
            header('Access-Control-Allow-Methods: '.$cors['allow_methods']);
        }
        if ($this->input->method(TRUE)==='OPTIONS') { exit; }

        $this->obsName = 'http_api_request';
        $this->obsLabels = ['endpoint'=>$this->uri->uri_string()];
        $this->observability->beginRequest($this->obsName, $this->obsLabels);

        // Auth (except for auth/token and docs)
        $path = $this->uri->uri_string();
        if (!preg_match('#^api/auth/token$#', $path) && !preg_match('#^api/docs$#', $path)) {
            $ctx = $this->apiauth->fromHeader();
            if (!$ctx) $this->json(['ok'=>false,'error'=>'Unauthorized'], 401);
            $this->apiUser = $ctx['user'];
            $this->apiToken = $ctx['token'];
            $this->rateLimit($this->apiToken['id']);
        }
    }

    protected function rateLimit(int $tokenId): void {
        // Use rate_counters table (action='api')
        $cfg = $this->config->item('api')['rate_limit'] ?? ['window_sec'=>60,'max'=>120];
        $win = (int)$cfg['window_sec']; $max = (int)$cfg['max']; $now = time();
        $ws = (int)floor($now / $win) * $win;
        $key = ['realm_id'=>$tokenId,'action'=>'api','window_start'=>$ws]; // reuse columns
        $row = $this->db->get_where('rate_counters',$key)->row_array();
        if (!$row) {
            $this->db->insert('rate_counters', $key + ['count'=>1,'updated_at'=>$now]);
            return;
        }
        if ((int)$row['count'] >= $max) $this->json(['ok'=>false,'error'=>'Rate limited'], 429);
        $this->db->set('count','count+1',FALSE)->set('updated_at',$now)->where($key)->update('rate_counters');
    }

    protected function json($1) {
        $this->observability->endRequest($status);
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
        exit;
    }
}
