<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CombatFormula {

    private array $cfg;
    private Formula $base;

    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('formulas');
        $CI->load->library('Formula');
        $this->cfg  = $CI->config->item('formulas')['combat'] ?? [];
        $this->base = new Formula();
    }

    /** Calcula pérdidas de una ronda entre dos bandos (arrays de stacks). */
    public function resolveRound(array $sideA, array $sideB): array {
        // Reutilizamos normalización del Engine pero local aquí (hp default)
        $A = $this->normalize($sideA);
        $B = $this->normalize($sideB);

        [$pA, $dA] = $this->powerDefense($A);
        [$pB, $dB] = $this->powerDefense($B);

        // daño bruto base
        $dmgToA = max((int)($this->cfg['min_damage'] ?? 0), (int)round(($pB - $dA) * ($this->cfg['damage_scale'] ?? 0.15)));
        $dmgToB = max((int)($this->cfg['min_damage'] ?? 0), (int)round(($pA - $dB) * ($this->cfg['damage_scale'] ?? 0.15)));

        // tipo dominante por lado
        $typeA = $this->dominantDamage($A);
        $typeB = $this->dominantDamage($B);

        // aplica resistencias promedio del stack objetivo seleccionado (proporcional simple)
        $idxA = $this->pickTarget($A);
        $idxB = $this->pickTarget($B);

        $lossesA = []; $lossesB = [];
        if ($idxA >= 0) {
            $lost = $this->applyResistedDamage($A[$idxA], $dmgToA, $typeB);
            if ($lost > 0) $lossesA[$A[$idxA]['id']] = $lost;
        }
        if ($idxB >= 0) {
            $lost = $this->applyResistedDamage($B[$idxB], $dmgToB, $typeA);
            if ($lost > 0) $lossesB[$B[$idxB]['id']] = $lost;
        }

        // ganador por pérdidas causadas
        $sumA = array_sum($lossesB ?: [0]);
        $sumB = array_sum($lossesA ?: [0]);
        $winner = ($sumA > $sumB) ? 'A' : (($sumB > $sumA) ? 'B' : 'draw');

        return ['winner'=>$winner,'lossesA'=>$lossesA,'lossesB'=>$lossesB];
    }

    private function normalize(array $side): array {
        $out = [];
        foreach ($side as $u) {
            $hp  = max(1, (int)($u['hp'] ?? $this->base->unitHpDefault()));
            $qty = max(0, (int)($u['qty'] ?? 0));
            $out[] = [
                'id'=>(string)($u['id'] ?? 'unit'),
                'hp'=>$hp, 'qty'=>$qty,
                'attack'=>(int)($u['attack'] ?? 0),
                'defense'=>(int)($u['defense'] ?? 0),
                'damage_type'=>(string)($u['damage_type'] ?? 'physical'),
                'resist'=> is_array($u['resist'] ?? null) ? $u['resist'] : []
            ];
        }
        return $out;
    }

    private function powerDefense(array $side): array {
        $p=0; $d=0;
        foreach ($side as $u) { $p += $u['attack']*$u['qty']; $d += $u['defense']*$u['qty']; }
        return [$p,$d];
    }

    private function dominantDamage(array $side): string {
        $count = ['physical'=>0,'magical'=>0];
        foreach ($side as $u) {
            $dt = $u['damage_type'] ?? 'physical';
            if (!isset($count[$dt])) $count[$dt] = 0;
            $count[$dt] += $u['qty'];
        }
        return ($count['magical'] > $count['physical']) ? 'magical' : 'physical';
    }

    private function pickTarget(array $units): int {
        if (!$units) return -1;
        $sum = 0; foreach ($units as $u) $sum += max(1, $u['hp']*$u['qty']);
        $r = mt_rand(1, max(1,$sum)); $acc = 0;
        foreach ($units as $i=>$u) { $acc += max(1, $u['hp']*$u['qty']); if ($r <= $acc) return $i; }
        return 0;
    }

    private function applyResistedDamage(array $stack, int $incoming, string $type): int {
        $hp  = max(1, (int)$stack['hp']);
        $qty = max(0, (int)$stack['qty']);
        if ($qty <= 0 || $incoming <= 0) return 0;

        $map = $this->cfg['resist_map'][$stack['damage_type']] ?? [];
        $selfRes = isset($map[$type]) ? (float)$map[$type] : 0.0;
        $extra   = 0.0;
        if (!empty($stack['resist']) && is_array($stack['resist']) && isset($stack['resist'][$type])) {
            $extra = (float)$stack['resist'][$type];
        }
        $res = max(0.0, min(0.95, $selfRes + $extra));
        $effective = (int)floor($incoming * (1.0 - $res));
        return max(0, min($qty, (int)floor($effective / $hp)));
    }
}
