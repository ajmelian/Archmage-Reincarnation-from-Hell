<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    protected string $table = 'users';

    public function create(array $data): int {
        $this->db->insert($this->table, $data);
        return (int)$this->db->insert_id();
    }

    public function findByEmail(string $email): ?array {
        $row = $this->db->get_where($this->table, ['email'=>$email])->row_array();
        return $row ?: null;
    }

    public function findById(int $id): ?array {
        $row = $this->db->get_where($this->table, ['id'=>$id])->row_array();
        return $row ?: null;
    }
}
