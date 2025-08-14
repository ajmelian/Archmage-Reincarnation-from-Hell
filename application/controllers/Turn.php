<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Turn extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library(['Engine','EngineExt','EngineSpells','EngineHeroes']);
        $this->load->model(['Realm_model','Unit_model','Building_model','Research_model','Spell_model','Hero_model','Realmhero_model','Item_model','Inventory_model']);
        $this->load->database();
    }

    public function run() {
        $tick = $this->getNextTick();
        echo "Resolviendo turno $tick...\n";

        $buildingDefs = $this->Building_model->mapById();
        $unitDefs = []; foreach ($this->Unit_model->all() as $u) { $unitDefs[$u['id']] = $u; }
        $researchDefs = $this->Research_model->mapById();

        // 1) Producción para todos los reinos
        $realms = $this->db->get('realms')->result_array();
        foreach ($realms as $realm) {
            $reportLines = [];
            $state = $this->Realm_model->loadState($realm);

            // Producción base por edificios
            EngineExt::produce($state, $buildingDefs);
            $reportLines[] = 'Production applied';

            // Bonos de héroes/objetos a recursos
            $realmHeroes = $this->Realmhero_model->forRealm((int)$realm['id']);
            // Equipamiento por héroe
            $equipped = $this->db->get_where('hero_items', ['realm_hero_id IN (SELECT id FROM realm_heroes WHERE realm_id='.$realm['id'].')'=>NULL])->result_array(); // workaround CI for IN
            // Alternativa: manual join
            $equipped = $this->db->query('SELECT hi.* FROM hero_items hi JOIN realm_heroes rh ON rh.id=hi.realm_hero_id WHERE rh.realm_id=?', [(int)$realm['id']])->result_array();
            $itemsByHero = [];
            foreach ($equipped as $e) { $itemsByHero[$e['realm_hero_id']][] = $e; }
            $itemDefs = $this->Item_model->mapById();

            $bHeroes = EngineHeroes::bonuses($realmHeroes, $itemDefs);
            $bItems  = EngineHeroes::itemsBonuses($equipped, $itemDefs);
            $goldMult = 1.0 + ($bHeroes['gold_bonus'] + $bItems['gold_bonus']);
            $manaMult = 1.0 + ($bHeroes['mana_bonus'] + $bItems['mana_bonus']);
            $resMult  = 1.0 + ($bHeroes['research_bonus'] + $bItems['research_bonus']);
            $state['resources']['gold'] = (int)floor($state['resources']['gold'] * $goldMult);
            $state['resources']['mana'] = (int)floor($state['resources']['mana'] * $manaMult);
            $state['resources']['research'] = (int)floor($state['resources']['research'] * $resMult);

            // Bonos de investigación aplicados a recursos recién generados
            $bonus = EngineExt::researchBonuses($state, $researchDefs);
            if ($bonus['gold_bonus'] > 0)  $state['resources']['gold']  = (int)floor($state['resources']['gold']  * (1.0 + $bonus['gold_bonus']));
            if ($bonus['mana_bonus'] > 0)  $state['resources']['mana']  = (int)floor($state['resources']['mana']  * (1.0 + $bonus['mana_bonus']));

            $this->Realm_model->saveState((int)$realm['id'], $state);
        }

        // 2) Aplicar órdenes por reino en orden lógico
        $orders = $this->db->order_by('created_at','ASC')->get_where('orders', ['tick'=>$tick, 'status'=>'pending'])->result_array();
        // Agrupar por user_id
        $byUser = [];
        foreach ($orders as $o) { $byUser[$o['user_id']][] = $o; }

        foreach ($byUser as $userId => $list) {
            $realm = $this->Realm_model->getOrCreate((int)$userId);
            $state = $this->Realm_model->loadState($realm);

            // Primero: explore
            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'explore') {
                    $amount = max(0, (int)($payload['amount'] ?? 0));
                    $state['resources']['land'] = (int)$state['resources']['land'] + $amount; // 1:1 simple
                    $this->markApplied($o['id']);
                    $reportLines[] = 'Order applied: '.($payload['type'] ?? '');
                }
            }

            // Segundo: research (gastar puntos existentes)
            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'research') {
                    $techId = (string)($payload['techId'] ?? '');
                    $log = EngineExt::research($state, $researchDefs, $techId);
                    $this->markApplied($o['id']);
                    $reportLines[] = 'Order applied: '.($payload['type'] ?? '');
                }
            }

            // Tercero: recruit
            // Héroes: contratación
            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'hire_hero') {
                    $hid = (string)($payload['heroId'] ?? '');
                    $defs = $this->Hero_model->mapById();
                    if (isset($defs[$hid])) {
                        $cost = (int)($defs[$hid]['gold_cost'] ?? 200);
                        if (($state['resources']['gold'] ?? 0) >= $cost) {
                            $state['resources']['gold'] -= $cost;
                            $stats = json_decode($defs[$hid]['base_stats'] ?? '{}', true) ?: [];
                            $this->Realmhero_model->add((int)$realm['id'], $hid, $stats);
                            $this->markApplied($o['id']);
                    $reportLines[] = 'Order applied: '.($payload['type'] ?? '');
                        } else {
                            $this->markRejected($o['id'], 'Not enough gold for hero');
                        }
                    } else {
                        $this->markRejected($o['id'], 'Unknown hero');
                    }
                }
            }

            // Equipar item a héroe
            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'equip_item') {
                    $rid = (int)($payload['realmHeroId'] ?? 0);
                    $itemId = (string)($payload['itemId'] ?? '');
                    $hero = $this->db->get_where('realm_heroes', ['id'=>$rid,'realm_id'=>$realm['id']])->row_array();
                    $itemDefs = $this->Item_model->mapById();
                    if ($hero && isset($itemDefs[$itemId])) {
                        // check inventory and slot uniqueness
                        $slot = $itemDefs[$itemId]['slot'] ?? 'trinket';
                        $exists = $this->db->get_where('hero_items', ['realm_hero_id'=>$rid,'slot'=>$slot])->row_array();
                        if ($exists) { $this->markRejected($o['id'], 'Slot already equipped'); continue; }
                        if ($this->Inventory_model->take((int)$realm['id'], $itemId, 1)) {
                            $this->db->insert('hero_items', ['realm_hero_id'=>$rid,'item_id'=>$itemId,'slot'=>$slot,'created_at'=>time()]);
                            $this->markApplied($o['id']);
                    $reportLines[] = 'Order applied: '.($payload['type'] ?? '');
                        } else {
                            $this->markRejected($o['id'], 'Item not in inventory');
                        }
                    } else {
                        $this->markRejected($o['id'], 'Invalid hero or item');
                    }
                }
            }

            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'recruit') {
                    $uid = (string)($payload['unitId'] ?? '');
                    $qty = max(0, (int)($payload['qty'] ?? 0));
                    if ($qty > 0 && isset($unitDefs[$uid])) {
                        $cost = (int)($unitDefs[$uid]['cost'] ?? 0) * $qty;
                        if (($state['resources']['gold'] ?? 0) >= $cost) {
                            $state['resources']['gold'] -= $cost;
                            $state['army'][$uid] = (int)($state['army'][$uid] ?? 0) + $qty;
                            $this->markApplied($o['id']);
                    $reportLines[] = 'Order applied: '.($payload['type'] ?? '');
                        } else {
                            $this->markRejected($o['id'], 'Not enough gold');
                        }
                    } else {
                        $this->markRejected($o['id'], 'Unknown unit or qty');
                    }
                }
            }

            
            // Hechizos: investigación
            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'spell_research') {
                    $sid = (string)($payload['spellId'] ?? '');
                    $defs = $this->Spell_model->mapById();
                    if (isset($defs[$sid])) {
                        $msg = EngineSpells::researchSpell($state, $defs[$sid]);
                        $this->markApplied($o['id']);
                    $reportLines[] = 'Order applied: '.($payload['type'] ?? '');
                    } else {
                        $this->markRejected($o['id'], 'Unknown spell');
                    }
                }
            }

            // Hechizos: lanzamiento
            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'spell_cast') {
                    $sid = (string)($payload['spellId'] ?? '');
                    $targetId = (int)($payload['targetRealmId'] ?? 0);
                    $defs = $this->Spell_model->mapById();
                    $target = $this->db->get_where('realms', ['id'=>$targetId])->row_array();
                    if ($target && isset($defs[$sid])) {
                        $stateTarget = $this->Realm_model->loadState($target);
                        $msg = EngineSpells::castSpell($state, $stateTarget, $defs[$sid], $tick);
                        $this->Realm_model->saveState((int)$target['id'], $stateTarget);
                        $this->db->insert('spell_logs', [
                            'tick'=>$tick,
                            'caster_realm_id'=>$realm['id'],
                            'target_realm_id'=>$target['id'],
                            'spell_id'=>$sid,
                            'log'=>$msg,
                            'created_at'=>time()
                        ]);
                        $this->markApplied($o['id']);
                    $reportLines[] = 'Order applied: '.($payload['type'] ?? '');
                    } else {
                        $this->markRejected($o['id'], 'Spell cast target/def not found');
                    }
                }
            }

            // Limpieza de efectos expirados
            EngineSpells::cleanupEffects($state, $tick);

            // Cuarto: attack
            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'attack') {
                    $targetId = (int)($payload['targetRealmId'] ?? 0);
                    $target = $this->db->get_where('realms', ['id'=>$targetId])->row_array();
                    if ($target) {
                        $stateA = $state;
                        $stateB = $this->Realm_model->loadState($target);

                        // Construir lados de combate desde armies y defs (hp=1 placeholder)
                        $sideA = [];
                        foreach ($stateA['army'] as $uid=>$q) {
                            if (!isset($unitDefs[$uid])) continue;
                            $sideA[] = ['id'=>$uid,'attack'=>(int)$unitDefs[$uid]['attack'],'defense'=>(int)$unitDefs[$uid]['defense'],'hp'=>(int)($unitDefs[$uid]['hp'] ?? 1),'qty'=>(int)$q];
                        }
                        $sideB = [];
                        foreach ($stateB['army'] as $uid=>$q) {
                            if (!isset($unitDefs[$uid])) continue;
                            $sideB[] = ['id'=>$uid,'attack'=>(int)$unitDefs[$uid]['attack'],'defense'=>(int)$unitDefs[$uid]['defense'],'hp'=>(int)($unitDefs[$uid]['hp'] ?? 1),'qty'=>(int)$q];
                        }

                        $seed = crc32($tick.'-'.$realm['id'].'-'.$target['id']);
                        $modsA = EngineSpells::activeModifiers($stateA, $tick);
$modsB = EngineSpells::activeModifiers($stateB, $tick);
// Apply research bonuses too
$rbA = EngineExt::researchBonuses($stateA, $researchDefs);
$rhA = $this->Realmhero_model->forRealm((int)$realm['id']);
$eqA = $this->db->query('SELECT hi.* FROM hero_items hi JOIN realm_heroes rh ON rh.id=hi.realm_hero_id WHERE rh.realm_id=?', [(int)$realm['id']])->result_array();
$ihA = EngineHeroes::itemsBonuses($eqA, $itemDefs);
$bhA = EngineHeroes::bonuses($rhA, $itemDefs);
$rbB = EngineExt::researchBonuses($stateB, $researchDefs);
$rhB = $this->Realmhero_model->forRealm((int)$target['id']);
$eqB = $this->db->query('SELECT hi.* FROM hero_items hi JOIN realm_heroes rh ON rh.id=hi.realm_hero_id WHERE rh.realm_id=?', [(int)$target['id']])->result_array();
$ihB = EngineHeroes::itemsBonuses($eqB, $itemDefs);
$bhB = EngineHeroes::bonuses($rhB, $itemDefs);
$attackMultA = 1.0 + ($modsA['attack_bonus'] + ($rbA['attack_bonus'] ?? 0) + ($bhA['attack_bonus'] ?? 0) + ($ihA['attack_bonus'] ?? 0));
$defenseMultA = 1.0 + ($modsA['defense_bonus'] + ($rbA['defense_bonus'] ?? 0) + ($bhA['defense_bonus'] ?? 0) + ($ihA['defense_bonus'] ?? 0));
$attackMultB = 1.0 + ($modsB['attack_bonus'] + ($rbB['attack_bonus'] ?? 0) + ($bhB['attack_bonus'] ?? 0) + ($ihB['attack_bonus'] ?? 0));
$defenseMultB = 1.0 + ($modsB['defense_bonus'] + ($rbB['defense_bonus'] ?? 0) + ($bhB['defense_bonus'] ?? 0) + ($ihB['defense_bonus'] ?? 0));

foreach ($sideA as &$u) { $u['attack'] = (int)round($u['attack'] * $attackMultA); $u['defense'] = (int)round($u['defense'] * $defenseMultA); }
foreach ($sideB as &$u) { $u['attack'] = (int)round($u['attack'] * $attackMultB); $u['defense'] = (int)round($u['defense'] * $defenseMultB); }
unset($u);

$result = $this->engine->resolveCombat($sideA, $sideB, $seed);

                        // Aplicar pérdidas
                        foreach ($result['lossesA'] as $uid=>$lost) {
                            $state['army'][$uid] = max(0, (int)($state['army'][$uid] ?? 0) - (int)$lost);
                        }
                        foreach ($result['lossesB'] as $uid=>$lost) {
                            $stateB['army'][$uid] = max(0, (int)($stateB['army'][$uid] ?? 0) - (int)$lost);
                        }

                        // Guardar battle log
                        $this->db->insert('battles', [
                            'tick'=>$tick,
                            'attacker_user_id'=>$realm['user_id'],
                            'defender_user_id'=>$target['user_id'],
                            'log'=>$result['log'],
                            'created_at'=>time()
                        ]);

                        // Persistir estados
                        $this->Realm_model->saveState((int)$realm['id'], $state);
                        $this->Realm_model->saveState((int)$target['id'], $stateB);
                        $this->markApplied($o['id']);
                    $reportLines[] = 'Order applied: '.($payload['type'] ?? '');
                    } else {
                        $this->markRejected($o['id'], 'Target not found');
                    }
                }
            }

            
            // Guardar reporte del turno para este reino
            $this->db->insert('realm_reports', [
                'realm_id'=>$realm['id'],
                'tick'=>$tick,
                'report'=>implode("\n", $reportLines),
                'created_at'=>time()
            ]);

            // Persist final state for attacker realm
            $this->Realm_model->saveState((int)$realm['id'], $state);
        }

        // Finalizar turno
        $this->db->insert('turns', ['tick'=>$tick, 'resolved_at'=>time(), 'notes'=>'OK']);
        echo "Turno $tick resuelto.\n";
    }

    private function getNextTick(): int {
        $q = $this->db->select_max('tick','t')->get('turns')->row_array();
        return (int)($q['t'] ?? 0) + 1;
    }

    private function markApplied(int $orderId): void {
        $this->db->where('id',$orderId)->update('orders', ['status'=>'applied']);
    }
    private function markRejected(int $orderId, string $reason): void {
        $this->db->where('id',$orderId)->update('orders', ['status'=>'rejected']);
    }
}
