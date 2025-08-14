<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Modcli extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('ModerationService');
        $this->load->database();
    }

    public function cleanup() {
        $res = $this->moderationservice->cleanup();
        echo "Cleanup: ".json_encode($res).PHP_EOL;
    }

    public function list_reports($status='open') {
        $rows = $this->moderationservice->gmListReports($status);
        foreach ($rows as $r) {
            echo "#{$r['id']} {$r['target_type']}:{$r['target_id']} by realm {$r['reporter_realm_id']} at ".date('c',$r['created_at'])." status={$r['status']}\n";
        }
    }

    public function mute($realmId, $scope='chat_global', $minutes=60, $reason='') {
        $id = $this->moderationservice->gmMute((int)$realmId, $scope, (int)$minutes, $reason);
        echo "Muted realm {$realmId} scope {$scope} minutes {$minutes} (id={$id})\n";
    }

    public function resolve($reportId, $status='resolved', $resolution='actioned') {
        $this->moderationservice->gmResolveReport((int)$reportId, $resolution, $status);
        echo "Report #{$reportId} set {$status}: {$resolution}\n";
    }
}
