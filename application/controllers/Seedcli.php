<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Seedcli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
        $this->load->config('backup');
    }

    private function dir(): string {
        $d = rtrim($this->config->item('backup')['dir'] ?? (APPPATH.'../backups'), '/');
        if (!is_dir($d)) @mkdir($d, 0775, true);
        $sd = $d.'/seeds';
        if (!is_dir($sd)) @mkdir($sd, 0775, true);
        return $sd;
    }

    public function export($tablesCsv='') {
        $tables = $tablesCsv ? array_filter(array_map('trim', explode(',', $tablesCsv))) : ($this->config->item('backup')['seed_tables'] ?? []);
        $ts = date('Ymd_His');
        foreach ($tables as $t) {
            $rows = $this->db->get($t)->result_array();
            $fn = $this->dir()."/{$t}_{$ts}.csv";
            $fp = fopen($fn, 'w');
            if (!$rows) { fclose($fp); continue; }
            fputcsv($fp, array_keys($rows[0]));
            foreach ($rows as $r) fputcsv($fp, $r);
            fclose($fp);
            echo "Exported {$t} -> {$fn}\n";
        }
        $this->db->insert('backup_jobs',['type'=>'seed_export','status'=>'done','filename'=>null,'meta'=>json_encode(['tables'=>$tables]),'created_at'=>time(),'finished_at'=>time()]);
    }

    public function imp($csvFile, $table) {
        $fn = $csvFile;
        if (!is_file($fn)) { echo "CSV not found: {$fn}\n"; return; }
        $fp = fopen($fn, 'r');
        $headers = fgetcsv($fp);
        if (!$headers) { echo "Empty CSV\n"; return; }
        $this->db->trans_start();
        while (($row = fgetcsv($fp)) !== false) {
            $data = [];
            foreach ($headers as $i=>$h) $data[$h] = $row[$i] ?? null;
            $this->db->insert($table, $data);
        }
        $this->db->trans_complete();
        fclose($fp);
        $ok = $this->db->trans_status();
        $this->db->insert('backup_jobs',['type'=>'seed_import','status'=>$ok?'done':'error','filename'=>basename($fn),'meta'=>json_encode(['table'=>$table]),'created_at'=>time(),'finished_at'=>time()]);
        echo $ok ? "Imported OK into {$table}\n" : "Import failed\n";
    }
}
