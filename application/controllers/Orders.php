<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['security']);
    }

    public function submit() {
        $this->output->set_content_type('application/json');
        $userId = 1; // TODO: sesión real
        $tick = (int)($this->input->post('tick', TRUE) ?: 0);
        $type = $this->input->post('type', TRUE);

        if (!$tick || !$type) { echo json_encode(['ok'=>false,'error'=>'Missing fields']); return; }

        $payload = ['type'=>$type];

        switch ($type) {
            case 'explore':
                $payload['amount'] = (int)$this->input->post('amount', TRUE);
                break;
            case 'research':
                $payload['techId'] = $this->input->post('techId', TRUE);
                break;
            case 'recruit':
                $payload['unitId'] = $this->input->post('unitId', TRUE);
                $payload['qty'] = (int)$this->input->post('qty', TRUE);
                break;
            case 'attack':
                $payload['targetRealmId'] = (int)$this->input->post('targetRealmId', TRUE);
                break;
            default:
                echo json_encode(['ok'=>false,'error'=>'Unknown order']); return;
        }

        $idKey = substr(hash_hmac('sha256', json_encode([$userId,$tick,$payload], JSON_UNESCAPED_UNICODE), 'app-key'), 0, 32);

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
