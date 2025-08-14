<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Messages extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function inbox() {
        $userId = (int)$this->session->userdata('userId');
        $realm = $this->db->get_where('realms', ['user_id'=>$userId])->row_array();
        $msgs = $this->db->order_by('id','DESC')->get_where('messages', ['receiver_realm_id'=>$realm['id']])->result_array();
        $this->load->view('messages/inbox', ['realm'=>$realm,'msgs'=>$msgs]);
    }

    public function send() {
        if ($this->input->method(TRUE) === 'POST') {
            $userId = (int)$this->session->userdata('userId');
            $realm = $this->db->get_where('realms', ['user_id'=>$userId])->row_array();
            $to = (int)$this->input->post('to', TRUE);
            $subject = trim($this->input->post('subject', TRUE));
            $body = trim($this->input->post('body', TRUE));
            if ($to && $subject) {
                $this->db->insert('messages', [
                    'sender_realm_id'=>$realm['id'],
                    'receiver_realm_id'=>$to,
                    'subject'=>$subject,
                    'body'=>$body,
                    'is_read'=>0,
                    'created_at'=>time()
                ]);
            }
            redirect('messages/inbox');
        }
        show_404();
    }
}
