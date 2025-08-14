<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TickRunner {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('tick');
        $this->CI->load->library(['Wallet','Inventory','Formulas/EconomyFormula','TalentTree']);
    }

    public function runOne(): array {
        $state = $this->CI->db->get('tick_state')->row_array();
        $now = time();
        // lock naive
        if (($state['locked_at'] ?? 0) > 0 && ($now - (int)$state['locked_at']) < 300) {
            return ['ok'=>false,'msg'=>'locked'];
        }
        $this->CI->db->update('tick_state', ['locked_at'=>$now], ['id'=>$state['id']]);

        $processed = 0; $effectsCleared = 0; $queuesDone = 0;
        try {
            // batch realms
            $realms = $this->CI->db->select('id')->order_by('id','ASC')->limit((int)$this->CI->config->item('tick')['batch_size'])->get('realms')->result_array();
            foreach ($realms as $r) {
                $rid = (int)$r['id'];
                $this->produce($rid);
                $queuesDone += $this->processQueues($rid, $now);
                $effectsCleared += $this->clearEffects($rid, $now);
                $processed++;
            }
            $this->CI->db->update('tick_state', ['tick_no'=>$state['tick_no']+1,'last_tick_at'=>$now,'locked_at'=>0], ['id'=>$state['id']]);
            return ['ok'=>true,'processed'=>$processed,'queues'=>$queuesDone,'effects_cleared'=>$effectsCleared];
        } catch (Throwable $e) {
            $this->CI->db->update('tick_state', ['locked_at'=>0], ['id'=>$state['id']]);
            return ['ok'=>false,'error'=>$e->getMessage()];
        }
    }

    private function produce(int $realmId): void {
        // construir estado de EconomyFormula
        $b = $this->buildingsState($realmId);
        $bon = $this->talentBonuses($realmId);
        $state = ['buildings'=>$b, 'bonuses'=>['gold'=>$bon['gold'] ?? [], 'mana'=>$bon['mana'] ?? [], 'research'=>$bon['research'] ?? []]];
        $out = $this->CI->economyformula->produce($state);
        if ($out['gold']>0) $this->CI->wallet->add($realmId, 'gold', $out['gold'], 'tick_production', 'tick', null);
        if ($out['mana']>0) $this->CI->wallet->add($realmId, 'mana', $out['mana'], 'tick_production', 'tick', null);
        if ($out['research']>0) $this->CI->wallet->add($realmId, 'research', $out['research'], 'tick_production', 'tick', null);

        // upkeep sencillo si hay tablas (opcional)
        if ($this->CI->db->table_exists('armies')) {
            $units = $this->CI->db->select_sum('qty')->get_where('armies',['realm_id'=>$realmId])->row_array();
            $count = (int)($units['qty'] ?? 0);
            $cfg = $this->CI->config->item('tick');
            $goldCost = (int)round($count * (float)($cfg['upkeep']['gold_per_unit'] ?? 0));
            $manaCost = (int)round($count * (float)($cfg['upkeep']['mana_per_unit'] ?? 0));
            if ($goldCost>0) { try { $this->CI->wallet->spend($realmId, 'gold', $goldCost, 'tick_upkeep', 'tick', null); } catch (Throwable $e) {} }
            if ($manaCost>0) { try { $this->CI->wallet->spend($realmId, 'mana', $manaCost, 'tick_upkeep', 'tick', null); } catch (Throwable $e) {} }
        }
    }

    private function buildingsState(int $realmId): array {
        $st = [];
        if ($this->CI->db->table_exists('buildings')) {
            $rows = $this->CI->db->get_where('buildings', ['realm_id'=>$realmId])->result_array();
            foreach ($rows as $r) {
                if (isset($r['qty']) && $r['qty']>0) $st[$r['building_id']] = (int)$r['qty'];
            }
        }
        return $st;
    }

    private function talentBonuses(int $realmId): array {
        if (!$this->CI->db->table_exists('hero_talents') || !$this->CI->db->table_exists('realm_heroes')) return [];
        $rows = $this->CI->db->get_where('realm_heroes', ['realm_id'=>$realmId])->result_array();
        $bon = [];
        foreach ($rows as $h) {
            $tal = $this->CI->talenttree->heroTalents($realmId, $h['hero_id']);
            $agg = $this->CI->talenttree->aggregateBonuses($tal);
            foreach ($agg as $k=>$vals) {
                if (!isset($bon[$k])) $bon[$k]=[];
                foreach ($vals as $v) $bon[$k][] = $v;
            }
        }
        // map keys to EconomyFormula expectations
        return [
            'gold' => $bon['gold_bonus'] ?? [],
            'mana' => $bon['mana_bonus'] ?? [],
            'research' => $bon['research_bonus'] ?? [],
        ];
    }

    private function processQueues(int $rid, int $now): int {
        $done = 0;
        // buildings
        if ($this->CI->db->table_exists('building_queue')) {
            $q = $this->CI->db->order_by('finish_at','ASC')->get_where('building_queue', ['realm_id'=>$rid])->result_array();
            foreach ($q as $row) {
                if ($row['finish_at'] <= $now) {
                    $this->CI->db->trans_start();
                    // apply
                    $cur = $this->CI->db->get_where('buildings', ['realm_id'=>$rid,'building_id'=>$row['building_id']])->row_array();
                    if ($cur) {
                        $this->CI->db->set('qty', 'qty+'.(int)$row['qty'], FALSE)->set('updated_at', time())->where(['realm_id'=>$rid,'building_id'=>$row['building_id']])->update('buildings');
                    } else {
                        $this->CI->db->insert('buildings', ['realm_id'=>$rid,'building_id'=>$row['building_id'],'qty'=>(int)$row['qty'],'level'=>0,'updated_at'=>time()]);
                    }
                    $this->CI->db->delete('building_queue', ['id'=>$row['id']]);
                    $this->CI->db->trans_complete();
                    $done++;
                }
            }
        }
        // research
        if ($this->CI->db->table_exists('research_queue')) {
            $q = $this->CI->db->order_by('finish_at','ASC')->get_where('research_queue', ['realm_id'=>$rid])->result_array();
            foreach ($q as $row) {
                if ($row['finish_at'] <= $now) {
                    $this->CI->db->trans_start();
                    $cur = $this->CI->db->get_where('research_levels', ['realm_id'=>$rid,'research_id'=>$row['research_id']])->row_array();
                    if ($cur) {
                        $this->CI->db->set('level', 'GREATEST(level,'.(int)$row['level_target'].')', FALSE)->set('updated_at', time())->where(['realm_id'=>$rid,'research_id'=>$row['research_id']])->update('research_levels');
                    } else {
                        $this->CI->db->insert('research_levels', ['realm_id'=>$rid,'research_id'=>$row['research_id'],'level'=>(int)$row['level_target'],'updated_at'=>time()]);
                    }
                    $this->CI->db->delete('research_queue', ['id'=>$row['id']]);
                    $this->CI->db->trans_complete();
                    $done++;
                }
            }
        }
        return $done;
    }

    private function clearEffects(int $rid, int $now): int {
        if (!$this->CI->db->table_exists('active_effects')) return 0;
        $this->CI->db->where('realm_id',$rid)->where('expires_at <', $now)->delete('active_effects');
        return $this->CI->db->affected_rows();
    }
}
