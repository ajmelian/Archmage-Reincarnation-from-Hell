<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EconomyFormula {

    private array $cfg;

    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('formulas');
        $this->cfg = $CI->config->item('formulas')['economy'] ?? [];
    }

    public function produce(array $state): array {
        // $state: ['buildings'=>['gold_mine'=>n,'mana_well'=>n,...],'bonuses'=>['gold'=>[], 'mana'=>[], 'research'=>[]]]
        $gold  = 0.0; $mana = 0.0; $research = 0.0;
        $b = $state['buildings'] ?? [];

        $gold  += ($b['gold_mine']  ?? 0) * ($this->cfg['gold_per_building'] ?? 5.0);
        $mana  += ($b['mana_well']  ?? 0) * ($this->cfg['mana_per_building'] ?? 3.0);
        $research += ($b['academy'] ?? 0) * 1.0;

        // diminishing returns por tierra total (aprox)
        $land = array_sum($b);
        $gold   *= (1.0 - min(0.75, $land * ($this->cfg['dr_gold'] ?? 0.0005)));
        $mana   *= (1.0 - min(0.75, $land * ($this->cfg['dr_mana'] ?? 0.0005)));
        $research *= (1.0 - min(0.75, $land * ($this->cfg['dr_research'] ?? 0.0004)));

        // aplicar bonos externos (usando Formula stacking/caps si están)
        $CI =& get_instance();
        $CI->load->library('Formula');
        $f = new Formula();

        $gold   *= (1.0 + $f->applyBonus('gold_bonus', $state['bonuses']['gold'] ?? []));
        $mana   *= (1.0 + $f->applyBonus('mana_bonus', $state['bonuses']['mana'] ?? []));
        $research *= (1.0 + $f->applyBonus('research_bonus', $state['bonuses']['research'] ?? []));

        // caps finales
        $gold   = $gold   * min(1.0 + ($this->cfg['gold_cap'] ?? 2.0), 3.0);
        $mana   = $mana   * min(1.0 + ($this->cfg['mana_cap'] ?? 2.0), 3.0);
        $research = $research * min(1.0 + ($this->cfg['research_cap'] ?? 2.0), 3.0);

        return ['gold'=>(int)round($gold), 'mana'=>(int)round($mana), 'research'=>(int)round($research)];
    }

    public function researchCost(int $level): int {
        $base = (int)($this->cfg['research_base_cost'] ?? 100);
        $g    = (float)($this->cfg['research_growth'] ?? 1.12);
        return (int)round($base * pow($g, max(0,$level)));
    }
}
