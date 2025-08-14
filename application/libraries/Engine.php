<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Motor de combate simplificado con soporte para:
 * - Fórmulas configurables (Formula.php / config/game.php)
 * - Tipos de daño (physical/magical) y resistencias por unidad (array 'resist')
 * - Selección de objetivo configurable (proportional | focus_low_hp | focus_high_hp)
 * - Registro de pérdidas por ronda (round log) para replays
 *
 * Espera que las unidades vengan con forma:
 *   [
 *     ['id'=>'swordsman','attack'=>10,'defense'=>8,'hp'=>1,'qty'=>100,
 *      'damage_type'=>'physical','resist'=>['physical'=>0.1,'magical'=>0.0]],
 *     ...
 *   ]
 */
class Engine {

    /** @var Formula */
    private Formula $formula;

    /** Inicializa la librería Formula en cada resolución de combate. */
    private function bootFormula(): void {
        if (!isset($this->formula)) {
            $CI =& get_instance();
            $CI->load->library('Formula');
            $this->formula = new Formula();
        }
    }

    /**
     * Resuelve un combate de 1 “asalto” (round) y devuelve pérdidas y log.
     *
     * @param array $sideA conjunto de unidades del lado A
     * @param array $sideB conjunto de unidades del lado B
     * @param int   $seed  semilla para RNG (determinismo en pruebas/replays)
     * @return array ['winner'=>'A'|'B'|'draw','lossesA'=>[],'lossesB'=>[],'log'=>string,'rounds'=>array]
     */
    public function resolveCombat(array $sideA, array $sideB, int $seed = 12345): array {
        $this->bootFormula();

        // Normaliza unidades y calcula HP total por stack
        $A = $this->normalizeUnits($sideA);
        $B = $this->normalizeUnits($sideB);

        // Potencia agregada (muy simplificada) y defensas agregadas
        [$powerA, $defA] = $this->powerAndDefense($A);
        [$powerB, $defB] = $this->powerAndDefense($B);

        // Daño base según fórmulas (antes de resistencias)
        $dmgToA = $this->formula->baseDamage($powerB, $defA); // base before resist
        $dmgToB = $this->formula->baseDamage($powerA, $defB); // base before resist

        // Jitter simple (opcional): usa mt_rand que ya es suficiente para distribución
        // (spread está en config, pero aquí mantenemos una ronda estable)
        $log = '';
        $roundLog = [];

        // Distribuye daño hacia un objetivo por lado con targeting desde config
        $idxA = $this->pickTarget($A); // objetivo que recibe daño en A
        $idxB = $this->pickTarget($B); // objetivo que recibe daño en B

        $lossesA = [];
        $lossesB = [];

        if ($idxA >= 0 && isset($A[$idxA])) {
            $lost = $this->applyDamageToStack($A[$idxA], $dmgToA, $this->dominantDamageType($B)); // daño entrante desde B
            if ($lost > 0) {
                $lossesA[$A[$idxA]['id']] = $lost;
                $log .= "A loses {$lost} of {$A[$idxA]['id']}\n";
                $roundLog[] = ['side'=>'A','unit'=>$A[$idxA]['id'],'lost'=>$lost];
            }
        }
        if ($idxB >= 0 && isset($B[$idxB])) {
            $lost = $this->applyDamageToStack($B[$idxB], $dmgToB, $this->dominantDamageType($A)); // daño entrante desde A
            if ($lost > 0) {
                $lossesB[$B[$idxB]['id']] = $lost;
                $log .= "B loses {$lost} of {$B[$idxB]['id']}\n";
                $roundLog[] = ['side'=>'B','unit'=>$B[$idxB]['id'],'lost'=>$lost];
            }
        }

        // Ganador por daño causado (muy básico). Empate si iguales o ambos 0.
        $sumA = array_sum($lossesB ?: [0]);
        $sumB = array_sum($lossesA ?: [0]);
        $winner = ($sumA > $sumB) ? 'A' : (($sumB > $sumA) ? 'B' : 'draw');

        return [
            'winner'  => $winner,
            'lossesA' => $lossesA,
            'lossesB' => $lossesB,
            'log'     => $log,
            'rounds'  => $roundLog
        ];
    }

