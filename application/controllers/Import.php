<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
    }

    public function definitions() {
        $base = FCPATH.'../data/csv'; // project/data/csv
        $files = [
            'buildings' => $base.'/buildings.csv',
            'research' => $base.'/research.csv',
            'units'  => $base.'/units.csv',
            'heroes' => $base+'/heroes.csv',
            'items'  => $base+'/items.csv',
            'spells' => $base+'/spells.csv',
        ];

        foreach ($files as $kind => $path) {
            if (!is_file($path)) { echo "SKIP $kind (no file)\n"; continue; }
            echo "Importando $kind...\n";
            $rows = $this->parseCsv($path);
            switch ($kind) {
                case 'buildings':
                    foreach ($rows as $r) {
                        if (!isset($r['id'])) continue;
                        $outputs = [
                            'gold'=> (int)($r['gold'] ?? $r['oro'] ?? 0),
                            'mana'=> (int)($r['mana'] ?? 0),
                            'research'=> (int)($r['research'] ?? $r['investigacion'] ?? 0),
                            'land'=> (int)($r['land'] ?? $r['tierra'] ?? 0)
                        ];
                        $data = [
                            'id'=> (string)$r.get('id', ''),
                            'name'=> (string)($r.get('name') ?? $r.get('nombre') ?? ''),
                            'cost'=> (int)($r.get('cost') ?? $r.get('coste') ?? 0),
                            'outputs'=> json_encode($outputs),
                        ];
                        $this->db->replace('building_def', $data);
                    }
                    break;
                case 'research':
                    foreach ($rows as $r) {
                        if (!isset($r['id'])) continue;
                        $effect = [];
                        foreach (['attack_bonus','defense_bonus','gold_bonus','mana_bonus','research_bonus'] as $k) {
                            if (isset($r[$k])) $effect[$k] = (float)$r[$k];
                        }
                        $data = [
                            'id'=> (string)$r.get('id', ''),
                            'name'=> (string)($r.get('name') ?? $r.get('nombre') ?? ''),
                            'cost'=> (int)($r.get('cost') ?? $r.get('coste') ?? 0),
                            'effect'=> json_encode($effect),
                        ];
                        $this->db->replace('research_def', $data);
                    }
                    break;
                case 'units':
                    foreach ($rows as $r) {
                        if (!isset($r['id'])) continue;
                        $data = [
                            'id' => (string)$r.get('id', ''),
                            'name' => (string)($r.get('name') ?? $r.get('nombre') ?? ''),
                            'cost' => (int)($r.get('cost')  ?? $r.get('coste')  ?? 0),
                            'attack' => (int)($r.get('attack') ?? $r.get('ataque') ?? 0),
                            'defense' => (int)($r.get('defense') ?? $r.get('defensa') ?? 0),
                            'hp' => (int)($r.get('hp') ?? $r.get('vida') ?? 1),
                            'tags' => json_encode([]),
                        ];
                        $this->db->replace('unit_def', $data);
                    }
                    break;
                case 'heroes':
                    foreach ($rows as $r) {
                        if (!isset($r['id'])) continue;
                        $data = [
                            'id' => (string)$r.get('id', ''),
                            'name' => (string)($r.get('name') ?? $r.get('nombre') ?? ''),
                            'class' => (string)($r.get('class') ?? $r.get('clase') ?? ''),
                            'base_stats' => json_encode($r, JSON_UNESCAPED_UNICODE),
                        ];
                        $this->db->replace('hero_def', $data);
                    }
                    break;
                case 'items':
                    foreach ($rows as $r) {
                        if (!isset($r['id'])) continue;
                        $data = [
                            'id' => (string)$r.get('id', ''),
                            'name' => (string)($r.get('name') ?? $r.get('nombre') ?? ''),
                            'slot' => (string)($r.get('slot') ?? ''),
                            'modifiers' => json_encode($r, JSON_UNESCAPED_UNICODE),
                            'cost' => (int)($r.get('cost') ?? $r.get('coste') ?? 0),
                        ];
                        $this->db->replace('item_def', $data);
                    }
                    break;
                case 'spells':
                    foreach ($rows as $r) {
                        if (!isset($r['id'])) continue;
                        $data = [
                            'id' => (string)$r.get('id', ''),
                            'name' => (string)($r.get('name') ?? $r.get('nombre') ?? ''),
                            'school' => (string)($r.get('school') ?? $r.get('escuela') ?? ''),
                            'type' => (string)($r.get('type') ?? $r.get('tipo') ?? ''),
                            'target' => (string)($r.get('target') ?? $r.get('objetivo') ?? 'self'),
                            'power' => (int)($r.get('power') ?? $r.get('poder') ?? 0),
                            'duration' => (int)($r.get('duration') ?? $r.get('duracion') ?? 0),
                            'mana_cost' => (int)($r.get('mana_cost') ?? $r.get('coste_mana') ?? 0),
                            'research_cost' => (int)($r.get('research_cost') ?? $r.get('coste_investigacion') ?? 0),
                            'cost' => (int)($r.get('cost') ?? $r.get('coste') ?? 0),
                            'effect' => json_encode($r, JSON_UNESCAPED_UNICODE),
                            'params' => json_encode($r, JSON_UNESCAPED_UNICODE),
                        ];
                        $this->db->replace('spell_def', $data);
                    }
                    break;
                    foreach ($rows as $r) {
                        if (!isset($r['id'])) continue;
                        $data = [
                            'id' => (string)$r.get('id', ''),
                            'name' => (string)($r.get('name') ?? $r.get('nombre') ?? ''),
                            'school' => (string)($r.get('school') ?? $r.get('escuela') ?? ''),
                            'cost' => (int)($r.get('cost') ?? $r.get('coste') ?? 0),
                            'effect' => json_encode($r, JSON_UNESCAPED_UNICODE),
                        ];
                        $this->db->replace('spell_def', $data);
                    }
                    break;
            }
        }
        echo "Importación completada.\n";
    }

    private function parseCsv(string $path): array {
        $out = [];
        if (($h = fopen($path, 'r')) !== FALSE) {
            $headers = fgetcsv($h);
            $headers = array_map(fn($x)=>trim(mb_strtolower((string)$x)), $headers);
            while (($row = fgetcsv($h)) !== FALSE) {
                $r = [];
                foreach ($headers as $i=>$key) {
                    $r[$key] = isset($row[$i]) ? trim((string)$row[$i]) : null;
                }
                $out[] = $r;
            }
            fclose($h);
        }
        return $out;
    }
}
