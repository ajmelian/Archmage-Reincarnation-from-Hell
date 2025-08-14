<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Librería Engine: RNG determinista + esqueleto de resolución de combate.
 * Extiende aquí con economía, investigación, hechizos, etc.
 */
class Engine {

    public function rng(int $seed): XorShift32 {
        return new XorShift32($seed);
    }

    public function resolveCombat(array $sideA, array $sideB, int $seed = 12345): array {
        $rng = $this->rng($seed);
        $log = [];
        $lossA = $lossB = [];

        $powerA = array_sum(array_map(fn($u)=>($u['attack'] ?? 0) * ($u['qty'] ?? 0), $sideA));
        $powerB = array_sum(array_map(fn($u)=>($u['attack'] ?? 0) * ($u['qty'] ?? 0), $sideB));
        $defA   = array_sum(array_map(fn($u)=>($u['defense'] ?? 0) * ($u['qty'] ?? 0), $sideA));
        $defB   = array_sum(array_map(fn($u)=>($u['defense'] ?? 0) * ($u['qty'] ?? 0), $sideB));

        $dmgToA = max(0, (int)round(($powerB - $defA) * 0.15));
        $dmgToB = max(0, (int)round(($powerA - $defB) * 0.15));

        $sumQtyA = array_sum(array_map(fn($x)=>$x['qty'] ?? 0, $sideA)) ?: 1;
        $sumQtyB = array_sum(array_map(fn($x)=>$x['qty'] ?? 0, $sideB)) ?: 1;

        foreach ($sideA as $u) {
            $hp = max(1, (int)($u['hp'] ?? 1));
            $share = $dmgToA > 0 ? max(0, (int)round($dmgToA * (($u['qty'] ?? 0) / $sumQtyA))) : 0;
            $jitter = $rng->uniformInt(-2, 2);
            $lost = max(0, min(($u['qty'] ?? 0), (int)floor(($share + $jitter) / $hp)));
            $lossA[$u['id'] ?? 'u'] = $lost;
        }
        foreach ($sideB as $u) {
            $hp = max(1, (int)($u['hp'] ?? 1));
            $share = $dmgToB > 0 ? max(0, (int)round($dmgToB * (($u['qty'] ?? 0) / $sumQtyB))) : 0;
            $jitter = $rng->uniformInt(-2, 2);
            $lost = max(0, min(($u['qty'] ?? 0), (int)floor(($share + $jitter) / $hp)));
            $lossB[$u['id'] ?? 'u'] = $lost;
        }

        $log[] = sprintf('PowerA=%d PowerB=%d DmgToA=%d DmgToB=%d', $powerA, $powerB, $dmgToA, $dmgToB);
        return ['lossesA'=>$lossA, 'lossesB'=>$lossB, 'log'=>implode("\n",$log)];
    }
}

class XorShift32 {
    private int $state;
    public function __construct(int $seed) {
        $this->state = $seed & 0xFFFFFFFF;
        if ($this->state === 0) $this->state = 0x6d2b79f5;
    }
    private function next(): int {
        $x = $this->state;
        $x ^= ($x << 13) & 0xFFFFFFFF;
        $x ^= ($x >> 17);
        $x ^= ($x << 5) & 0xFFFFFFFF;
        return $this->state = $x & 0xFFFFFFFF;
    }
    public function uniformInt(int $min, int $max): int {
        $u = ($this->next() & 0xFFFFFFFF) / 4294967296.0;
        return $min + (int)floor($u * (($max - $min) + 1));
    }
}
