<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BuildingService {
    private array $cfg;
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('buildings');
        $this->CI->load->library('Wallet');
        $this->cfg = $this->CI->config->item('buildings') ?? [];
    }

    public function getDef(string $id): ?array {
        $d = $this->CI->db->get_where('building_def',['id'=>$id])->row_array();
        return $d ?: null;
    }

    public function listDefs(): array {
        return $this->CI->db->order_by('category','ASC')->order_by('id','ASC')->get('building_def')->result_array();
    }

    public function owned(int $realmId): array {
        if (!$this->CI->db->table_exists('buildings')) return [];
        return $this->CI->db->get_where('buildings',['realm_id'=>$realmId])->result_array();
    }

    public function queueList(int $realmId): array {
        return $this->CI->db->order_by('finish_at','ASC')->get_where('building_queue',['realm_id'=>$realmId])->result_array();
    }

    /** Calcula coste y tiempo total para construir qty unidades, con crecimiento geométrico */
    public function quote(int $realmId, string $buildingId, int $qty): array {
        $def = $this->getDef($buildingId);
        if (!$def) throw new Exception('Building not found');
        $qty = max(1,$qty);
        // current qty (built + queued)
        $built = $this->CI->db->select_sum('qty')->get_where('buildings',['realm_id'=>$realmId,'building_id'=>$buildingId])->row_array();
        $queued= $this->CI->db->select_sum('qty')->get_where('building_queue',['realm_id'=>$realmId,'building_id'=>$buildingId])->row_array();
        $cur = (int)($built['qty'] ?? 0) + (int)($queued['qty'] ?? 0);

        $g = (float)$def['growth_rate'];
        $gold=0; $mana=0; $sec=0;
        for ($i=0;$i<$qty;$i++) {
            $mult = pow($g, $cur + $i);
            $gold += (int)round($def['base_cost_gold'] * $mult);
            $mana += (int)round($def['base_cost_mana'] * $mult);
            $sec  += (int)round($def['build_time_sec'] * $mult);
        }
        $finish = time() + $sec;
        return ['gold'=>$gold,'mana'=>$mana,'seconds'=>$sec,'finish_at'=>$finish, 'current'=>$cur];
    }

    public function queue(int $realmId, string $buildingId, int $qty): int {
        $q = $this->quote($realmId, $buildingId, $qty);
        // Pay
        if ($q['gold']>0) $this->CI->wallet->spend($realmId, 'gold', $q['gold'], 'build_queue', 'building', null);
        if ($q['mana']>0) $this->CI->wallet->spend($realmId, 'mana', $q['mana'], 'build_queue', 'building', null);
        $now = time();
        $this->CI->db->insert('building_queue',[
            'realm_id'=>$realmId,'building_id'=>$buildingId,'qty'=>$qty,
            'finish_at'=>$q['finish_at'],'created_at'=>$now
        ]);
        $id = (int)$this->CI->db->insert_id();
        $this->log($realmId,'queue',$buildingId,$qty,['cost_gold'=>$q['gold'],'cost_mana'=>$q['mana'],'finish_at'=>$q['finish_at']]);
        return $id;
    }

    public function cancel(int $realmId, int $queueId): void {
        $row = $this->CI->db->get_where('building_queue',['id'=>$queueId])->row_array();
        if (!$row || (int)$row['realm_id']!==$realmId) throw new Exception('Not your queue item');
        if ($row['finish_at'] <= time()) throw new Exception('Already finished');
        // refund
        $def = $this->getDef($row['building_id']);
        $estimate = $this->quote($realmId, $row['building_id'], (int)$row['qty']);
        $rate = (float)($this->cfg['queue_cancel_refund'] ?? 1.0);
        $gold = (int)round($estimate['gold'] * $rate);
        $mana = (int)round($estimate['mana'] * $rate);
        if ($gold>0) $this->CI->wallet->add($realmId, 'gold', $gold, 'build_cancel_refund', 'building', $queueId);
        if ($mana>0) $this->CI->wallet->add($realmId, 'mana', $mana, 'build_cancel_refund', 'building', $queueId);
        $this->CI->db->delete('building_queue',['id'=>$queueId]);
        $this->log($realmId,'cancel',$row['building_id'],(int)$row['qty'],['refund_gold'=>$gold,'refund_mana'=>$mana]);
    }

    public function demolish(int $realmId, string $buildingId, int $qty): void {
        $b = $this->CI->db->get_where('buildings',['realm_id'=>$realmId,'building_id'=>$buildingId])->row_array();
        $qty = max(1, $qty);
        if (!$b || (int)$b['qty'] < $qty) throw new Exception('Not enough to demolish');
        $this->CI->db->set('qty','qty-'.$qty,FALSE)->set('updated_at', time())->where(['realm_id'=>$realmId,'building_id'=>$buildingId])->update('buildings');
        // refund partial
        $quote = $this->quote($realmId, $buildingId, $qty);
        $rate = (float)($this->cfg['demolish_refund_rate'] ?? 0.0);
        $gold = (int)round($quote['gold'] * $rate);
        $mana = (int)round($quote['mana'] * $rate);
        if ($gold>0) $this->CI->wallet->add($realmId, 'gold', $gold, 'demolish_refund', 'building', null);
        if ($mana>0) $this->CI->wallet->add($realmId, 'mana', $mana, 'demolish_refund', 'building', null);
        $this->log($realmId,'demolish',$buildingId,$qty,['refund_gold'=>$gold,'refund_mana'=>$mana]);
    }

    public function markFinished(int $realmId, string $buildingId, int $qty): void {
        // usado desde TickRunner al aplicar una cola (si quieres log)
        $this->log($realmId,'finish',$buildingId,$qty,[]);
    }

    private function log(int $realmId, string $type, string $bid, int $qty, array $payload): void {
        $this->CI->db->insert('building_logs',[
            'realm_id'=>$realmId,'type'=>$type,'building_id'=>$bid,'qty'=>$qty,
            'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE),'created_at'=>time()
        ]);
    }
}
