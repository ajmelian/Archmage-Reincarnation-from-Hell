<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Alliances extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model(['Realm_model']);
    }

    public function index() {
        $userId = (int)$this->session->userdata('userId');
        $realm = $this->db->get_where('realms', ['user_id'=>$userId])->row_array();
        $memberships = $this->db->query('SELECT am.*, a.name, a.tag FROM alliance_members am JOIN alliances a ON a.id=am.alliance_id WHERE am.realm_id=?', [$realm['id']])->result_array();
        $alliances = $this->db->order_by('id','DESC')->limit(50)->get('alliances')->result_array();
        $this->load->view('alliances/index', ['realm'=>$realm,'memberships'=>$memberships,'alliances'=>$alliances]);
    }

    public function create() {
        if ($this->input->method(TRUE) === 'POST') {
            $name = trim($this->input->post('name', TRUE));
            $tag  = trim($this->input->post('tag', TRUE));
            if ($name && $tag) {
                $this->db->insert('alliances', ['name'=>$name,'tag'=>$tag,'created_at'=>time()]);
                $aid = (int)$this->db->insert_id();
                $userId = (int)$this->session->userdata('userId');
                $realm = $this->db->get_where('realms', ['user_id'=>$userId])->row_array();
                $this->db->insert('alliance_members', ['alliance_id'=>$aid,'realm_id'=>$realm['id'],'role'=>'leader','joined_at'=>time()]);
            }
            redirect('alliances');
        }
        show_404();
    }

    public function join($allianceId) {
        $userId = (int)$this->session->userdata('userId');
        $realm = $this->db->get_where('realms', ['user_id'=>$userId])->row_array();
        $exists = $this->db->get_where('alliance_members', ['alliance_id'=>$allianceId,'realm_id'=>$realm['id']])->row_array();
        if (!$exists) {
            $this->db->insert('alliance_members', ['alliance_id'=>$allianceId,'realm_id'=>$realm['id'],'role'=>'member','joined_at'=>time()]);
        }
        redirect('alliances');
    }

    public function leave($allianceId) {
        $userId = (int)$this->session->userdata('userId');
        $realm = $this->db->get_where('realms', ['user_id'=>$userId])->row_array();
        $this->db->where(['alliance_id'=>$allianceId,'realm_id'=>$realm['id']])->delete('alliance_members');
        redirect('alliances');
    }
}
