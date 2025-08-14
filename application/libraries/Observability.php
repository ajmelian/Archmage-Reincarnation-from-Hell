<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Observability {

    private $CI;
    private $reqStart = 0.0;
    private $reqLabels = [];
    private $reqName = '';

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('observability');
    }

    private function winStart(): int {
        $sec = (int)($this->CI->config->item('observability')['counter_window_sec'] ?? 60);
        $now = time();
        return (int)floor($now / $sec) * $sec;
    }

    private function normLabels(array $labels): string {
        ksort($labels);
        return json_encode($labels, JSON_UNESCAPED_UNICODE);
    }

    public function inc(string $name, array $labels=[], int $delta=1): void {
        $ws = $this->winStart();
        $lab = $this->normLabels($labels);
        $now = time();
        // upsert
        $row = $this->CI->db->get_where('metrics_counter', ['name'=>$name,'window_start'=>$ws,'labels'=>$lab])->row_array();
        if ($row) {
            $this->CI->db->set('count', 'count+'.$delta, FALSE)->set('updated_at',$now)
                ->where(['name'=>$name,'window_start'=>$ws,'labels'=>$lab])->update('metrics_counter');
        } else {
            $this->CI->db->insert('metrics_counter', ['name'=>$name,'labels'=>$lab,'window_start'=>$ws,'count'=>$delta,'updated_at'=>$now]);
        }
    }

    public function observe(string $name, int $ms, array $labels=[]): void {
        $ws = $this->winStart();
        $lab = $this->normLabels($labels);
        $now = time();
        $row = $this->CI->db->get_where('metrics_summary', ['name'=>$name,'window_start'=>$ws,'labels'=>$lab])->row_array();
        if ($row) {
            $min = min((int)$row['min_ms'], $ms);
            $max = max((int)$row['max_ms'], $ms);
            $this->CI->db->set('count','count+1',FALSE)
                ->set('sum_ms','sum_ms+'.$ms,FALSE)
                ->set('min_ms',$min)->set('max_ms',$max)
                ->set('updated_at',$now)
                ->where(['name'=>$name,'window_start'=>$ws,'labels'=>$lab])->update('metrics_summary');
        } else {
            $this->CI->db->insert('metrics_summary', [
                'name'=>$name,'labels'=>$lab,'window_start'=>$ws,'count'=>1,'sum_ms'=>$ms,'min_ms'=>$ms,'max_ms'=>$ms,'updated_at'=>$now
            ]);
        }
    }

    // Request lifecycle helpers
    public function beginRequest(string $name, array $labels=[]): void {
        $this->reqStart = microtime(true);
        $this->reqName = $name;
        $this->reqLabels = $labels;
        $this->inc($name.'_total', $labels, 1);
    }

    public function endRequest(int $status=200): void {
        if ($this->reqStart <= 0) return;
        $ms = (int)round((microtime(true) - $this->reqStart) * 1000);
        $labels = $this->reqLabels;
        $labels['status'] = (string)$status;
        $this->observe($this->reqName.'_duration_ms', $ms, $labels);
        $this->reqStart = 0.0;
    }

    // Export Prometheus
    public function exportPrometheus(int $sinceSec=300): string {
        $ns = $this->CI->config->item('observability')['prom_namespace'] ?? 'app';
        $now = time();
        $limit = $now - max(60, $sinceSec);
        $out = [];
        // counters
        $rows = $this->CI->db->order_by('window_start','ASC')
            ->get_where('metrics_counter', ['window_start >='=>$limit])->result_array();
        foreach ($rows as $r) {
            $labels = json_decode($r['labels'] ?? '{}', true) ?: [];
            $labelParts = [];
            foreach ($labels as $k=>$v) {
                $labelParts[] = $k.'="'.str_replace('"','\"', (string)$v).'"';
            }
            $metric = $ns.'_'.str_replace('.','_',$r['name']);
            $out[] = $metric.'{'.implode(',', $labelParts).'} '.(int)$r['count'];
        }
        // summaries (we emit count and sum)
        $rows = $this->CI->db->order_by('window_start','ASC')
            ->get_where('metrics_summary', ['window_start >='=>$limit])->result_array();
        foreach ($rows as $r) {
            $labels = json_decode($r['labels'] ?? '{}', true) ?: [];
            $labelParts = [];
            foreach ($labels as $k=>$v) {
                $labelParts[] = $k.'="'.str_replace('"','\"', (string)$v).'"';
            }
            $base = $ns.'_'.str_replace('.','_',$r['name']);
            $out[] = $base.'_count{'.implode(',', $labelParts).'} '.(int)$r['count'];
            $out[] = $base.'_sum{'.implode(',', $labelParts).'} '.(int)$r['sum_ms'];
            $out[] = $base.'_min{'.implode(',', $labelParts).'} '.(int)$r['min_ms'];
            $out[] = $base.'_max{'.implode(',', $labelParts).'} '.(int)$r['max_ms'];
        }
        return implode("\n", $out)."\n";
    }

    public function cleanup(): array {
        $days = (int)($this->CI->config->item('observability')['retention_days'] ?? 7);
        $limit = time() - $days*86400;
        $this->CI->db->where('window_start <', $limit)->delete('metrics_counter');
        $n1 = $this->CI->db->affected_rows();
        $this->CI->db->where('window_start <', $limit)->delete('metrics_summary');
        $n2 = $this->CI->db->affected_rows();
        return ['counter_deleted'=>$n1,'summary_deleted'=>$n2];
    }
}
