<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Turn extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->library('Engine');
        $this->load->database();
    }

    public function run() {
        // Esqueleto: tomar órdenes 'pending' por tick, resolver y aplicar.
        $tick = $this->input->get('tick') ? (int)$this->input->get('tick') : $this->getNextTick();
        echo "Resolviendo turno $tick...\n";

        $orders = $this->db->get_where('orders', ['tick'=>$tick, 'status'=>'pending'])->result_array();
        // TODO: agrupar por reino/usuario, validar, aplicar al estado, resolver combates si los hay.
        foreach ($orders as $o) {
            // Marca como aplicada (placeholder)
            $this->db->where('id', $o['id'])->update('orders', ['status'=>'applied']);
        }

        // Guardar registro del turno
        $this->db->insert('turns', ['tick'=>$tick, 'resolved_at'=>time(), 'notes'=>'OK']);
        echo "Turno $tick resuelto.\n";
    }

    private function getNextTick(): int {
        $q = $this->db->select_max('tick','t')->get('turns')->row_array();
        return (int)($q['t'] ?? 0) + 1;
    }
}
