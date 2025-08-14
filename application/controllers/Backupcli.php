<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Backupcli extends CI_Controller {
    public function __construct() { parent::__construct(); if (!is_cli()) show_404(); $this->load->library('BackupService'); }
    public function create() {
        $fname = $this->backupservice->createDump('cli');
        echo "Created: {$fname}\n";
    }
    public function prune() {
        $this->backupservice->prune();
        echo "Prune completed\n";
    }
    public function list() {
        $rows = $this->backupservice->listFiles();
        foreach ($rows as $r) echo "#{$r['id']} {$r['filename']} ".round($r['size_bytes']/1048576,2)."MB ".date('c',$r['created_at'])."\n";
    }
}
