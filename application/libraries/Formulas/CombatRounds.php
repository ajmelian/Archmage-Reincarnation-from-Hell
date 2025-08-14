<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Multi-round combat with initiative/speed, morale and formations.
 * - Units have: id, attack, defense, hp, qty, speed, morale, damage_type, resist, row ('front'|'back')
 * - Each round: initiative order by speed desc; each actor attacks once.
 * - Targeting: attackers prefer enemy 'front' row; if empty, target 'back'.
 * - Morale: after each round, side may retreat if losses exceed threshold.
 * - Timeline: detailed events for replay/export.
 */
class CombatRounds {

    private array $cfg; // from formulas['combat']

    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('formulas');
        $this->cfg = $CI->config->item('formulas')['combat'] ?? [];
    }

    public function resolve(array $sideA, array $sideB, int $seed = 12345): array {
        mt_srand($seed);
        $A = $this->normalize($sideA);
        $B = $this->normalize($sideB);

        $initA = $this->countUnits($A);
        $initB = $this->countUnits($B);

        $maxRounds = (int)($this->cfg['rounds']['max_rounds'] ?? 6);
        $retreatTh = (float)($this->cfg['rounds']['retreat_threshold'] ?? 0.3);

        $timeline = [];
        $round = 0;

        while ($round < $maxRounds && $this->countUnits($A) > 0 && $this->countUnits($B) > 0) {
            $round++;
            $events = [];
            $events[] = ['type'=>'start_round','round'=>$round,'state'=>$this->snapshot($A,$B)];

            $order = $this->initiativeOrder($A, $B); // list of ['side'=>'A'|'B','idx'=>int]
            foreach ($order as $act) {
                if ($act['side']==='A') {
                    if (!isset($A[$act['idx']]) || $A[$act['idx']]['qty']<=0) continue;
                    $tgt = $this->pickTargetByRow($B);
                    if ($tgt < 0) continue;
                    $res = $this->attack($A[$act['idx']], $B[$tgt]);
                    $B[$tgt]['qty'] -= $res['killed'];
                    if ($B[$tgt]['qty'] < 0) $B[$tgt]['qty'] = 0;
                    $events[] = ['type'=>'attack','actor'=>['side'=>'A','unit'=>$A[$act['idx']]['id']], 'target'=>['side'=>'B','unit'=>$B[$tgt]['id']], 'damage'=>$res['damage'],'killed'=>$res['killed']];
                } else {
                    if (!isset($B[$act['idx']]) || $B[$act['idx']]['qty']<=0) continue;
                    $tgt = $this->pickTargetByRow($A);
                    if ($tgt < 0) continue;
                    $res = $this->attack($B[$act['idx']], $A[$tgt]);
                    $A[$tgt]['qty'] -= $res['killed'];
                    if ($A[$tgt]['qty'] < 0) $A[$tgt]['qty'] = 0;
                    $events[] = ['type'=>'attack','actor'=>['side'=>'B','unit'=>$B[$act['idx']]['id']], 'target'=>['side'=>'A','unit'=>$A[$tgt]['id']], 'damage'=>$res['damage'],'killed'=>$res['killed']];
                }
            }

            // Morale check (retreat if casualties exceed threshold of initial)
            $lostA = ($initA>0) ? 1.0 - ($this->countUnits($A)/$initA) : 1.0;
            $lostB = ($initB>0) ? 1.0 - ($this->countUnits($B)/$initB) : 1.0;
            $retA = ($lostA >= $retreatTh);
            $retB = ($lostB >= $retreatTh);
            if ($retA) $events[] = ['type'=>'morale','side'=>'A','action'=>'retreat','lost_ratio'=>$lostA];
            if ($retB) $events[] = ['type'=>'morale','side'=>'B','action'=>'retreat','lost_ratio'=>$lostB];

            $events[] = ['type'=>'end_round','round'=>$round,'state'=>$this->snapshot($A,$B)];
            $timeline[] = $events;

            if ($retA || $retB) break;
        }

        // compute losses vs originals
        $lossesA = $this->losses($A, $sideA);
        $lossesB = $this->losses($B, $sideB);

        $sumA = array_sum($lossesB ?: [0]); // damage B did to A
        $sumB = array_sum($lossesA ?: [0]); // damage A did to B
        $winner = ($sumA > $sumB) ? 'A' : (($sumB > $sumA) ? 'B' : 'draw');

        // Flat log for legacy viewer
        $log = "";
        foreach ($timeline as $roundEvents) {
            foreach ($roundEvents as $e) {
                if ($e['type']==='attack' && $e['killed']>0) {
                    $log .= "{$e['actor']['side']} {$e['actor']['unit']} kills {$e['killed']} of {$e['target']['unit']}\n";
                }
            }
        }

        return ['winner'=>$winner,'lossesA'=>$lossesA,'lossesB'=>$lossesB,'timeline'=>$timeline,'log'=>$log];
    }

    private function normalize(array $side): array {
        $out = [];
        foreach ($side as $u) {
            $out[] = [
                'id'=>(string)($u['id'] ?? 'unit'),
                'attack'=>(int)($u['attack'] ?? 0),
                'defense'=>(int)($u['defense'] ?? 0),
                'hp'=>max(1,(int)($u['hp'] ?? 1)),
                'qty'=>max(0,(int)($u['qty'] ?? 0)),
                'speed'=>max(1,(int)($u['speed'] ?? 1)),
                'morale'=>max(0,(int)($u['morale'] ?? 100)),
                'damage_type'=>(string)($u['damage_type'] ?? 'physical'),
                'resist'=> is_array($u['resist'] ?? null) ? $u['resist'] : [],
                'row'=> in_array(($u['row'] ?? 'front'), ['front','back'], true) ? $u['row'] : 'front'
            ];
        }
        return $out;
    }

    private function countUnits(array $side): int {
        $c=0; foreach ($side as $u) $c += (int)$u['qty']; return $c;
    }

    private function snapshot(array $A, array $B): array {
        $map = function($arr) { $m=[]; foreach ($arr as $u){ $m[$u['id']]=$u['qty']; } return $m; };
        return ['A'=>$map($A),'B'=>$map($B)];
    }

    private function initiativeOrder(array $A, array $B): array {
        $order = [];
        foreach ($A as $i=>$u) if ($u['qty']>0) $order[] = ['side'=>'A','idx'=>$i,'speed'=>$u['speed']];
        foreach ($B as $i=>$u) if ($u['qty']>0) $order[] = ['side'=>'B','idx'=>$i,'speed'=>$u['speed']];
        usort($order, function($x,$y){
            if ($x['speed']===$y['speed']) return 0;
            return ($x['speed']>$y['speed']) ? -1 : 1;
        });
        return $order;
    }

    private function pickTargetByRow(array $enemy): int {
        $front = []; $back=[];
        foreach ($enemy as $i=>$u) {
            if ($u['qty']<=0) continue;
            if ($u['row']==='front') $front[]=$i; else $back[]=$i;
        }
        $pool = $front ? $front : $back;
        
        if (!$pool) return -1;
        return $pool[array_rand($pool)];
    }

    private function attack(array $att, array $def): array {
        $atkPower = $att['attack'] * max(1,$att['qty']);
        $defPower = $def['defense'] * max(1,$def['qty']);
        $scale = (float)($this->cfg['damage_scale'] ?? 0.15);
        $min   = (int)($this->cfg['min_damage'] ?? 0);
        $damage = max($min, (int)round(($atkPower - $defPower) * $scale));

        // Resistances based on defender type and explicit stack resist
        $map = $this->cfg['resist_map'][$def['damage_type']] ?? [];
        $selfRes = isset($map[$att['damage_type']]) ? (float)$map[$att['damage_type']] : 0.0;
        $extra = 0.0;
        if (!empty($def['resist']) && isset($def['resist'][$att['damage_type']])) $extra = (float)$def['resist'][$att['damage_type']];
        $res = max(0.0, min(0.95, $selfRes + $extra));

        $effective = (int)floor($damage * (1.0 - $res));
        $killed = max(0, min($def['qty'], (int)floor($effective / max(1,$def['hp']))));
        return ['damage'=>$effective,'killed'=>$killed];
    }

    private function losses(array $end, array $start): array {
        $mapStart = [];
        foreach ($start as $u) { $mapStart[$u['id']] = (int)($u['qty'] ?? 0); }
        $mapEnd = [];
        foreach ($end as $u) { $mapEnd[$u['id']] = (int)$u['qty']; }
        $losses = [];
        foreach ($mapStart as $id=>$qty0) {
            $qty1 = $mapEnd[$id] ?? 0;
            if ($qty1 < $qty0) $losses[$id] = $qty0 - $qty1;
        }
        return $losses;
    }
}
