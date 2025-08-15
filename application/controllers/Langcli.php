<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Langcli extends CI_Controller {
    public function __construct(){ parent::__construct(); if(!is_cli()) show_404(); $this->load->helper('directory'); }
    public function audit() {
        $langs = ['english','spanish'];
        $packs = ['common','content','battle','war'];
        $missing = [];
        $keys = [];
        foreach ($langs as $L) {
            $keys[$L] = [];
            foreach ($packs as $p) {
                $file = APPPATH.'language/'.$L.'/'.$p.'_lang.php';
                if (!file_exists($file)) continue;
                $arr = [];
                include($file);
                if (isset($lang) && is_array($lang)) {
                    foreach ($lang as $k=>$v) $keys[$L][$k] = true;
                }
            }
        }
        // usa spanish como referencia
        foreach (array_keys($keys['spanish']) as $k) {
            foreach ($langs as $L) {
                if (empty($keys[$L][$k])) $missing[] = ['lang'=>$L, 'key'=>$k];
            }
        }
        echo json_encode(['missing'=>$missing], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
    }
}
