<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Modcli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('ModerationService');
    }
    public function flags() {
        $rows = $this->moderationservice->flags('pending', 1000);
        foreach ($rows as $r) echo "#{$r['id']} {$r['type']} realm={$r['reporter_realm_id']} target={$r['target_type']}#{$r['target_id']} ".date('c',$r['created_at'])."\n";
    }
    public function sanction($modUserId, $targetRealmId, $action, $minutes, $reason='cli') {
        $id = $this->moderationservice->sanction((int)$modUserId,(int)$targetRealmId,$action,(int)$minutes,$reason);
        echo "Sanction #{$id} applied\n";
    }
    public function expire() {
        $n = $this->moderationservice->expire();
        echo "Expired sanctions (checked): {$n}\n";
    }
}
