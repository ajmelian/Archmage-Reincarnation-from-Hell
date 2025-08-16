<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends MY_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->library(['NotificationService','session']);
        $this->load->helper(['url']);
        // TODO: auth
    }

    private function uid() { return (int)$this->session->userdata('user_id'); }

    public function center() {
        $uid = $this->uid(); if (!$uid) show_error('Login requerido', 401);
        $rows = $this->notificationservice->list($uid, false, 100, 0);
        $this->load->view('notifications/center', ['rows'=>$rows]);
    }

    public function list_json() {
        $uid = $this->uid(); if (!$uid) { $this->output->set_status_header(401)->set_output('{}'); return; }
        $rows = $this->notificationservice->list($uid, false, 50, 0);
        $this->output->set_content_type('application/json')->set_output(json_encode(['notifications'=>$rows]));
    }

    public function unread_badge() {
        $uid = $this->uid(); if (!$uid) { $this->output->set_content_type('application/json')->set_output('{"count":0}'); return; }
        $n = $this->notificationservice->unreadCount($uid);
        $this->output->set_content_type('application/json')->set_output(json_encode(['count'=>$n]));
    }

    public function mark_read($id) {
        $uid = $this->uid(); if (!$uid) show_error('Login requerido', 401);
        $ok = $this->notificationservice->markRead((int)$id, $uid);
        redirect('notifications/center');
    }

    public function mark_all() {
        $uid = $this->uid(); if (!$uid) show_error('Login requerido', 401);
        $this->notificationservice->markAllRead($uid);
        redirect('notifications/center');
    }
}
