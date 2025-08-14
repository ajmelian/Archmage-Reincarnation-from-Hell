<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Backupcli extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
        $this->load->dbutil();
        $this->load->config('backup');
    }

    private function dir(): string {
        $d = rtrim($this->config->item('backup')['dir'] ?? (APPPATH.'../backups'), '/');
        if (!is_dir($d)) @mkdir($d, 0775, true);
        return $d;
    }

    private function rotate(): void {
        $keep = (int)($this->config->item('backup')['keep'] ?? 10);
        $files = glob($this->dir().'/*.sql*');
        usort($files, function($a,$b){ return filemtime($b) - filemtime($a); });
        if (count($files) > $keep) {
            foreach (array_slice($files, $keep) as $f) @unlink($f);
        }
    }

    public function list() {
        $files = glob($this->dir().'/*.sql*');
        usort($files, function($a,$b){ return filemtime($b) - filemtime($a); });
        foreach ($files as $f) {
            echo basename($f).'  '.date('Y-m-d H:i', filemtime($f)).'  '.filesize($f)." bytes\n";
        }
    }

    public function dump($tablesCsv='') {
        $tables = array_filter(array_map('trim', explode(',', $tablesCsv)));
        $prefs = ['format'=>'txt', 'filename'=>'archmage.sql', 'add_drop'=>TRUE, 'add_insert'=>TRUE, 'newline'=>"\n"];
        if ($tables) {
            $backup = $this->dbutil->backup($prefs + ['tables'=>$tables]);
            $type = 'tables';
        } else {
            $backup = $this->dbutil->backup($prefs);
            $type = 'db_full';
        }
        if (!$backup) { echo "Backup failed\n"; return; }
        $ts = date('Ymd_His');
        $gzip = (bool)($this->config->item('backup')['gzip'] ?? true);
        $name = $type.'_'.$ts.'.sql'.($gzip?'.gz':'');
        $path = $this->dir().'/'.$name;
        if ($gzip) {
            $gz = gzopen($path, 'wb9'); gzwrite($gz, $backup); gzclose($gz);
        } else {
            file_put_contents($path, $backup);
        }
        $this->db->insert('backup_jobs',['type'=>$type,'status'=>'done','filename'=>$name,'meta'=>json_encode(['tables'=>$tables]),'created_at'=>time(),'finished_at'=>time()]);
        $this->rotate();
        echo "Backup written: {$path}\n";
    }

    public function restore($filename) {
        $file = $this->dir().'/'.$filename;
        if (!is_file($file)) { echo "File not found: {$file}\n"; return; }
        if (substr($file, -3)=='.gz') {
            $sql = gzdecode(file_get_contents($file));
        } else {
            $sql = file_get_contents($file);
        }
        if (!$sql) { echo "Empty SQL\n"; return; }
        $this->db->trans_start();
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        foreach (array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/',$sql))) as $stmt) {
            if ($stmt==='') continue;
            $this->db->query($stmt);
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        $this->db->trans_complete();
        $ok = $this->db->trans_status();
        $this->db->insert('backup_jobs',['type'=>'restore','status'=>$ok?'done':'error','filename'=>$filename,'created_at'=>time(),'finished_at'=>time()]);
        echo $ok ? "Restore OK\n" : "Restore failed\n";
    }
}
