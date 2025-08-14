<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Alliancecli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('AllianceService');
    }

    public function create($realmId, $name, $tag) {
        $id = $this->allianceservice->create((int)$realmId, $name, $tag, '');
        echo "Alliance #{$id} created\n";
    }
    public function invite($fromRealmId, $toRealmId) {
        $id = $this->allianceservice->invite((int)$fromRealmId, (int)$toRealmId);
        echo "Invite #{$id} created\n";
    }
    public function accept($realmId, $inviteId) {
        $this->allianceservice->accept((int)$realmId, (int)$inviteId);
        echo "Realm {$realmId} joined by invite {$inviteId}\n";
    }
}
