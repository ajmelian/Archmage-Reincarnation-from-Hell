<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Battle extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['Engine','BattlePolicy','CountersService','ProtectionService']);
        $this->load->helper(['url','form']);
        if (!$this->session->userdata('userId')) redirect('auth/login');
    }

    private function realmMe() { return $this->db->get_where('realms',['user_id'=>(int)$this->session->userdata('userId')])->row_array(); }

    public function can_attack($defenderId) {
        $att = $this->realmMe(); if (!$att) show_error('No realm.');
        $def = $this->db->get_where('realms',['id'=>(int)$defenderId])->row_array(); if (!$def) show_404();

        // Protecciones del defensor
        if ($this->protectionservice->has((int)$def['id'], 'meditation')) {
            $this->output->set_output(json_encode(['ok'=>false,'reason'=>'target_under_meditation'])); return;
        }
        if ($this->protectionservice->has((int)$def['id'], 'damage')) {
            $this->output->set_output(json_encode(['ok'=>false,'reason'=>'target_under_damage_protection'])); return;
        }

        // Counter?
        $isCounter = $this->countersservice->canCounter((int)$att['id'], (int)$def['id']);
        list($ok,$ratio,$reason) = $this->battlepolicy->can_attack(
            ['net_power'=>(int)$att['net_power'],'id'=>(int)$att['id']],
            ['net_power'=>(int)$def['net_power'],'id'=>(int)$def['id']],
            'regular',
            $isCounter
        );
        $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>$ok,'ratio'=>$ratio,'reason'=>$reason,'counter'=>$isCounter]));
    }

    public function simulate() {
        // Demo: recibir stacks via POST y emparejar
        $atk = json_decode($this->input->post('atk', TRUE), true) ?: [];
        $def = json_decode($this->input->post('def', TRUE), true) ?: [];
        $atkOrd = $this->engine->stack_order($atk);
        $defOrd = $this->engine->stack_order($def);
        $pairs = $this->engine->pairing($atkOrd, $defOrd);
        $this->output->set_content_type('application/json')->set_output(json_encode(['atk'=>$atkOrd,'def'=>$defOrd,'pairs'=>$pairs]));
    }
}


    public function prebattle() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $payload = json_decode($this->input->raw_input_stream, true) ?: [];
        $result = $this->prebattleservice->resolve($payload);
        // Computar loot modifier si el cliente provee NP y flag counter
        $attNP = (int)($payload['attacker']['np'] ?? 0);
        $defNP = (int)($payload['defender']['np'] ?? 0);
        $isCounter = (bool)($payload['is_counter'] ?? false);
        $lootMod = $this->battlepolicy->lootModifier($attNP, $defNP, $isCounter);
        $result['loot_modifier'] = $lootMod;
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }


    public function start() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $type = (string)($this->input->post('type', TRUE) ?: 'regular');
        $attId = (int)$this->input->post('attacker_realm_id', TRUE);
        $defId = (int)$this->input->post('defender_realm_id', TRUE);
        $isCounter = (bool)$this->input->post('is_counter', TRUE);
        $id = $this->battleservice->start($attId, $defId, $type, null, $isCounter);
        $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>true,'battle_id'=>$id]));
    }

    public function finalize() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $payload = json_decode($this->input->raw_input_stream, true) ?: [];
        $res = $this->battleservice->finalize($payload);
        $this->output->set_content_type('application/json')->set_output(json_encode(['ok'=>true,'result'=>$res]));
    }

    public function report($id) {
        $row = $this->db->get_where('battles',['id'=>(int)$id])->row_array();
        if (!$row) show_404();
        $this->output->set_content_type('application/json')->set_output($row['report'] ?: '{}');
    }
