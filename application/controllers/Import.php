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
                case 'units':
                    foreach ($rows as $r) {
                        if (!isset($r['id'])) continue;
                        $data = [
                            'id' => (string)$r.get('id', ''),
                            'name' => (string)($r.get('name') ?? $r.get('nombre') ?? ''),
                            'cost' => (int)($r.get('cost')  ?? $r.get('coste')  ?? 0),
                            'attack' => (int)($r.get('attack') ?? $r.get('ataque') ?? 0),
                            'defense' => (int)($r.get('defense') ?? $r.get('defensa') ?? 0),
                            'hp' => (int)($r.get('hp') ?? 1),
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
