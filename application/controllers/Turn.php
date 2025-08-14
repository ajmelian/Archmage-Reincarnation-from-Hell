<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Turn extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library(['Engine','EngineExt']);
        $this->load->model(['Realm_model','Unit_model','Building_model','Research_model']);
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
            $state = $this->Realm_model->loadState($realm);

            // Producción base por edificios
            EngineExt::produce($state, $buildingDefs);

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
                }
            }

            // Segundo: research (gastar puntos existentes)
            foreach ($list as $o) {
                $payload = json_decode($o['payload'], true) ?: [];
                if (($payload['type'] ?? '') === 'research') {
                    $techId = (string)($payload['techId'] ?? '');
                    $log = EngineExt::research($state, $researchDefs, $techId);
                    $this->markApplied($o['id']);
                }
            }

            // Tercero: recruit
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
                        } else {
                            $this->markRejected($o['id'], 'Not enough gold');
                        }
                    } else {
                        $this->markRejected($o['id'], 'Unknown unit or qty');
                    }
                }
            }

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
                    } else {
                        $this->markRejected($o['id'], 'Target not found');
                    }
                }
            }

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
