<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Loot {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /** Rueda una tabla de drop y devuelve items [ ['item_id'=>..., 'qty'=>n], ... ] */
    public function roll(string $tableId, int $rolls=1, int $seed=0): array {
        if ($seed) mt_srand($seed);
        $entries = $this->CI->db->get_where('drop_table_entry', ['table_id'=>$tableId])->result_array();
        if (!$entries) return [];
        $weights = []; $total = 0;
        foreach ($entries as $e) { $w = max(1, (int)$e['weight']); $weights[]=$w; $total+=$w; }
        $out = [];
        for ($i=0;$i<$rolls;$i++) {
            $r = mt_rand(1, $total); $acc=0; $pick=null;
            foreach ($entries as $idx=>$e) { $acc += $weights[$idx]; if ($r <= $acc) { $pick=$e; break; } }
            if ($pick) {
                $qty = mt_rand(max(1,(int)$pick['min_qty']), max((int)$pick['min_qty'], (int)$pick['max_qty']));
                $out[] = ['item_id'=>$pick['item_id'],'qty'=>$qty];
            }
        }
        return $out;
    }
}
