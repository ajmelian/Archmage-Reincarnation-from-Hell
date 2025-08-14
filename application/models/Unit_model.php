<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Unit_model extends CI_Model {
    protected string $table = 'unit_def';
    public function all(): array { return $this->db->get($this->table)->result_array(); }
}
