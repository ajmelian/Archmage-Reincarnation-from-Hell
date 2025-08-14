<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Hero_model extends CI_Model {
    protected string $table = 'hero_def';
    public function all(): array { return $this->db->get($this->table)->result_array(); }
    public function mapById(): array { $o=[]; foreach($this->all() as $r){$o[$r['id']]=$r;} return $o; }
}
