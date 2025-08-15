<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Engine: utilidades de combate (stack ordering + pairing v1)
 * NOTA: Estas funciones no calculan el daño; solo determinan emparejamientos.
 */
class Engine {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('game');
    }

    private function cfg() { return $this->CI->config->item('game')['combat'] ?? []; }

    /**
     * Ordena stacks aplicando multiplicadores para "stack order power".
     * $stacks: [ ['id'=>..., 'type'=>'ranged|melee|flying', 'power'=>int], ... ]
     */
    public function stack_order(array $stacks): array {
        $cfg = $this->cfg();
        $mult = $cfg['stack_order_multipliers'] ?? ['ranged'=>1.0,'melee'=>1.5,'flying'=>2.25];
        foreach ($stacks as &$s) {
            $m = $mult[$s['type']] ?? 1.0;
            $s['_order_power'] = $s['power'] * $m;
        }
        unset($s);
        usort($stacks, function($a,$b){
            if ($a['_order_power'] == $b['_order_power']) return 0;
            return ($a['_order_power'] > $b['_order_power']) ? -1 : 1;
        });
        // limitar a max_stacks
        $max = (int)($cfg['max_stacks'] ?? 10);
        return array_slice($stacks, 0, $max);
    }

    /**
     * Pairing básico con "over-spill" y umbral mínimo de tamaño relativo.
     * $atk/$def: stacks ya ordenados por stack_order().
     * Cada stack tiene: 'type','power'.
     * Retorna: lista de emparejamientos [ ['atk_idx'=>i,'def_idx'=>j,'share'=>float 0..1], ... ]
     */
    public function pairing(array $atk, array $def): array {
        $cfg = $this->cfg();
        $minRatio = (float)($cfg['pair_min_ratio'] ?? 0.10);
        $pairs = [];
        $defAvail = [];
        foreach ($def as $j=>$s) $defAvail[$j] = $s['power'];

        foreach ($atk as $i=>$a) {
            $remaining = $a['power'];
            // Primer pase: objetivo primario compatible
            foreach ($def as $j=>$d) {
                if ($defAvail[$j] <= 0) continue;
                if (!$this->can_hit($a['type'], $d['type'])) continue;
                // umbral - no atacar objetivos ridículamente pequeños
                if ($defAvail[$j] < $minRatio * $remaining) continue;
                $take = min($remaining, $defAvail[$j]);
                if ($take <= 0) continue;
                $pairs[] = ['atk_idx'=>$i,'def_idx'=>$j,'share'=>$take / $a['power']];
                $remaining -= $take;
                $defAvail[$j] -= $take;
                if ($remaining <= 0) break;
            }
            // Over-spill: distribuir sobrante a cualquier compatible restante (ignorando umbral)
            if ($remaining > 0) {
                foreach ($def as $j=>$d) {
                    if ($defAvail[$j] <= 0) continue;
                    if (!$this->can_hit($a['type'], $d['type'])) continue;
                    $take = min($remaining, $defAvail[$j]);
                    if ($take <= 0) continue;
                    $pairs[] = ['atk_idx'=>$i,'def_idx'=>$j,'share'=>$take / $a['power']];
                    $remaining -= $take;
                    $defAvail[$j] -= $take;
                    if ($remaining <= 0) break;
                }
            }
        }
        return $pairs;
    }

    private function can_hit($atkType, $defType): bool {
        // Simplificación: ranged puede golpear a todos; melee no puede golpear flying; flying puede golpear todos
        if ($atkType === 'ranged') return true;
        if ($atkType === 'flying') return true;
        if ($atkType === 'melee' && $defType === 'flying') return false;
        return true;
    }
}
