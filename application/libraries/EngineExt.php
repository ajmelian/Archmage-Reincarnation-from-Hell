<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EngineExt {

    /** Aplica producción por edificios al estado del reino. */
    public static function produce(array &$state, array $buildingDefs): void {
        $resources = &$state['resources'];
        foreach (($state['buildings'] ?? []) as $bid => $qty) {
            if (!isset($buildingDefs[$bid])) continue;
            $out = json_decode($buildingDefs[$bid]['outputs'] ?? '{}', true) ?: [];
            foreach ($out as $res => $val) {
                $resources[$res] = ($resources[$res] ?? 0) + ($val * (int)$qty);
            }
        }
    }

    /** Gasta puntos de investigación en una tecnología. */
    public static function research(array &$state, array $researchDefs, string $techId): array {
        $log = [];
        $points = (int)($state['resources']['research'] ?? 0);
        if (!isset($researchDefs[$techId])) { $log[] = "Unknown tech $techId"; return $log; }
        $cost = (int)$researchDefs[$techId]['cost'];
        $progress = (int)($state['researchProgress'][$techId] ?? 0);
        $needed = max(0, $cost - $progress);
        $spend = min($points, $needed);
        $state['resources']['research'] = $points - $spend;
        $state['researchProgress'][$techId] = $progress + $spend;
        if ($progress + $spend >= $cost) {
            $state['researchCompleted'][$techId] = true;
            $log[] = "Completed $techId";
        } else {
            $log[] = "Progress $techId +$spend/$cost";
        }
        return $log;
    }

    /** Calcula factor de bonus por tecnología completada. */
    public static function researchBonuses(array $state, array $researchDefs): array {
        $bonus = ['attack_bonus'=>0,'defense_bonus'=>0,'gold_bonus'=>0,'mana_bonus'=>0];
        foreach (array_keys($state['researchCompleted'] ?? []) as $techId) {
            $eff = json_decode($researchDefs[$techId]['effect'] ?? '{}', true) ?: [];
            foreach ($eff as $k=>$v) { if (isset($bonus[$k])) $bonus[$k] += (float)$v; }
        }
        return $bonus;
    }
}
