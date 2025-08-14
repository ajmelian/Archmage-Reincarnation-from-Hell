<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BackupService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->dbutil();
        $this->CI->load->config('backup');
        $this->CI->load->helper(['file']);
        $this->cfg = $this->CI->config->item('backup') ?? [];
        $this->path = rtrim($this->cfg['path'] ?? (FCPATH.'backups'), '/');
        if (!is_dir($this->path)) @mkdir($this->path, 0775, true);
    }

    public function createDump($note=null, $tables=null) {
        $prefs = [
            'format' => ($this->cfg['format'] ?? 'gzip'),
            'add_drop' => TRUE,
            'add_insert' => TRUE,
            'newline' => "\n",
        ];
        if (is_array($tables) && $tables) $prefs['tables'] = $tables;
        $backup = $this->CI->dbutil->backup($prefs);
        if (!$backup) throw new Exception('DB backup failed');
        $ts = date('Ymd_His');
        $ext = ($this->cfg['format'] ?? 'gzip') === 'zip' ? 'zip' : 'gz';
        $fname = ($this->cfg['filename_prefix'] ?? 'db_') . $ts . '.' . $ext;
        $full = $this->path . '/' . $fname;
        write_file($full, $backup);
        $size = @filesize($full) ?: 0;
        $checksum = hash_file('sha256', $full);
        $uid = (int)($this->CI->session->userdata('userId') ?? 0) ?: null;
        $this->CI->db->insert('backups',[
            'filename'=>$fname,'size_bytes'=>$size,'checksum'=>$checksum,'type'=>'db','note'=>$note,'created_by_user_id'=>$uid,'created_at'=>time()
        ]);
        $this->prune();
        return $fname;
    }

    public function listFiles($limit=200) {
        return $this->CI->db->order_by('created_at','DESC')->limit($limit)->get('backups')->result_array();
    }

    public function delete($id) {
        $row = $this->CI->db->get_where('backups',['id'=>$id])->row_array();
        if (!$row) return false;
        $full = $this->path . '/' . $row['filename'];
        if (is_file($full)) @unlink($full);
        $this->CI->db->delete('backups',['id'=>$id]);
        return true;
    }

    public function filePath($row) {
        return $this->path . '/' . $row['filename'];
    }

    public function prune() {
        // 1) Respeta keep_last
        $keep = (int)($this->cfg['keep_last'] ?? 10);
        $rows = $this->CI->db->order_by('created_at','DESC')->get('backups')->result_array();
        $toDelete = [];
        if (count($rows) > $keep) {
            foreach (array_slice($rows, $keep) as $r) $toDelete[] = $r;
        }
        // 2) Respeta max_total_mb
        $maxBytes = (int)($this->cfg['max_total_mb'] ?? 2048) * 1024 * 1024;
        $sum = 0;
        foreach ($rows as $r) $sum += (int)$r['size_bytes'];
        if ($sum > $maxBytes) {
            // desde el más antiguo, hasta cumplir
            $acc = $sum;
            $older = array_reverse($rows);
            foreach ($older as $r) {
                if ($acc <= $maxBytes) break;
                if (!in_array($r, $toDelete, true)) $toDelete[] = $r;
                $acc -= (int)$r['size_bytes'];
            }
        }
        foreach ($toDelete as $r) $this->delete((int)$r['id']);
    }
}
