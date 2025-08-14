<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Econcli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('EconomyService');
        $this->load->database();
    }

    public function show_params() {
        $rows = $this->db->order_by('key','ASC')->get('econ_params')->result_array();
        foreach ($rows as $r) echo $r['key'].' = '.$r['value'].PHP_EOL;
    }

    public function set($key, $value) {
        $v = is_numeric($value) ? 0+$value : $value;
        $this->economyservice->setParam($key, $v);
        echo "Set {$key} = {$value}\n";
    }

    public function mod_add($realmIdOrGlobal, $key, $value, $minutes='0', $reason='cli') {
        $realmId = ($realmIdOrGlobal==='global') ? null : (int)$realmIdOrGlobal;
        $exp = is_numeric($minutes) && (int)$minutes>0 ? time() + ((int)$minutes)*60 : null;
        $this->db->insert('econ_modifiers',[
            'realm_id'=>$realmId,'key'=>$key,'value'=>0+$value,'scope'=>'all','reason'=>$reason,'expires_at'=>$exp,'created_at'=>time()
        ]);
        echo "Modifier added ({$key}={$value}) realm=".($realmId??'global')." exp=".($exp?date('c',$exp):'none').PHP_EOL;
    }

    public function mod_del($id) {
        $this->db->delete('econ_modifiers',['id'=>(int)$id]);
        echo "Modifier #{$id} removed\n";
    }

    public function simulate($realmId) {
        $p = $this->economyservice->preview((int)$realmId);
        echo json_encode($p, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
    }

    public function tick_one($realmId) {
        $p = $this->economyservice->tick((int)$realmId);
        echo "Tick applied to realm {$realmId}\n";
        echo json_encode($p['net'], JSON_UNESCAPED_UNICODE).PHP_EOL;
    }

    public function tick_all($limit='200') {
        $n = $this->economyservice->tickAll((int)$limit);
        echo "Tick applied to {$n} realms\n";
    }
}
