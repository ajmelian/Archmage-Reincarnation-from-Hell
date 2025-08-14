<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Formula {

    private array $stacking;
    private array $caps;
    private array $combat;
    private array $economy;

    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('game');
        $this->stacking = $CI->config->item('stacking') ?? [];
        $this->caps     = $CI->config->item('caps') ?? [];
        $this->combat   = $CI->config->item('combat') ?? [];
        $this->economy  = $CI->config->item('economy') ?? [];
    }

    /** Aplica stacking + caps a una lista de modificadores (e.g., attack_bonus). */
    public function applyBonus(string $key, array $values): float {
        $mode = $this->stacking[$key] ?? 'additive';
        $v = 0.0;
        if ($mode === 'multiplicative') {
            $mult = 1.0;
            foreach ($values as $x) $mult *= (1.0 + (float)$x);
            $v = $mult - 1.0;
        } else {
            foreach ($values as $x) $v += (float)$x;
        }
        $cap = $this->caps[$key] ?? null;
        if ($cap !== null) $v = min($v, (float)$cap);
        return $v;
    }

    /** Daño base según (Atk - Def) escalado y capado por mínimo. */
    public function baseDamage(int $atk, int $def): int {
        $scale = (float)($this->combat['damage_scale'] ?? 0.15);
        $min   = (int)($this->combat['min_damage'] ?? 0);
        $raw = ($atk - $def) * $scale;
        return max($min, (int)round($raw));
    }

    /** HP por unidad por defecto si no está definido en la unidad. */
    public function unitHpDefault(): int {
        return (int)($this->combat['hp_per_unit'] ?? 1);
    }

    /** Rango de jitter configurado (si se usa). */
    public function jitterSpread(): int {
        return (int)($this->combat['spread'] ?? 0);
    }
}
