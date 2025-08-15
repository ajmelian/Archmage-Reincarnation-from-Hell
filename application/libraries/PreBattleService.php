<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PreBattleService: resuelve resistencias pre-batalla para 1 hechizo y 1 ítem del atacante.
 * Cadena: Barrier -> Color -> (los ítems son 'plain'; solo Barrier).
 */
class PreBattleService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('game');
        $this->CI->load->library('DeterministicRNG');
    }

    private function cfg() { return $this->CI->config->item('game')['prebattle'] ?? []; }

    /**
     * @param array $context [
     *   'battle_id'=>int|string,
     *   'attacker'=>['realm_id'=>int, 'np'=>int],
     *   'defender'=>['realm_id'=>int, 'np'=>int, 'barrier_pct'=>float 0..1, 'color_resists'=>['red'=>0.2,...]],
     *   'attack_spell'=>['id'=>..., 'name'=>..., 'color'=> 'red|blue|green|white|black', 'base_success'=>0..1],
     *   'attack_item' =>['id'=>..., 'name'=>..., 'color'=>null|color, 'base_success'=>0..1]
     * ]
     * @return array resultado con flags y probabilidades
     */
    public function resolve(array $context): array {
        $cfg = $this->cfg();
        $seed = 'battle:'.$context['battle_id'].':pre';
        $this->CI->deterministicrng->seed($seed);

        $def = $context['defender'] ?? [];
        $bar = min(max((float)($def['barrier_pct'] ?? 0.0), 0.0), (float)($cfg['barrier_max'] ?? 0.75));
        $cres = $def['color_resists'] ?? [];

        $out = ['spell'=>['applied'=>false,'p'=>0.0,'stages'=>[]], 'item'=>['applied'=>false,'p'=>0.0,'stages'=>[]]];

        // Resolver SPELL
        if (!empty($context['attack_spell'])) {
            $sp = $context['attack_spell'];
            $p  = isset($sp['base_success']) ? (float)$sp['base_success'] : (float)($cfg['default_spell_base_success'] ?? 1.0);
            // Barrier
            $p *= (1.0 - $bar);
            $out['spell']['stages'][] = ['stage'=>'barrier','barrier'=>$bar,'p'=>max(0.0,$p)];
            // Color
            $color = $sp['color'] ?? null;
            if ($color && isset($cres[$color])) {
                $p *= (1.0 - max(0.0, min(1.0, (float)$cres[$color])));
                $out['spell']['stages'][] = ['stage'=>'color','color'=>$color,'resist'=>$cres[$color],'p'=>max(0.0,$p)];
            } else {
                $out['spell']['stages'][] = ['stage'=>'color','color'=>$color,'resist'=>0.0,'p'=>max(0.0,$p)];
            }
            // Tirada determinista
            $r = $this->CI->deterministicrng->nextFloat();
            $out['spell']['p'] = max(0.0, min(1.0, $p));
            $out['spell']['roll'] = $r;
            $out['spell']['applied'] = ($r < $out['spell']['p']);
        }

        // Resolver ITEM (plain: solo Barrier, ignora color)
        if (!empty($context['attack_item'])) {
            $it = $context['attack_item'];
            $p  = isset($it['base_success']) ? (float)$it['base_success'] : 1.0;
            $p *= (1.0 - $bar);
            $out['item']['stages'][] = ['stage'=>'barrier','barrier'=>$bar,'p'=>max(0.0,$p)];
            $r = $this->CI->deterministicrng->nextFloat();
            $out['item']['p'] = max(0.0, min(1.0, $p));
            $out['item']['roll'] = $r;
            $out['item']['applied'] = ($r < $out['item']['p']);
        }

        $out['defender'] = ['barrier_pct'=>$bar,'color_resists'=>$cres];
        return $out;
    }
}
