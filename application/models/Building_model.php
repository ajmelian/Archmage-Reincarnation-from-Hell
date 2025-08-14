<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Building_model extends CI_Model {
    protected string $table = 'building_def';
    public function all(): array { return $this->db->get($this->table)->result_array(); }
    public function mapById(): array {
        $out = []; foreach ($this->all() as $b) { $out[$b['id']] = $b; } return $out;
    }
}