    /** Normaliza unidades: aplica defaults, calcula hpTotal para targeting proporcional. */
    private function normalizeUnits(array $side): array {
        $out = [];
        foreach ($side as $u) {
            $hp   = max(1, (int)($u['hp'] ?? $this->formula->unitHpDefault()));
            $qty  = max(0, (int)($u['qty'] ?? 0));
            $id   = (string)($u['id'] ?? 'unit');
            $atk  = (int)($u['attack']  ?? 0);
            $def  = (int)($u['defense'] ?? 0);
            $dtype = (string)($u['damage_type'] ?? 'physical');
            $res   = $u['resist'] ?? []; // ['physical'=>0.1,'magical'=>0.0]
            $hpTotal = $hp * $qty;

            $out[] = [
                'id'=>$id,'attack'=>$atk,'defense'=>$def,'hp'=>$hp,'qty'=>$qty,
                'hpTotal'=>$hpTotal,'damage_type'=>$dtype,'resist'=>$res
            ];
        }
        return $out;
    }

    /** Agrega potencia y defensa brutas del lado (simple). */
    private function powerAndDefense(array $side): array {
        $power = 0; $def = 0;
        foreach ($side as $u) {
            $power += (int)$u['attack']  * (int)$u['qty'];
            $def   += (int)$u['defense'] * (int)$u['qty'];
        }
        return [$power, $def];
    }

    /** Elige un objetivo según estrategia en config/game.php::combat.targeting. */
    private function pickTarget(array $units): int {
        // $units: array de stacks con 'hpTotal', 'qty', 'hp'
        $CI =& get_instance();
        $CI->load->config('game');
        $strategy = $CI->config->item('combat')['targeting'] ?? 'proportional';
        if (empty($units)) return -1;

        if ($strategy === 'focus_low_hp') {
            $minHp = PHP_INT_MAX; $idx = 0;
            foreach ($units as $i=>$u) {
                $hp = $u['hpTotal'] ?? ($u['qty'] * $u['hp']);
                if ($hp < $minHp) { $minHp=$hp; $idx=$i; }
            }
            return $idx;
        } elseif ($strategy === 'focus_high_hp') {
            $maxHp = -1; $idx = 0;
            foreach ($units as $i=>$u) {
                $hp = $u['hpTotal'] ?? ($u['qty'] * $u['hp']);
                if ($hp > $maxHp) { $maxHp=$hp; $idx=$i; }
            }
            return $idx;
        } else {
            // proporcional por HP total
            $sum = 0; foreach ($units as $u) { $sum += max(1, (int)($u['hpTotal'] ?? ($u['qty']*$u['hp']))); }
            $r = mt_rand(1, max(1,$sum)); $acc=0;
            foreach ($units as $i=>$u) {
                $acc += max(1, (int)($u['hpTotal'] ?? ($u['qty']*$u['hp'])));
                if ($r <= $acc) return $i;
            }
            return 0;
        }
    }

    /** Determina el tipo de daño “dominante” del lado atacante (simplificado). */
    private function dominantDamageType(array $side): string {
        $count = ['physical'=>0,'magical'=>0];
        foreach ($side as $u) {
            $dt = $u['damage_type'] ?? 'physical';
            if (!isset($count[$dt])) $count[$dt]=0;
            $count[$dt] += (int)$u['qty'];
        }
        return ($count['magical'] > $count['physical']) ? 'magical' : 'physical';
    }

    /**
     * Aplica daño “plano” a un stack y devuelve cuántas unidades se pierden.
     * Aplica resistencia según damageType entrante (si existe).
     */
    private function applyDamageToStack(array $stack, int $incomingDamage, string $damageType): int {
        $hp   = max(1, (int)$stack['hp']);
        $qty  = max(0, (int)$stack['qty']);
        if ($qty <= 0 || $incomingDamage <= 0) return 0;

        $resist = 0.0;
        if (!empty($stack['resist']) && is_array($stack['resist']) && isset($stack['resist'][$damageType])) {
            $resist = max(0.0, min(0.95, (float)$stack['resist'][$damageType]));
        }
        $effective = (int)floor($incomingDamage * (1.0 - $resist));
        if ($effective <= 0) return 0;

        $lost = (int)floor($effective / $hp);
        return max(0, min($qty, $lost));
    }
}
