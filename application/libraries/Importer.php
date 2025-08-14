<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Importer {

    private CI_Controller $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->library('Schema');
    }

    public function parse(string $path): array {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'csv') return $this->parseCsv($path);
        if ($ext === 'ods') return $this->parseOds($path);
        throw new Exception("Unsupported file type: $ext");
    }

    private function parseCsv(string $path): array {
        $rows = []; $headers = [];
        if (!($fh = fopen($path, 'r'))) throw new Exception("Cannot open CSV");
        if (($h = fgetcsv($fh)) !== false) $headers = $this->normalizeHeaders($h);
        while (($r = fgetcsv($fh)) !== false) {
            if (count($r) === 1 && trim($r[0]) === '') continue;
            $rows[] = array_combine($headers, array_map('trim', $r));
        }
        fclose($fh);
        return $rows;
    }

    private function parseOds(string $path): array {
        if (!class_exists('ZipArchive')) throw new Exception('ZipArchive required for ODS');
        $zip = new ZipArchive();
        if ($zip->open($path) !== TRUE) throw new Exception("Cannot open ODS");
        $xml = $zip->getFromName('content.xml');
        $zip->close();
        if (!$xml) throw new Exception("ODS missing content.xml");
        $sx = @simplexml_load_string($xml);
        if (!$sx) throw new Exception("Cannot parse ODS content.xml");

        $sx->registerXPathNamespace('table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0');
        $sx->registerXPathNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');

        $rows = [];
        $sheet = $sx->xpath('//table:table[1]')[0]; // first sheet
        $header = [];
        $first = TRUE;
        foreach ($sheet->xpath('.//table:table-row') as $tr) {
            $cells = [];
            foreach ($tr->xpath('./table:table-cell') as $cell) {
                $rep = (int)($cell->attributes('table', TRUE)->numbercolumnsrepeated ?? 1);
                $text = '';
                foreach ($cell->xpath('.//text:p') as $p) {
                    $text .= (string)$p;
                }
                for ($i=0; $i<$rep; $i++) $cells[] = trim($text);
            }
            if ($first) {
                $header = $this->normalizeHeaders($cells);
                $first = FALSE;
            } else {
                if (count(array_filter($cells)) === 0) continue;
                $rows[] = array_combine($header, array_slice($cells, 0, count($header)));
            }
        }
        return $rows;
    }

    private function normalizeHeaders(array $headers): array {
        $norm = [];
        foreach ($headers as $h) {
            $h = trim(mb_strtolower($h));
            $h = str_replace([' ', '/', '-', 'á','é','í','ó','ú'], ['_','_','_','a','e','i','o','u'], $h);
            $norm[] = $h;
        }
        return $norm;
    }

    /** Validate and prepare diffs for a given kind. */
    public function validateAndDiff(string $kind, array $rows): array {
        $issues = [];
        $diffs = [];
        $ids = [];
        $stats = ['inserted'=>0,'updated'=>0,'skipped'=>0,'errors'=>0];

        foreach ($rows as $idx=>$r) {
            $r = Schema::normalizeRow($kind, $r);
            if (empty($r['id'])) {
                $issues[] = ['row'=>$idx+1,'severity'=>'error','message'=>'Missing id'];
                $stats['errors']++; continue;
            }
            if (isset($ids[$r['id']])) {
                $issues[] = ['row'=>$idx+1,'severity'=>'error','message'=>"Duplicate id: {$r['id']}"];
                $stats['errors']++; continue;
            }
            $ids[$r['id']] = TRUE;
            $r = Schema::typecast($kind, $r);
            $vr = Schema::validate($kind, $r);
            foreach ($vr['errors'] as $e) { $issues[] = ['row'=>$idx+1,'severity'=>'error','message'=>$e]; $stats['errors']++; }
            foreach ($vr['warnings'] as $w) { $issues[] = ['row'=>$idx+1,'severity'=>'warn','message'=>$w]; }

            // Reference checks (best-effort)
            foreach (Schema::references($kind) as $field=>$ref) {
                if (!empty($r[$field])) {
                    $exists = $this->CI->db->select($ref['column'].' as id')->get_where($ref['table'], [$ref['column']=>$r[$field]])->row_array();
                    if (!$exists) {
                        $issues[] = ['row'=>$idx+1,'severity'=>'error','message'=>"Missing reference: {$field}='{$r[$field]}' in {$ref['table']}"];
                        $stats['errors']++;
                    }
                }
            }

            // Build diff vs DB
            $table = $this->tableForKind($kind);
            $existing = $this->CI->db->get_where($table, ['id'=>$r['id']])->row_array();
            if (!$existing) {
                $diffs[] = ['op'=>'insert','id'=>$r['id'],'data'=>$this->project($kind,$r)];
                $stats['inserted']++;
            } else {
                $changed = $this->diffAssoc($this->project($kind,$existing), $this->project($kind,$r));
                if ($changed) { $diffs[] = ['op'=>'update','id'=>$r['id'],'changes'=>$changed]; $stats['updated']++; }
                else { $stats['skipped']++; }
            }
        }

        return ['issues'=>$issues,'diffs'=>$diffs,'stats'=>$stats];
    }

    private function diffAssoc(array $a, array $b): array {
        $changes = [];
        foreach ($b as $k=>$v) {
            $av = $a[$k] ?? null;
            if ($av !== $v) $changes[$k] = ['from'=>$av,'to'=>$v];
        }
        return $changes;
    }

    private function tableForKind(string $kind): string {
        switch ($kind) {
            case 'units': return 'unit_def';
            case 'buildings': return 'building_def';
            case 'research': return 'research_def';
            case 'spells': return 'spell_def';
            case 'heroes': return 'hero_def';
            case 'items': return 'item_def';
            default: throw new Exception("Unknown kind: $kind");
        }
    }

    private function project(string $kind, array $r): array {
        // keep only known fields per table
        $keep = [
            'unit_def'=>['id','name','attack','defense','hp','cost','damage_type','resist'],
            'building_def'=>['id','name','cost','outputs'],
            'research_def'=>['id','name','cost','effect'],
            'spell_def'=>['id','name','school','type','target','mana_cost','research_cost','effect'],
            'hero_def'=>['id','name','cost','bonuses'],
            'item_def'=>['id','name','cost','slot','bonuses'],
        ];
        $table = $this->tableForKind($kind);
        $out = [];
        foreach ($keep[$table] as $k) {
            if (array_key_exists($k, $r)) $out[$k] = $r[$k];
        }
        return $out;
    }

    /** Execute diffs according to mode: noop (no writes), tx_rollback (simulate writes), commit. */
    public function apply(string $kind, array $diffs, string $mode='noop'): array {
        $table = $this->tableForKind($kind);
        $stats = ['inserted'=>0,'updated'=>0];
        if ($mode === 'noop') return $stats;

        $this->CI->db->trans_begin();
        try {
            foreach ($diffs as $d) {
                if ($d['op'] === 'insert') {
                    $this->CI->db->insert($table, $d['data']);
                    $stats['inserted']++;
                } elseif ($d['op'] === 'update') {
                    $this->CI->db->where('id', $d['id'])->update($table, array_column($d['changes'], 'to', array_keys($d['changes'])));
                    $stats['updated']++;
                }
            }
            if ($mode === 'tx_rollback') {
                $this->CI->db->trans_rollback();
            } else {
                $this->CI->db->trans_commit();
            }
        } catch (Throwable $e) {
            $this->CI->db->trans_rollback();
            throw $e;
        }
        return $stats;
    }
}
