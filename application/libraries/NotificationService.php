<?php defined('BASEPATH') OR exit('No direct script access allowed');

class NotificationService {
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); }

    public function send($userId, $type, $title, $body=null, $url=null) {
        $row = [
            'user_id'=>(int)$userId, 'type'=>substr((string)$type,0,32),
            'title'=>substr((string)$title,0,190), 'body'=>$body, 'url'=>$url,
            'created_at'=>time()
        ];
        $this->CI->db->insert('notifications',$row);
        return (int)$this->CI->db->insert_id();
    }

    public function list($userId, $unreadOnly=false, $limit=50, $offset=0) {
        if ($unreadOnly) $this->CI->db->where('read_at', NULL);
        return $this->CI->db->order_by('id','DESC')->get_where('notifications',['user_id'=>(int)$userId], $limit, $offset)->result_array();
    }

    public function unreadCount($userId) {
        return (int)$this->CI->db->where('user_id',(int)$userId)->where('read_at', NULL)->count_all_results('notifications');
    }

    public function markRead($id, $userId) {
        $this->CI->db->update('notifications',['read_at'=>time()],['id'=>(int)$id,'user_id'=>(int)$userId]);
        return $this->CI->db->affected_rows()>0;
    }

    public function markAllRead($userId) {
        $this->CI->db->update('notifications',['read_at'=>time()],['user_id'=>(int)$userId,'read_at'=>NULL]);
        return $this->CI->db->affected_rows()>=0;
    }
}
