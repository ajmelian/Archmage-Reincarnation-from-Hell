<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Export extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library(['ExportService','AdminService']);
        $this->admin = $this->adminservice->requireAdmin();
        $this->load->helper(['url','form']);
    }
    public function index() {
        $modules = ['realms','inventory','market_listings','market_trades','auctions','auction_bids','alliances','alliance_members','audit_log','mod_flags','mod_actions','economy_history','econ_params'];
        $this->load->view('export/index', ['modules'=>$modules]);
    }
    public function download() {
        $module = (string)$this->input->get('module', TRUE);
        $format = (string)$this->input->get('format', TRUE) ?: 'csv';
        $filters = [];
        foreach (['since','realm_id','user_id','alliance_id','auction_id','item_id','status','target_realm_id'] as $k) {
            $v = $this->input->get($k, TRUE); if ($v!==null && $v!=='') $filters[$k] = $v;
        }
        $rows = $this->exportservice->fetch($module, $filters);
        if ($format==='json') {
            $payload = json_encode($rows, JSON_UNESCAPED_UNICODE);
            $this->output->set_content_type('application/json');
            $this->output->set_header('Content-Disposition: attachment; filename="'.$module.'.json"');
            $this->output->set_output($payload);
        } else {
            $csv = $this->exportservice->toCsv($rows);
            $this->output->set_content_type('text/csv');
            $this->output->set_header('Content-Disposition: attachment; filename="'.$module.'.csv"');
            $this->output->set_output($csv);
        }
    }
}
