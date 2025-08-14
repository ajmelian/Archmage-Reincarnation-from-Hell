<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ResearchService {
    private array $cfg;
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('research');
        $this->CI->load->library('Caching');
        $this->CI->load->library('Wallet');
        $this->cfg = $this->CI->config->item('research') ?? [];
    }

    public function listDefs(): array {
        return $this->CI->db->order_by('category','ASC')->order_by('id','ASC')->get('research_def')->result_array();
    }

    public function level(int $realmId, string $rid): int {
        $row = $this->CI->db->get_where('research_levels',['realm_id'=>$realmId,'research_id'=>$rid])->row_array();
        $lvl = (int)($row['level'] ?? 0);
        // consider queued target
        $q = $this->CI->db->select_max('level_target','lt')->get_where('research_queue',['realm_id'=>$realmId,'research_id'=>$rid])->row_array();
        $queuedMax = (int)($q['lt'] ?? 0);
        return max($lvl, $queuedMax);
    }

    public function getDef(string $rid): ?array {
        $d = $this->CI->db->get_where('research_def',['id'=>$rid])->row_array();
        return $d ?: null;
    }

    public function queueList(int $realmId): array {
        return $this->CI->db->order_by('finish_at','ASC')->get_where('research_queue',['realm_id'=>$realmId])->result_array();
    }

    /** Calcula coste y tiempo para subir desde el nivel actual (incl. colas) hasta targetLevel. */
    public function quote(int $realmId, string $rid, int $targetLevel): array {
        $def = $this->getDef($rid);
        if (!$def) throw new Exception('Research not found');
        $current = $this->level($realmId, $rid);
        $targetLevel = max($current+1, min((int)$def['max_level'], $targetLevel));
        // prereqs
        $pre = json_decode($def['prereqs'] ?? '[]', true) ?: [];
        foreach ($pre as $prId=>$reqLv) {
            $prCur = $this->level($realmId, $prId);
            if ($prCur < (int)$reqLv) throw new Exception('Prerequisite not met: '.$prId.' >= '.$reqLv);
        }

        $g = (float)$def['growth_rate'];
        $research=0; $gold=0; $mana=0; $sec=0;
        for ($lvl=$current; $lvl<$targetLevel; $lvl++) {
            $mult = pow($g, $lvl);
            $research += (int)round($def['base_cost_research'] * $mult);
            $gold     += (int)round($def['base_cost_gold'] * $mult);
            $mana     += (int)round($def['base_cost_mana'] * $mult);
            $sec      += (int)round($def['time_sec'] * $mult);
        }
        $finish = time() + $sec;
        return ['research'=>$research,'gold'=>$gold,'mana'=>$mana,'seconds'=>$sec,'finish_at'=>$finish,'current'=>$current,'target'=>$targetLevel];
    }

    public function queue(int $realmId, string $rid, int $targetLevel): int {
        $q = $this->quote($realmId, $rid, $targetLevel);
        if ($q['research']>0) $this->CI->wallet->spend($realmId, 'research', $q['research'], 'research_queue', 'research', null);
        if ($q['gold']>0)     $this->CI->wallet->spend($realmId, 'gold', $q['gold'], 'research_queue', 'research', null);
        if ($q['mana']>0)     $this->CI->wallet->spend($realmId, 'mana', $q['mana'], 'research_queue', 'research', null);
        $this->CI->db->insert('research_queue',[
            'realm_id'=>$realmId,'research_id'=>$rid,'level_target'=>$q['target'],
            'finish_at'=>$q['finish_at'],'created_at'=>time()
        ]);
        $id = (int)$this->CI->db->insert_id();
        $this->log($realmId,'queue',$rid,$q['target'],['cost_research'=>$q['research'],'cost_gold'=>$q['gold'],'cost_mana'=>$q['mana'],'finish_at'=>$q['finish_at']]);
        return $id;
    }

    public function cancel(int $realmId, int $queueId): void {
        $row = $this->CI->db->get_where('research_queue',['id'=>$queueId])->row_array();
        if (!$row || (int)$row['realm_id']!==$realmId) throw new Exception('Not your queue item');
        if ($row['finish_at'] <= time()) throw new Exception('Already finished');
        $def = $this->getDef($row['research_id']);
        $quote = $this->quote($realmId, $row['research_id'], (int)$row['level_target']);
        $rate = (float)($this->cfg['queue_cancel_refund'] ?? 1.0);
        $r = (int)round($quote['research'] * $rate);
        $g = (int)round($quote['gold'] * $rate);
        $m = (int)round($quote['mana'] * $rate);
        if ($r>0) $this->CI->wallet->add($realmId, 'research', $r, 'research_cancel_refund', 'research', $queueId);
        if ($g>0) $this->CI->wallet->add($realmId, 'gold', $g, 'research_cancel_refund', 'research', $queueId);
        if ($m>0) $this->CI->wallet->add($realmId, 'mana', $m, 'research_cancel_refund', 'research', $queueId);
        $this->CI->db->delete('research_queue',['id'=>$queueId]);
        $this->log($realmId,'cancel',$row['research_id'],(int)$row['level_target'],['refund_research'=>$r,'refund_gold'=>$g,'refund_mana'=>$m]);
    }

    public function markFinished(int $realmId, string $rid, int $levelTarget): void {
        $this->log($realmId,'finish',$rid,$levelTarget,[]);
    }

    private function log(int $realmId, string $type, string $rid, int $lt, array $payload): void {
        $this->CI->db->insert('research_logs',[
            'realm_id'=>$realmId,'type'=>$type,'research_id'=>$rid,'level_target'=>$lt,
            'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE),'created_at'=>time()
        ]);
    }
}
