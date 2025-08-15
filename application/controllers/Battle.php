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
