<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Chatcli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
        $this->load->config('chat');
    }

    public function cleanup() {
        $days = (int)($this->config->item('chat')['retention_days'] ?? 14);
        $limit = time() - $days*86400;
        $this->db->where('created_at <', $limit)->delete('chat_messages');
        $n1 = $this->db->affected_rows();
        $this->db->where('created_at <', $limit)->delete('dm_messages');
        $n2 = $this->db->affected_rows();
        echo "Deleted chat_messages=$n1, dm_messages=$n2\n";
    }
}
