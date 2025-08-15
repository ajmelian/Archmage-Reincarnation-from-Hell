<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ContentService {
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); }

    public function list($table, $limit=50, $offset=0) {
        return $this->CI->db->order_by('id','DESC')->get($table, $limit, $offset)->result_array();
    }
    public function get($table, $id) {
        return $this->CI->db->get_where($table, ['id'=>(int)$id])->row_array();
    }
    public function create($table, $data) {
        $data['created_at'] = time();
        $this->CI->db->insert($table, $data);
        return (int)$this->CI->db->insert_id();
    }
    public function update($table, $id, $data) {
        $this->CI->db->update($table, $data, ['id'=>(int)$id]);
        return $this->CI->db->affected_rows()>=0;
    }
    public function delete($table, $id) {
        $this->CI->db->delete($table, ['id'=>(int)$id]);
        return $this->CI->db->affected_rows()>0;
    }
}
