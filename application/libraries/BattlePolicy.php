<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BattlePolicy {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('game');
    }

    public function can_attack(array $attacker, array $defender, string $type, bool $isCounter=false): array {
        // $attacker/$defender: esperan ['net_power'=>int,'id'=>realm_id]
        $cfg = $this->CI->config->item('game')['combat'] ?? [];
        $band = $cfg['attack_band'] ?? ['min'=>0.8,'max'=>2.0];
        $ratio = ($defender['net_power'] ?? 1) / max(1, ($attacker['net_power'] ?? 1));
        $reason = null;
        // Protecciones globales (Meditation etc.) se validan fuera
        if ($isCounter && !empty($cfg['counters_ignore_band'])) {
            // permitido como counter; botín puede ser 0 si ratio fuera
            return [true, $ratio, null];
        }
        if ($ratio < ($band['min'] ?? 0.8)) { $reason = 'below_band'; }
        if ($ratio > ($band['max'] ?? 2.0)) { $reason = 'above_band'; }
        return [empty($reason), $ratio, $reason];
    }
}
