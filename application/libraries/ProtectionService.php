<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ProtectionService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('game');
    }

    public function has($realmId, string $type): ?array {
        $now = time();
        $row = $this->CI->db->where('realm_id',(int)$realmId)->where('type',$type)
            ->where('(expires_at IS NULL OR expires_at >= '.$now.')', null, false)
            ->order_by('id','DESC')->limit(1)->get('protections')->row_array();
        return $row ?: null;
    }

    public function grant($realmId, string $type, ?int $expiresAt=null, array $data=[]): int {
        $this->CI->db->insert('protections',[
            'realm_id'=>(int)$realmId,'type'=>$type,'expires_at'=>$expiresAt,'data'=>json_encode($data, JSON_UNESCAPED_UNICODE),'created_at'=>time()
        ]);
        return (int)$this->CI->db->insert_id();
    }

    // Damage protection: si pierde > X% NP en 24h, asignar protección (ej. 12h)
    public function evaluateDamageProtection($realmId, int $lostPercent24h, int $hours=12) {
        $cfg = $this->CI->config->item('game')['protections'] ?? [];
        $thr = (int)($cfg['damage_threshold_percent_24h'] ?? 30);
        if ($lostPercent24h >= $thr) {
            $this->grant($realmId, 'damage', time()+$hours*3600, ['lost_percent'=>$lostPercent24h]);
            return true;
        }
        return false;
    }

    // Contadores 24h p.ej. Pillage/Volcano
    public function incCounter($realmId, string $type, int $maxPer24h): array {
        $now = time(); $window = $now - 24*3600;
        $row = $this->CI->db->get_where('action_counters',['realm_id'=>$realmId,'type'=>$type])->row_array();
        if (!$row) {
            $this->CI->db->insert('action_counters',['realm_id'=>$realmId,'type'=>$type,'window_start'=>$now,'count'=>1,'updated_at'=>$now]);
            return [true,1];
        }
        // reset si ventana vencida
        if ((int)$row['window_start'] < $window) {
            $this->CI->db->update('action_counters',['window_start'=>$now,'count'=>1,'updated_at'=>$now],['id'=>$row['id']]);
            return [true,1];
        }
        $count = (int)$row['count'] + 1;
        if ($count > $maxPer24h) return [false, $row['count']];
        $this->CI->db->update('action_counters',['count'=>$count,'updated_at'=>$now],['id'=>$row['id']]);
        return [true, $count];
    }
}
