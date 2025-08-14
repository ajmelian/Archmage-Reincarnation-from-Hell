<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Motor de hechizos: investigar y lanzar (invocar, buff, daño).
 */
class EngineSpells {

    /** Investiga un hechizo (usa "research" points del estado). */
    public static function researchSpell(array &$state, array $spellDef): string {
        $id = $spellDef['id'];
        $cost = (int)($spellDef['research_cost'] ?? 0);
        $points = (int)($state['resources']['research'] ?? 0);
        $progress = (int)($state['spellsProgress'][$id] ?? 0);
        $need = max(0, $cost - $progress);
        $spend = min($points, $need);
        $state['resources']['research'] = $points - $spend;
        $state['spellsProgress'][$id] = $progress + $spend;
        if ($progress + $spend >= $cost) {
            $state['spellsCompleted'][$id] = true;
            return "Spell $id completed";
        }
        return "Spell $id progress +$spend/$cost";
    }

    /** Lanza un hechizo si hay maná y está completado. Devuelve log y aplica efectos. */
    public static function castSpell(array &$stateCaster, array &$stateTarget, array $spellDef, int $currentTick): string {
        $id = $spellDef['id'];
        $manaCost = (int)($spellDef['mana_cost'] ?? 0);
        if (($stateCaster['resources']['mana'] ?? 0) < $manaCost) return "Not enough mana for $id";
        if (empty($stateCaster['spellsCompleted'][$id])) return "Spell $id not researched";

        $stateCaster['resources']['mana'] -= $manaCost;
        $type = $spellDef['type'] ?? '';
        $effect = json_decode($spellDef['effect'] ?? '{}', true) ?: [];
        $duration = (int)($spellDef['duration'] ?? 0);
        $log = '';

        switch ($type) {
            case 'summon':
                $sum = $effect['summon'] ?? null;
                if ($sum) {
                    $uid = $sum['unitId'] ?? null; $qty = (int)($sum['qty'] ?? 0);
                    if ($uid && $qty > 0) {
                        $stateCaster['army'][$uid] = (int)($stateCaster['army'][$uid] ?? 0) + $qty;
                        $log = "Summoned $qty of $uid";
                    }
                }
                break;
            case 'buff_attack':
            case 'buff_defense':
                $bonus = $effect['attack_bonus'] ?? ($effect['defense_bonus'] ?? 0);
                $stateCaster['activeEffects'][] = ['spellId'=>$id,'expiresTick'=>$currentTick + $duration,'data'=>$effect];
                $log = "Applied $id for $duration ticks";
                break;
            case 'damage_army':
                $dmg = (int)($effect['damage'] ?? 0);
                // Daño plano: eliminar unidades por HP=1 para ejemplo (ajusta a fórmula real)
                $remaining = $dmg;
                foreach ($stateTarget['army'] as $uid => $q) {
                    if ($remaining <= 0) break;
                    $loss = min($q, $remaining);
                    $stateTarget['army'][$uid] = max(0, $q - $loss);
                    $remaining -= $loss;
                }
                $log = "Damaged enemy army -$dmg units (flat)";
                break;
            default:
                $log = "Spell $id has no handler";
        }
        return $log;
    }

    /** Calcula modificadores temporales activos a partir de activeEffects. */
    public static function activeModifiers(array $state, int $currentTick): array {
        $mods = ['attack_bonus'=>0.0,'defense_bonus'=>0.0];
        foreach (($state['activeEffects'] ?? []) as $eff) {
            if (($eff['expiresTick'] ?? 0) >= $currentTick) {
                $data = $eff['data'] ?? [];
                if (isset($data['attack_bonus'])) $mods['attack_bonus'] += (float)$data['attack_bonus'];
                if (isset($data['defense_bonus'])) $mods['defense_bonus'] += (float)$data['defense_bonus'];
            }
        }
        return $mods;
    }

    /** Limpia efectos expirados. */
    public static function cleanupEffects(array &$state, int $currentTick): void {
        $out = [];
        foreach (($state['activeEffects'] ?? []) as $eff) {
            if (($eff['expiresTick'] ?? 0) >= $currentTick) $out[] = $eff;
        }
        $state['activeEffects'] = $out;
    }
}
