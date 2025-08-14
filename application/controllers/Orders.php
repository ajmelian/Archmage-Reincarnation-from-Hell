<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {
    public function submit() {
        $this->output->set_content_type('application/json');

        $userId = 1; // TODO: sesión real
        $tick = (int)$this->input->post('tick', TRUE);
        $type = $this->input->post('type', TRUE);

        if (!$tick || !$type) {
            echo json_encode(['ok'=>false,'error'=>'Missing fields']); return;
        }

        // Idempotencia básica por usuario+tick+payload
        $payload = ['type'=>$type, 'params'=>$_POST];
        $idKey = substr(hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE), 'app-key'), 0, 32);

        $this->db->insert('orders', [
            'user_id' => $userId,
            'tick' => $tick,
            'idempotency_key' => $idKey,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'created_at' => time()
        ]);

        echo json_encode(['ok'=>true,'key'=>$idKey]);
    }
}
