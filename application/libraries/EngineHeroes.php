<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EngineHeroes {

    /** Bonos agregados a partir de héroes y objetos equipados. */
    public static function bonuses(array $realmHeroes, array $itemDefs): array {
        $bonus = ['attack_bonus'=>0.0,'defense_bonus'=>0.0,'gold_bonus'=>0.0,'mana_bonus'=>0.0,'research_bonus'=>0.0];
        foreach ($realmHeroes as $h) {
            $stats = json_decode($h['stats'] ?? '{}', true) ?: [];
            foreach ($stats as $k=>$v) { if (isset($bonus[$k])) $bonus[$k] += (float)$v; }
            // Fetch equipped items
            // NOTE: we don't have hero_items here; the controller can pass 'itemsByHero' if needed.
        }
        // If controller provides 'itemsByHero', we can read modifiers from item defs
        return $bonus;
    }

    /** Acumula modificadores de items equipados por héroe. */
    public static function itemsBonuses(array $equippedRecords, array $itemDefs): array {
        $b = ['attack_bonus'=>0.0,'defense_bonus'=>0.0,'gold_bonus'=>0.0,'mana_bonus'=>0.0,'research_bonus'=>0.0];
        foreach ($equippedRecords as $rec) {
            $id = $rec['item_id'] ?? null;
            if (!$id || empty($itemDefs[$id])) continue;
            $mods = json_decode($itemDefs[$id]['modifiers'] ?? '{}', true) ?: [];
            foreach ($mods as $k=>$v) { if (isset($b[$k])) $b[$k] += (float)$v; }
        }
        return $b;
    }
}
