<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TalentTree {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('talents');
    }

    /** Devuelve los talentos de un héroe con su rango actual */
    public function heroTalents(int $realmId, string $heroId): array {
        $rows = $this->CI->db->order_by('talent_id','ASC')->get_where('hero_talents', [
            'realm_id'=>$realmId, 'hero_id'=>$heroId
        ])->result_array();
        $out = [];
        foreach ($rows as $r) {
            $def = $this->CI->db->get_where('talent_def', ['id'=>$r['talent_id']])->row_array();
            if (!$def) continue;
            $out[] = [
                'id'=>$r['talent_id'],
                'rank'=>(int)$r['rank'],
                'effects'=>json_decode($def['effects'] ?? '[]', true) ?: []
            ];
        }
        return $out;
    }

    /** Efectos por equipo (items equipados & sets alcanzados) */
    public function equipmentBonuses(int $realmId): array {
        if (!$this->CI->db->table_exists('equipment')) return [];
        $rows = $this->CI->db->get_where('equipment', ['realm_id'=>$realmId])->result_array();
        $bySet = [];
        $bon = [];
        foreach ($rows as $e) {
            $it = $this->CI->db->get_where('item_def', ['id'=>$e['item_id']])->row_array();
            $setId = $it['set_id'] ?? $e['set_id'] ?? null;
            if ($setId) {
                if (!isset($bySet[$setId])) $bySet[$setId]=0;
                $bySet[$setId]++;
            }
            // item bonuses directos (si existieran en item_def)
            if (!empty($it['bonuses'])) {
                $b = json_decode($it['bonuses'], true) ?: [];
                foreach ($b as $k=>$v) {
                    if (!isset($bon[$k])) $bon[$k]=[];
                    $bon[$k][] = (float)$v;
                }
            }
        }
        // aplicar set bonuses por umbrales
        foreach ($bySet as $sid=>$count) {
            $set = $this->CI->db->get_where('item_set_def',['id'=>$sid])->row_array();
            if ($set && !empty($set['bonuses'])) {
                $steps = json_decode($set['bonuses'], true) ?: [];
                foreach ($steps as $need=>$effects) {
                    if ($count >= (int)$need) {
                        foreach ($effects as $k=>$v) {
                            if (!isset($bon[$k])) $bon[$k]=[];
                            $bon[$k][] = (float)$v;
                        }
                    }
                }
            }
        }
        return $bon;
    }

    /** Agrega efectos de talentos en bruto a un mapa k=>[values...] */
    public function aggregateBonuses(array $talents): array {
        $out = [];
        foreach ($talents as $t) {
            $rank = (int)($t['rank'] ?? 0);
            $effects = $t['effects'] ?? [];
            foreach ($effects as $k=>$v) {
                // soporte tanto constantes como por-rango (array/lista o multiplicador)
                $val = is_array($v) ? ($v[$rank] ?? end($v)) : ($v * $rank);
                if (!isset($out[$k])) $out[$k]=[];
                $out[$k][] = (float)$val;
            }
        }
        return $out;
    }

    /** Compila y persiste bonuses por reino, separados por scope economy/combat */
    public function compileRealm(int $realmId): array {
        $cfgStack = $this->CI->config->item('talents') ?? [];
        $stacking = $cfgStack['stacking'] ?? [];
        $caps = $cfgStack['caps'] ?? [];
        $flatCaps = $cfgStack['flat_caps'] ?? [];

        // Talentos de todos los héroes del reino
        $heroRows = $this->CI->db->get_where('realm_heroes',['realm_id'=>$realmId])->result_array();
        $agg = [];
        foreach ($heroRows as $h) {
            $tals = $this->heroTalents($realmId, $h['hero_id']);
            $part = $this->aggregateBonuses($tals);
            foreach ($part as $k=>$vals) {
                if (!isset($agg[$k])) $agg[$k]=[];
                $agg[$k] = array_merge($agg[$k], $vals);
            }
        }
        // Equipamiento / sets
        $equip = $this->equipmentBonuses($realmId);
        foreach ($equip as $k=>$vals) {
            if (!isset($agg[$k])) $agg[$k]=[];
            $agg[$k] = array_merge($agg[$k], $vals);
        }

        // Resolver stacking y caps en dos scopes
        $econ = ['gold_pct'=>0.0,'mana_pct'=>0.0,'research_pct'=>0.0];
        $comb = ['attack_pct'=>0.0,'defense_pct'=>0.0,'unit_attack_flat'=>0.0,'unit_defense_flat'=>0.0];

        foreach ($agg as $k=>$vals) {
            if (in_array($k, ['gold_pct','mana_pct','research_pct','attack_pct','defense_pct'], true)) {
                $mode = $stacking[$k] ?? 'add';
                $value = 0.0;
                if ($mode==='add') { $value = array_sum($vals); }
                elseif ($mode==='mult') { $value = array_product(array_map(function($x){return 1.0+$x;}, $vals)) - 1.0; }
                elseif ($mode==='max') { $value = max($vals); }
                $cap = $caps[$k] ?? null;
                if ($cap !== null) $value = min($value, (float)$cap);
                if (isset($econ[$k])) $econ[$k] = $value;
                if (isset($comb[$k])) $comb[$k] = $value;
            } else {
                // flat bonuses
                $sum = array_sum($vals);
                $cap = $flatCaps[$k] ?? null;
                if ($cap !== null) $sum = min($sum, (float)$cap);
                if (isset($comb[$k])) $comb[$k] = $sum;
                else $econ[$k] = $sum; // allow arbitrary future flats
            }
        }

        $payloadE = json_encode($econ, JSON_UNESCAPED_UNICODE);
        $payloadC = json_encode($comb, JSON_UNESCAPED_UNICODE);
        $now = time();
        // upsert compiled_bonuses
        $this->CI->db->replace('compiled_bonuses', ['realm_id'=>$realmId,'scope'=>'economy','payload'=>$payloadE,'updated_at'=>$now]);
        $this->CI->db->replace('compiled_bonuses', ['realm_id'=>$realmId,'scope'=>'combat','payload'=>$payloadC,'updated_at'=>$now]);

        return ['economy'=>$econ,'combat'=>$comb];
    }

    public function getCompiled(int $realmId, string $scope='economy'): array {
        $row = $this->CI->db->get_where('compiled_bonuses',['realm_id'=>$realmId,'scope'=>$scope])->row_array();
        if (!$row) {
            $all = $this->compileRealm($realmId);
            return $all[$scope] ?? [];
        }
        return json_decode($row['payload'] ?? '[]', true) ?: [];
    }
}
