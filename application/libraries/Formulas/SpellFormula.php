<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SpellFormula {

    private array $cfg;

    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('formulas');
        $this->cfg = $CI->config->item('formulas')['spells'] ?? [];
    }

    public function powerForLevel(int $level): float {
        $p = (float)($this->cfg['power_per_level'] ?? 0.05);
        return 1.0 + ($level * $p);
    }

    public function duration(string $type): int {
        return (int)($this->cfg['duration'][$type] ?? 0);
    }

    public function manaCost(int $baseCost, int $level): int {
        $scale = (float)($this->cfg['mana_cost_scale'] ?? 1.0);
        return (int)round($baseCost * $scale * max(1.0, 1.0 + ($level * 0.0)));
    }
}
