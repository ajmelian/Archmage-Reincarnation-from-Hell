<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Research_model extends CI_Model {
    protected string $table = 'research_def';
    public function all(): array { return $this->db->get($this->table)->result_array(); }
    public function mapById(): array {
        $out = []; foreach ($this->all() as $r) { $out[$r['id']] = $r; } return $out;
    }
}
