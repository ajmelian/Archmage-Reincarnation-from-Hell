<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['Importer','Schema']);
        $this->load->helper(['url','form']);
        // Seguridad: sólo admin
        $uid = (int)$this->session->userdata('userId');
        $user = $this->db->get_where('users', ['id'=>$uid])->row_array();
        if (!$user || ($user['role'] ?? '') !== 'admin') { show_error('Forbidden', 403); }
    }

    public function index() {
        $logs = $this->db->order_by('id','DESC')->limit(25)->get('import_logs')->result_array();
        $this->load->view('admin/import/index', ['logs'=>$logs]);
    }

    public function run() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $kind = trim((string)$this->input->post('kind', TRUE));
        $mode = trim((string)$this->input->post('mode', TRUE)); // noop|tx_rollback|commit
        if (!in_array($kind, ['units','buildings','research','spells','heroes','items'], true)) show_error('Invalid kind', 400);
        if (!in_array($mode, ['noop','tx_rollback','commit'], true)) $mode = 'noop';

        // file upload
        if (empty($_FILES['file']['tmp_name'])) show_error('No file', 400);
        $tmp = $_FILES['file']['tmp_name'];
        $name = $_FILES['file']['name'];

        try {
            $rows = $this->importer->parse($tmp);
            $result = $this->importer->validateAndDiff($kind, $rows);
            $applyStats = $this->importer->apply($kind, $result['diffs'], $mode);

            $logId = $this->logImport([
                'kind'=>$kind,'filename'=>$name,'dry_run'=>($mode!=='commit')?1:0,'mode'=>$mode,
                'stats'=>json_encode(['planned'=>$result['stats'],'applied'=>$applyStats], JSON_UNESCAPED_UNICODE),
                'issues'=>json_encode($result['issues'], JSON_UNESCAPED_UNICODE),
                'diffs'=>json_encode($result['diffs'], JSON_UNESCAPED_UNICODE),
            ]);
            $this->load->view('admin/import/result', [
                'kind'=>$kind,'filename'=>$name,'mode'=>$mode,
                'planned'=>$result['stats'],'applied'=>$applyStats,
                'issues'=>$result['issues'],'diffs'=>$result['diffs'],'logId'=>$logId
            ]);
        } catch (Throwable $e) {
            $this->load->view('admin/import/error', ['error'=>$e->getMessage()]);
        }
    }

    private function logImport(array $data): int {
        $this->db->insert('import_logs', [
            'kind'=>$data['kind'],
            'filename'=>$data['filename'],
            'dry_run'=>$data['dry_run'],
            'mode'=>$data['mode'],
            'stats'=>$data['stats'],
            'issues'=>$data['issues'],
            'diffs'=>$data['diffs'],
            'created_at'=>time(),
            'actor_user_id'=>(int)$this->session->userdata('userId'),
        ]);
        return (int)$this->db->insert_id();
    }
}
