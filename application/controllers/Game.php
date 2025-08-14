<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Game extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model(['Realm_model','Building_model','Unit_model','Research_model']);
    }

    public function index() {
        $userId = 1; // TODO: auth real
        $realm = $this->Realm_model->getOrCreate($userId);
        $state = $this->Realm_model->loadState($realm);

        $buildings = $this->Building_model->all();
        $units = $this->Unit_model->all();
        $research = $this->Research_model->all();

        $data = [
            'currentTick' => $this->getCurrentTick(),
            'realm' => $realm,
            'state' => $state,
            'buildings' => $buildings,
            'units' => $units,
            'research' => $research
        ];
        $this->render('game/index', $data);
    }

    private function getCurrentTick(): int {
        $q = $this->db->select_max('tick','t')->get('turns')->row_array();
        return (int)($q['t'] ?? 0);
    }
}
