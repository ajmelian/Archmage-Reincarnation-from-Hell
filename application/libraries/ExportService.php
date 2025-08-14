<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ExportService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    // Mapeo de módulos -> SELECT base (puedes ampliar)
    private function moduleQuery($module, $filters=[]) {
        $db = $this->CI->db;
        switch ($module) {
            case 'realms':
                $db->from('realms');
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'inventory':
                $db->from('inventory');
                if (!empty($filters['realm_id'])) $db->where('realm_id', (int)$filters['realm_id']);
                return $db;
            case 'market_listings':
                $db->from('market_listings');
                if (!empty($filters['status'])) $db->where('status', (int)$filters['status']);
                if (!empty($filters['item_id'])) $db->where('item_id', $filters['item_id']);
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'market_trades':
                $db->from('market_trades');
                if (!empty($filters['item_id'])) $db->where('item_id', $filters['item_id']);
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'auctions':
                $db->from('auctions');
                if (!empty($filters['status'])) $db->where('status', (int)$filters['status']);
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'auction_bids':
                $db->from('auction_bids');
                if (!empty($filters['auction_id'])) $db->where('auction_id', (int)$filters['auction_id']);
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'alliances':
                $db->from('alliances');
                return $db;
            case 'alliance_members':
                $db->from('alliance_members');
                if (!empty($filters['alliance_id'])) $db->where('alliance_id', (int)$filters['alliance_id']);
                return $db;
            case 'audit_log':
                $db->from('audit_log');
                if (!empty($filters['user_id'])) $db->where('user_id', (int)$filters['user_id']);
                if (!empty($filters['realm_id'])) $db->where('realm_id', (int)$filters['realm_id']);
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'mod_actions':
                $db->from('mod_actions');
                if (!empty($filters['target_realm_id'])) $db->where('target_realm_id', (int)$filters['target_realm_id']);
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'mod_flags':
                $db->from('mod_flags');
                if (!empty($filters['status'])) $db->where('status', $filters['status']);
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'economy_history':
                $db->from('economy_history');
                if (!empty($filters['realm_id'])) $db->where('realm_id', (int)$filters['realm_id']);
                if (!empty($filters['since'])) $db->where('created_at >=', (int)$filters['since']);
                return $db;
            case 'econ_params':
                $db->from('econ_params'); return $db;
            default:
                show_error('Unknown module', 400);
        }
    }

    public function fetch($module, $filters=[]) {
        $db = clone $this->CI->db; // evita contaminación de estado
        $q = $this->moduleQuery($module, $filters);
        $res = $q->get()->result_array();
        return $res;
    }

    public function toCsv(array $rows) {
        if (!$rows) return '';
        $fh = fopen('php://temp', 'w+');
        fputcsv($fh, array_keys($rows[0]));
        foreach ($rows as $r) fputcsv($fh, $r);
        rewind($fh);
        $out = stream_get_contents($fh);
        fclose($fh);
        return $out;
    }
}
