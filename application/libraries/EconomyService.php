<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EconomyService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('economy');
        $this->CI->load->library(['Caching','Wallet','Observability']);
    }

    private function param($key, $default=0) {
        $row = $this->CI->db->get_where('econ_params',['key'=>$key])->row_array();
        if (!$row) return $default;
        $v = json_decode($row['value'], true);
        return ($v === null) ? $row['value'] : $v;
    }

    public function setParam($key, $value): void {
        $this->CI->db->replace('econ_params',['key'=>$key,'value'=>json_encode($value),'updated_at'=>time()]);
    }

    private function activeModifiers(?int $realmId): array {
        $now = time();
        $mods = $this->CI->db->get_where('econ_modifiers', ['realm_id'=>null])->result_array();
        if ($realmId) {
            $mods = array_merge($mods, $this->CI->db->get_where('econ_modifiers', ['realm_id'=>$realmId])->result_array());
        }
        // filtrar expirados
        $mods = array_values(array_filter($mods, function($m) use($now){ return empty($m['expires_at']) || (int)$m['expires_at'] >= $now; }));
        $out = ['gold_mul'=>0,'mana_mul'=>0,'research_mul'=>0,'gold_add'=>0,'mana_add'=>0,'research_add'=>0];
        foreach ($mods as $m) {
            $k = $m['key']; $v = (float)$m['value'];
            if (!isset($out[$k])) $out[$k]=0;
            $out[$k] += $v;
        }
        return $out;
    }

    private function realmSnapshot(int $realmId): array {
        // Datos agregados para cálculo
        $b = $this->CI->db->select('COUNT(*) as cnt, SUM(level) as lvl')->get_where('buildings',['realm_id'=>$realmId])->row_array();
        $r = $this->CI->db->select('COUNT(*) as cnt, SUM(level) as lvl')->get_where('research_levels',['realm_id'=>$realmId])->row_array();
        $units = 0;
        if ($this->CI->db->table_exists('units')) {
            $u = $this->CI->db->select('SUM(quantity) as q')->get_where('units',['realm_id'=>$realmId])->row_array();
            $units = (int)($u['q'] ?? 0);
        }
        // ranking aproximado por suma de niveles (si no hay ladder)
        $sumRef = $this->CI->db->select('SUM(level) as lvl')->group_by('realm_id')->get('buildings')->result_array();
        $arr = array_map(function($x){ return (int)$x['lvl']; }, $sumRef);
        rsort($arr);
        $self = (int)($b['lvl'] ?? 0);
        $percentile = 0.5;
        if ($arr) {
            $pos = 0;
            foreach ($arr as $i=>$v) { if ($self >= $v) { $pos = $i; break; } }
            $percentile = ($pos+1) / count($arr);
        }
        return [
            'buildings_count'=>(int)($b['cnt'] ?? 0),
            'buildings_level_sum'=>(int)($b['lvl'] ?? 0),
            'research_count'=>(int)($r['cnt'] ?? 0),
            'research_level_sum'=>(int)($r['lvl'] ?? 0),
            'units_total'=>$units,
            'percentile'=>$percentile,
        ];
    }

    private function curveOut(float $x): float {
        $k = (float)$this->param('curve.k', 0.15);
        $maxMul = (float)$this->param('curve.max_mul', 6.0);
        // x ~ poder normalizado: sum(levels)/100
        $out = $maxMul * (1 - exp(-$k * $x));
        return max(0.0, $out);
    }

    private function snowballFactor(float $percentile): float {
        $topP = (float)$this->param('snowball.top_percent', 0.1);
        $topPen = (float)$this->param('snowball.top_penalty', -0.15);
        $botP = (float)$this->param('snowball.bottom_percent', 0.25);
        $botBon = (float)$this->param('snowball.bottom_bonus', 0.15);
        $f = 0.0;
        if ($percentile <= $botP) $f += $botBon;
        if ($percentile >= (1.0 - $topP)) $f += $topPen;
        return $f; // multiplicativo: (1+f)
    }

    private function perTickCaps(): array {
        return [
            'gold'=>(int)$this->param('cap.per_tick.gold', 5000),
            'mana'=>(int)$this->param('cap.per_tick.mana', 4000),
            'research'=>(int)$this->param('cap.per_tick.research', 2500),
        ];
    }

    public function preview(int $realmId): array {
        $snap = $this->realmSnapshot($realmId);
        $x = ($snap['buildings_level_sum'] + $snap['research_level_sum']*0.5) / 100.0; // normalización
        $curveMul = $this->curveOut($x);
        $snow = $this->snowballFactor((float)$snap['percentile']);
        $mods = $this->activeModifiers($realmId);
        $base = [
            'gold'=>(int)$this->param('base.gold',50),
            'mana'=>(int)$this->param('base.mana',30),
            'research'=>(int)$this->param('base.research',20),
        ];
        $gross = [
            'gold'    => (int)round($base['gold'] * $curveMul),
            'mana'    => (int)round($base['mana'] * $curveMul),
            'research'=> (int)round($base['research'] * $curveMul),
        ];
        // upkeep
        $up = (int)$this->param('upkeep.unit', 1) * (int)$snap['units_total'] +
              (int)$this->param('upkeep.building', 0) * (int)$snap['buildings_level_sum'];
        $upkeep = ['gold'=>$up, 'mana'=>0, 'research'=>0];

        // aplicar modificadores multiplicativos (sumados) y aditivos
        $mul = [
            'gold'    => 1.0 + (float)($mods['gold_mul'] ?? 0) + $snow,
            'mana'    => 1.0 + (float)($mods['mana_mul'] ?? 0) + $snow,
            'research'=> 1.0 + (float)($mods['research_mul'] ?? 0) + $snow,
        ];
        $add = [
            'gold'    => (int)round($mods['gold_add'] ?? 0),
            'mana'    => (int)round($mods['mana_add'] ?? 0),
            'research'=> (int)round($mods['research_add'] ?? 0),
        ];

        $caps = $this->perTickCaps();
        $net = [];
        $modsOut = [];
        foreach (['gold','mana','research'] as $res) {
            $v = (int)round($gross[$res] * $mul[$res]) + $add[$res];
            $mDetail = ['mul'=>$mul[$res],'add'=>$add[$res],'snow'=>$snow];
            if ($res==='gold') $v -= (int)$upkeep['gold']; // upkeep se resta del oro
            $v = max(0, min($v, $caps[$res]));
            $modsOut[$res] = $mDetail;
            $net[$res] = $v;
        }

        return [
            'snapshot'=>$snap,
            'base'=>$base,
            'curve_mul'=>$curveMul,
            'snowball'=>$snow,
            'gross'=>$gross,
            'upkeep'=>$upkeep,
            'caps'=>$caps,
            'net'=>$net,
            'mods'=>$modsOut,
        ];
    }

    public function tick(int $realmId): array {
        $p = $this->preview($realmId);
        $now = time();
        foreach (['gold','mana','research'] as $res) {
            $gross = (int)$p['gross'][$res];
            $upk = (int)($p['upkeep'][$res] ?? 0);
            $net = (int)$p['net'][$res];
            $mods = (int)round($net - max(0,$gross - $upk)); // aproximación
            if ($net <= 0) $mods = 0;
            // aplicar a wallet
            if ($net > 0) $this->CI->wallet->add($realmId, $res, $net, 'econ_tick', 'engine', null);
            // registrar historia
            $this->CI->db->insert('economy_history',[
                'realm_id'=>$realmId,'resource'=>$res,'gross'=>$gross,'upkeep'=>$upk,'modifiers'=>$mods,'net'=>$net,
                'snapshot'=>json_encode($p, JSON_UNESCAPED_UNICODE),'created_at'=>$now
            ]);
        }
        $this->CI->observability->inc('economy.tick', ['realm_id'=>$realmId], 1);
        return $p;
    }

    public function tickAll(int $limit=200): int {
        $rs = $this->CI->db->limit($limit)->get('realms')->result_array();
        foreach ($rs as $r) { $this->tick((int)$r['id']); }
        return count($rs);
    }
}
