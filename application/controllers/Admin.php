<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form']);
        $this->load->library('AdminService');
        $this->load->config('admin');
        // Simple auth wall
        $this->admin = $this->adminservice->requireAdmin();
    }

    public function index() {
        $open = $this->adminservice->listReports('open', 10);
        $mutes = $this->adminservice->listMutes(10);
        $logs = $this->adminservice->fetchLogs('gm_actions', 10);
        $this->load->view('admin/dashboard', ['admin'=>$this->admin,'open'=>$open,'mutes'=>$mutes,'logs'=>$logs]);
    }

    public function reports($status='open') {
        $rows = $this->adminservice->listReports($status, 100);
        $this->load->view('admin/reports', ['admin'=>$this->admin,'status'=>$status,'rows'=>$rows]);
    }
    public function resolve_report() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $id = (int)$this->input->post('id', TRUE);
        $status = (string)$this->input->post('status', TRUE) ?: 'resolved';
        $resolution = (string)$this->input->post('resolution', TRUE) ?: '';
        $this->adminservice->resolveReport((int)$this->admin['id'], $id, $resolution, $status);
        redirect('admin/reports/'.$status);
    }

    public function mutes() {
        $rows = $this->adminservice->listMutes(200);
        $this->load->view('admin/mutes', ['admin'=>$this->admin,'rows'=>$rows]);
    }
    public function mute_post() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $realm = (int)$this->input->post('realm_id', TRUE);
        $scope = (string)$this->input->post('scope', TRUE);
        $minutes = (int)$this->input->post('minutes', TRUE);
        $reason = (string)$this->input->post('reason', TRUE);
        $this->adminservice->addMute((int)$this->admin['id'], $realm, $scope, $minutes, $reason);
        redirect('admin/mutes');
    }
    public function unmute($id) {
        $this->adminservice->delMute((int)$this->admin['id'], (int)$id);
        redirect('admin/mutes');
    }

    public function economy() {
        $this->load->view('admin/economy', ['admin'=>$this->admin]);
    }
    public function economy_post() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $realm = (int)$this->input->post('realm_id', TRUE);
        $resource = (string)$this->input->post('resource', TRUE);
        $delta = (int)$this->input->post('delta', TRUE);
        $reason= (string)$this->input->post('reason', TRUE);
        $this->adminservice->adjustWallet((int)$this->admin['id'], $realm, $resource, $delta, $reason);
        redirect('admin/economy');
    }

    public function logs($table='gm_actions') {
        $limit = (int)($this->config->item('admin')['logs_limits'][$table] ?? 200);
        $rows = $this->adminservice->fetchLogs($table, $limit);
        $this->load->view('admin/logs', ['admin'=>$this->admin,'table'=>$table,'rows'=>$rows]);
    }

    public function users() {
        $q = (string)$this->input->get('q', TRUE) ?: '';
        $rows = $q ? $this->adminservice->searchUsers($q, 50) : [];
        $this->load->view('admin/users', ['admin'=>$this->admin,'q'=>$q,'rows'=>$rows]);
    }
    public function user_admin($userId, $op='grant') {
        $this->adminservice->setAdmin((int)$this->admin['id'], (int)$userId, $op==='grant');
        redirect('admin/users');
    }
}


    public function economy_balance() {
        $this->load->library('EconomyService');
        if ($this->input->method(TRUE)==='POST') {
            $k = (string)$this->input->post('key', TRUE);
            $v = (string)$this->input->post('value', TRUE);
            if ($k!=='') $this->economyservice->setParam($k, is_numeric($v)?0+$v:$v);
            redirect('admin/economy_balance');
        }
        $rows = $this->db->order_by('key','ASC')->get('econ_params')->result_array();
        $mods = $this->db->order_by('created_at','DESC')->limit(100)->get('econ_modifiers')->result_array();
        $this->load->view('admin/economy_balance', ['params'=>$rows,'mods'=>$mods,'admin'=>$this->admin]);
    }
