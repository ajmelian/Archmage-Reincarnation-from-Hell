<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ObservabilityAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->lang->load('observability');
        $this->load->database();
        $this->load->library(['MetricsService','AuditService']);
        $this->load->helper(['url']);
        // TODO: auth/role admin
    }

    public function dashboard() {
        $top = $this->metricsservice->topToday(20);
        $recent = $this->auditservice->recent(50);
        $this->load->view('admin/observability_dashboard', ['top'=>$top,'recent'=>$recent]);
    }

    public function metrics_json() {
        $key = $this->input->get('key', TRUE) ?: 'http.Battle.finalize';
        $rows = $this->metricsservice->get($key, 14);
        $this->output->set_content_type('application/json')->set_output(json_encode(['key'=>$key,'series'=>$rows]));
    }
}
