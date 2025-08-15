<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Importer {
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); $this->CI->load->library('ContentService'); }

    public function import($filePath, $type) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'csv') return $this->importCsv($filePath, $type);
        if (in_array($ext, ['ods','xlsx'])) return $this->importSpreadsheet($filePath, $type);
        return ['ok'=>false,'error'=>'unsupported_format'];
    }

    private function mapRow($row, $type) {
        switch ($type) {
            case 'units':
                return [
                    'code'=>$row['code'] ?? $row[0] ?? null,
                    'name'=>$row['name'] ?? $row[1] ?? null,
                    'type'=>$row['type'] ?? $row[2] ?? 'melee',
                    'attack_types'=>$row['attack_types'] ?? $row[3] ?? null,
                    'power'=>(int)($row['power'] ?? $row[4] ?? 0),
                    'res_melee'=>(float)($row['res_melee'] ?? $row[5] ?? 0),
                    'res_ranged'=>(float)($row['res_ranged'] ?? $row[6] ?? 0),
                    'res_flying'=>(float)($row['res_flying'] ?? $row[7] ?? 0),
                ];
            case 'spells':
                return [
                    'code'=>$row['code'] ?? $row[0] ?? null,
                    'name'=>$row['name'] ?? $row[1] ?? null,
                    'color_id'=>null, 'rarity_id'=>null,
                    'base_success'=>(float)($row['base_success'] ?? $row[4] ?? 1.0),
                    'mana_cost'=>(int)($row['mana_cost'] ?? $row[5] ?? 0),
                    'effect'=>$row['effect'] ?? $row[6] ?? null,
                ];
            case 'items':
                return [
                    'code'=>$row['code'] ?? $row[0] ?? null,
                    'name'=>$row['name'] ?? $row[1] ?? null,
                    'rarity_id'=>null,
                    'base_success'=>(float)($row['base_success'] ?? $row[3] ?? 1.0),
                    'effect'=>$row['effect'] ?? $row[4] ?? null,
                ];
            case 'heroes':
                return [
                    'code'=>$row['code'] ?? $row[0] ?? null,
                    'name'=>$row['name'] ?? $row[1] ?? null,
                    'class'=>$row['class'] ?? $row[2] ?? null,
                    'bonus'=>isset($row['bonus']) ? json_encode($row['bonus']) : ($row[3] ?? null),
                ];
            default:
                return null;
        }
    }

    private function importCsv($filePath, $type) {
        $h = fopen($filePath, 'r'); if (!$h) return ['ok'=>false,'error'=>'open_failed'];
        $headers = fgetcsv($h);
        $count=0;
        while (($r = fgetcsv($h)) !== false) {
            $assoc = [];
            if ($headers) { foreach ($headers as $i=>$hname) { $assoc[$hname] = $r[$i] ?? null; } }
            $mapped = $this->mapRow($assoc ?: $r, $type);
            if ($mapped && $mapped['code']) { $this->CI->contentservice->create($type, $mapped); $count++; }
        }
        fclose($h);
        return ['ok'=>true,'count'=>$count];
    }

    private function importSpreadsheet($filePath, $type) {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            return ['ok'=>false,'error'=>'phpspreadsheet_missing'];
        }
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            $headers = array_shift($rows);
            $count=0;
            foreach ($rows as $row) {
                $assoc = [];
                foreach ($headers as $k=>$name) { $assoc[strtolower($name)] = $row[$k]; }
                $mapped = $this->mapRow($assoc, $type);
                if ($mapped && $mapped['code']) { $this->CI->contentservice->create($type, $mapped); $count++; }
            }
            return ['ok'=>true,'count'=>$count];
        } catch (\Throwable $e) {
            return ['ok'=>false,'error'=>'spreadsheet_error','message'=>$e->getMessage()];
        }
    }
}
