<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BattleService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->library(['PreBattleService','Engine','BattlePolicy','BattleResults','LootService','WarService']);
        $this->CI->load->config('game');
    }

    public function start($attRealmId, $defRealmId, $type='regular', $seed=null, $isCounter=false) {
        $seed = $seed ?: bin2hex(random_bytes(16));
        $key = hash('sha256', $attRealmId.'-'.$defRealmId.'-'.microtime(true).'-'.$seed);
        $rowA = $this->CI->db->select('id,net_power,alliance_id')->get_where('realms',['id'=>(int)$attRealmId])->row_array();
        $rowD = $this->CI->db->select('id,net_power,alliance_id')->get_where('realms',['id'=>(int)$defRealmId])->row_array();
        $this->CI->db->insert('battles',[
            'battle_key'=>$key,'seed'=>$seed,'type'=>$type,'is_counter'=>$isCounter?1:0,
            'attacker_realm_id'=>(int)$attRealmId,'defender_realm_id'=>(int)$defRealmId,
            'attacker_alliance_id'=>(int)($rowA['alliance_id'] ?? 0) ?: null,
            'defender_alliance_id'=>(int)($rowD['alliance_id'] ?? 0) ?: null,
            'attacker_np_before'=>(int)($rowA['net_power'] ?? 0),'defender_np_before'=>(int)($rowD['net_power'] ?? 0),
            'created_at'=>time()
        ]);
        return (int)$this->CI->db->insert_id();
    }

    public function finalize(array $payload) {
        // payload: battle_id?, type, attacker{realm_id,np,stacks}, defender{realm_id,np,stacks}, is_counter, prebattle{...}
        $type = $payload['type'] ?? 'regular';
        $att  = $payload['attacker'] ?? []; $def = $payload['defender'] ?? [];
        $isCounter = !empty($payload['is_counter']);
        $battleId = null;

        if (!empty($payload['battle_id'])) $battleId = (int)$payload['battle_id'];
        if (!$battleId && !empty($att['realm_id']) && !empty($def['realm_id'])) {
            $battleId = $this->start((int)$att['realm_id'], (int)$def['realm_id'], $type, null, $isCounter);
        }

        // Pre-battle (opcional si llega contexto de resistencias)
        $pre = null;
        if (!empty($payload['prebattle'])) {
            $ctx = $payload['prebattle'];
            $ctx['battle_id'] = $battleId ?: ($payload['battle_id'] ?? time());
            $pre = $this->CI->prebattleservice->resolve($ctx);
        }

        // Battle phase
        $atkOrd = $this->CI->engine->stack_order($att['stacks'] ?? []);
        $defOrd = $this->CI->engine->stack_order($def['stacks'] ?? []);
        $pairs  = $this->CI->engine->pairing($atkOrd, $defOrd);
        $phase  = $this->CI->engine->damage_phase($atkOrd, $defOrd, $pairs);

        $attLoss = (int)$phase['damage_to_atk'];
        $defLoss = (int)$phase['damage_to_def'];

        // Win/lose heurística: mayor daño infligido gana (empate -> atacante pierde)
        $attackerWin = ($defLoss > $attLoss);

        // Registrar pérdidas y protecciones
        if (!empty($att['realm_id']) && !empty($def['realm_id'])) {
            $this->CI->battleresults->applyAndLog($battleId, (int)$att['realm_id'], (int)$def['realm_id'], $attLoss, $defLoss);
        }

        // Loot
        $loot = $this->CI->lootservice->computeLoot([
            'type'=>$type, 'is_counter'=>$isCounter,
            'attacker_np'=>(int)($att['np'] ?? 0),'defender_np'=>(int)($def['np'] ?? 0),
            'attacker_realm_id'=>(int)($att['realm_id'] ?? 0),'defender_realm_id'=>(int)($def['realm_id'] ?? 0),
            'np_losses'=>['att'=>$attLoss,'def'=>$defLoss],
            'attacker_win'=>$attackerWin
        ]);
        // Aplicar loot
        if (!empty($att['realm_id']) && !empty($def['realm_id'])) {
            $this->CI->lootservice->applyLoot((int)$att['realm_id'], (int)$def['realm_id'], $loot);
        }

        // Persistir batalla
        if ($battleId) {
            $report = json_encode([
                'prebattle'=>$pre,'pairs'=>$pairs,'phase'=>$phase,'np_losses'=>['attacker'=>$attLoss,'defender'=>$defLoss],'loot'=>$loot,'attacker_win'=>$attackerWin
            ], JSON_UNESCAPED_UNICODE);
            $upd = [
                'attacker_np_loss'=>$attLoss,'defender_np_loss'=>$defLoss,
                'attacker_win'=>$attackerWin?1:0,'loot_gold'=>$loot['gold'],'loot_mana'=>$loot['mana'],'land_taken'=>$loot['land'],
                'report'=>$report,'resolved_at'=>time()
            ];
            $this->CI->db->update('battles', $upd, ['id'=>$battleId]);
        }

        // Guerras (si alianzas en guerra)
        if ($battleId) {
            $brow = $this->CI->db->get_where('battles',['id'=>$battleId])->row_array();
            if ($brow && ($brow['attacker_alliance_id'] || $brow['defender_alliance_id'])) {
                $this->CI->warservice->recordBattle([
                    'id'=>$battleId,
                    'attacker_alliance_id'=>$brow['attacker_alliance_id'],
                    'defender_alliance_id'=>$brow['defender_alliance_id'],
                    'attacker_np_loss'=>$attLoss,'defender_np_loss'=>$defLoss,
                    'land_taken'=>$loot['land'],'attacker_win'=>$attackerWin
                ]);
            }
        }

        return [
            'battle_id'=>$battleId,
            'prebattle'=>$pre,'pairs'=>$pairs,'phase'=>$phase,
            'np_losses'=>['attacker'=>$attLoss,'defender'=>$defLoss],
            'loot'=>$loot,'attacker_win'=>$attackerWin
        ];
    }
}
