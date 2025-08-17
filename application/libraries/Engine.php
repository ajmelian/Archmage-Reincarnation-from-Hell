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


    private function battle_cfg() {
        return $this->CI->config->item('game')['battle_phase'] ?? []; 
    }

    /**
     * Decide el tipo de ataque efectivo de un stack (para híbridos).
     * $stack: ['type'=>'melee|ranged|flying', 'attack_types'=>['melee','ranged']]
     * $defAvailTypes: lista de tipos defensores aún presentes ['melee','ranged','flying']
     */
    private function choose_attack_type($stack, $defAvailTypes) {
        $cfg = $this->battle_cfg();
        $ats = isset($stack['attack_types']) && is_array($stack['attack_types']) ? $stack['attack_types'] : [$stack['type']];
        $ats = array_values(array_unique($ats));
        if (count($ats) === 1) return $ats[0];
        // híbrido: preferencia por melee si hay objetivos no voladores
        if (!empty($cfg['hybrid_prefers_ground'])) {
            if (in_array('melee', $ats, true) && (in_array('melee', $defAvailTypes, true) || in_array('ranged', $defAvailTypes, true))) {
                return 'melee';
            }
        }
        // si hay voladores y el stack puede atacar 'ranged', usar ranged
        if (in_array('ranged', $ats, true) && in_array('flying', $defAvailTypes, true)) return 'ranged';
        // fallback: primer tipo
        return $ats[0];
    }

    /**
     * Determina si un stack con attack_type puede golpear a un tipo de objetivo
     */
    private function can_hit_attack_type($attackType, $defType): bool {
        if ($attackType === 'ranged') return true;
        if ($attackType === 'flying') return true;
        if ($attackType === 'melee' && $defType === 'flying') return false;
        return true;
    }

    /**
     * Fase de daño: aplica daño de A→D basado en los emparejamientos generados por pairing().
     * Cada par tiene 'share' de la potencia del stack atacante destinado a un stack defensor.
     * Se aplican resistencias de unidad del defensor por 'attack_type' y una eficiencia base.
     *
     * $atkStacks/$defStacks: arrays alineados con pairing indices
     *   Stack debe traer:
     *     - 'power': int
     *     - 'type':  'melee|ranged|flying'  (movilidad/defensa)
     *     - 'attack_types': ['melee'|'ranged'|'flying', ...] (opcional; por defecto [type])
     *     - 'unit_resists': ['melee'=>0..1,'ranged'=>0..1,'flying'=>0..1] (opcional; por defecto 0)
     *
     * @return array ['damage_to_def'=>int, 'damage_to_atk'=>int, 'def_losses'=>[j=>loss], 'atk_losses'=>[i=>loss]]
     */
    public function damage_phase(array $atkStacks, array $defStacks, array $pairs): array {
        $cfg = $this->battle_cfg();
        $eff = $cfg['attack_efficiency'] ?? ['melee'=>1.0,'ranged'=>1.0,'flying'=>1.0];
        // Tipos defensores presentes
        $defTypes = [];
        foreach ($defStacks as $ds) { $t = $ds['type'] ?? 'melee'; if (!in_array($t, $defTypes, true)) $defTypes[] = $t; }

        $defLoss = array_fill(0, count($defStacks), 0.0);
        $atkLoss = array_fill(0, count($atkStacks), 0.0);

        // Para cada atacante, elegimos attack_type efectivo
        $atkAttackType = [];
        foreach ($atkStacks as $i=>$as) {
            $atkAttackType[$i] = $this->choose_attack_type($as, $defTypes);
        }

        // Aplicar daño A->D según pairing
        foreach ($pairs as $p) {
            $i = $p['atk_idx']; $j = $p['def_idx']; $share = (float)$p['share'];
            if (!isset($atkStacks[$i]) || !isset($defStacks[$j])) continue;
            $a = $atkStacks[$i]; $d = $defStacks[$j];

            $attackType = $atkAttackType[$i];
            if (!$this->can_hit_attack_type($attackType, $d['type'])) continue;

            $base = max(0.0, (float)$a['power']) * max(0.0, min(1.0, $share));
            $res = 0.0;
            if (!empty($d['unit_resists']) && isset($d['unit_resists'][$attackType])) {
                $res = max(0.0, min(1.0, (float)$d['unit_resists'][$attackType]));
            }
            $effi = (float)($eff[$attackType] ?? 1.0);
            $dmg = $base * $effi * (1.0 - $res);
            $defLoss[$j] += $dmg;
        }

        // Cap de daño por stack
        $capRatio = (float)($cfg['damage_cap_vs_stack'] ?? 1.0);
        foreach ($defLoss as $j=>$loss) {
            $cap = $capRatio * max(0.0, (float)($defStacks[$j]['power'] ?? 0.0));
            if ($loss > $cap) $defLoss[$j] = $cap;
        }

        // (Opcional) Retaliation simple: proporcional a daño recibido (placeholder: 0.0)
        // $atkLoss[...] = ...;

        $sumDef = 0.0; foreach ($defLoss as $v) $sumDef += $v;
        $sumAtk = 0.0; foreach ($atkLoss as $v) $sumAtk += $v;

        return [
            'damage_to_def' => (int)round($sumDef),
            'damage_to_atk' => (int)round($sumAtk),
            'def_losses'    => array_map(function($x){ return (int)round($x); }, $defLoss),
            'atk_losses'    => array_map(function($x){ return (int)round($x); }, $atkLoss),
            'attack_types'  => $atkAttackType,
        ];
    }
}