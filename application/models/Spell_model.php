<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Spell_model extends CI_Model {
    protected string $table = 'spell_def';

    public function all(): array { return $this->db->get($this->table)->result_array(); }

    public function mapById(): array {
        $out = []; foreach ($this->all() as $s) { $out[$s['id']] = $s; } return $out;
    }
}
