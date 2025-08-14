<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Schema {

    // Required fields per kind and basic type hints
    public static function fields(string $kind): array {
        switch ($kind) {
            case 'units':
                return [
                    'id'=>'string','name'=>'string','attack'=>'int','defense'=>'int','hp'=>'int','cost'=>'int',
                    // optional
                    'damage_type'=>'string','resist'=>'json'
                ];
            case 'buildings':
                return ['id'=>'string','name'=>'string','cost'=>'int','outputs'=>'json'];
            case 'research':
                return ['id'=>'string','name'=>'string','cost'=>'int','effect'=>'json'];
            case 'spells':
                return ['id'=>'string','name'=>'string','school'=>'string','type'=>'string','target'=>'string','mana_cost'=>'int','research_cost'=>'int','effect'=>'json'];
            case 'heroes':
                return ['id'=>'string','name'=>'string','cost'=>'int','bonuses'=>'json'];
            case 'items':
                return ['id'=>'string','name'=>'string','cost'=>'int','slot'=>'string','bonuses'=>'json'];
            default:
                return [];
        }
    }

    public static function normalizeRow(string $kind, array $row): array {
        // normalize ES/EN synonyms
        $map = [
            'name'=>['name','nombre'],
            'attack'=>['attack','ataque'],
            'defense'=>['defense','defensa'],
            'hp'=>['hp','vida'],
            'cost'=>['cost','coste','costo'],
            'school'=>['school','escuela'],
            'type'=>['type','tipo'],
            'target'=>['target','objetivo'],
            'mana_cost'=>['mana_cost','coste_mana','mana'],
            'research_cost'=>['research_cost','coste_investigacion','investigacion_coste'],
            'slot'=>['slot','ranura','ubicacion'],
            'outputs'=>['outputs','produccion','salidas'],
            'effect'=>['effect','efecto','bonus','bono'],
            'bonuses'=>['bonuses','bonos','effects','efectos'],
            'damage_type'=>['damage_type','tipo_daño','tipo_dano'],
            'resist'=>['resist','resistencia','resistencias'],
        ];
        $norm = [];
        foreach ($map as $std=>$alts) {
            foreach ($alts as $a) {
                if (array_key_exists($a, $row) && $row[$a] !== '') { $norm[$std] = $row[$a]; break; }
            }
        }
        if (isset($row['id'])) $norm['id'] = (string)$row['id'];
        return $norm + $row; // preserve extra cols
    }

    public static function typecast(string $kind, array $row): array {
        $fields = self::fields($kind);
        foreach ($fields as $k=>$t) {
            if (!array_key_exists($k, $row)) continue;
            $v = $row[$k];
            if ($t === 'int') {
                $row[$k] = (int)$v;
            } elseif ($t === 'json') {
                if (is_string($v)) {
                    $trim = trim($v);
                    if ($trim === '') { $row[$k] = json_encode(new stdClass()); continue; }
                    // Try JSON first
                    $decoded = json_decode($trim, true);
                    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                        // Attempt "k1:v1,k2:v2" -> {"k1":v1,"k2":v2}
                        $obj = [];
                        foreach (preg_split('/\s*,\s*/', $trim) as $pair) {
                            if ($pair === '') continue;
                            $parts = preg_split('/\s*:\s*/', $pair, 2);
                            if (count($parts) === 2) { $obj[$parts[0]] = is_numeric($parts[1]) ? +$parts[1] : $parts[1]; }
                        }
                        $row[$k] = json_encode($obj, JSON_UNESCAPED_UNICODE);
                    } else {
                        $row[$k] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                    }
                } elseif (is_array($v)) {
                    $row[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $row[$k] = (string)$v;
            }
        }
        return $row;
    }

    public static function validate(string $kind, array $row): array {
        $errors = []; $warnings = [];
        $fields = self::fields($kind);
        foreach ($fields as $k=>$t) {
            if (!array_key_exists($k, $row) || $row[$k] === '' || $row[$k] === null) {
                $errors[] = "Missing required field: $k";
                continue;
            }
            if ($t === 'int' && !is_numeric($row[$k])) $errors[] = "Field $k must be int";
            if ($t === 'json') {
                $v = $row[$k];
                if (is_string($v)) {
                    $decoded = json_decode($v, true);
                    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                        $errors[] = "Field $k must be JSON";
                    }
                } elseif (!is_array($v)) {
                    $warnings[] = "Field $k should be JSON";
                }
            }
        }
        // basic sanity
        if (isset($row['cost']) && (int)$row['cost'] < 0) $errors[] = "Cost must be >= 0";
        if (isset($row['hp']) && (int)$row['hp'] <= 0) $errors[] = "HP must be > 0";
        if (isset($row['attack']) && (int)$row['attack'] < 0) $errors[] = "Attack must be >= 0";
        if (isset($row['defense']) && (int)$row['defense'] < 0) $errors[] = "Defense must be >= 0";
        return ['errors'=>$errors,'warnings'=>$warnings];
    }

    public static function references(string $kind): array {
        // Map of reference fields to table and column
        switch ($kind) {
            case 'spells':
                return ['requires'=>['table'=>'research_def','column'=>'id']];
            case 'items':
                return ['hero_id'=>['table'=>'hero_def','column'=>'id']];
            case 'heroes':
                return ['requires'=>['table'=>'research_def','column'=>'id']];
            default:
                return [];
        }
    }
}
