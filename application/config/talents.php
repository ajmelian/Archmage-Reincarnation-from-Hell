<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['talents'] = [
    // Stacking mode: 'add' (sum %), 'mult' (multiplicative), 'max' (take max)
    'stacking' => [
        'gold_pct'     => 'add',
        'mana_pct'     => 'add',
        'research_pct' => 'add',
        'attack_pct'   => 'add',
        'defense_pct'  => 'add',
    ],
    // Hard caps per key (applied after stacking): numbers are +% cap, e.g. 2.0 == +200%
    'caps' => [
        'gold_pct'     => 2.0,
        'mana_pct'     => 2.0,
        'research_pct' => 1.5,
        'attack_pct'   => 2.5,
        'defense_pct'  => 2.5,
    ],
    // Flat bonuses (per-unit or absolute) just sum, can be capped too
    'flat_caps' => [
        'unit_attack_flat' => 999999,
        'unit_defense_flat'=> 999999,
    ],

    // Item set thresholds → effect keys expected in item_set_def.bonuses JSON
    // e.g. { "2": {"attack_pct":0.05}, "4":{"attack_pct":0.1,"defense_pct":0.1} }
];
