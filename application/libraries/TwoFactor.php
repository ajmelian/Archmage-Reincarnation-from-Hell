<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TwoFactor {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('security');
    }

    private function base32Encode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $c) {
            $binary .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $chunks = str_split($binary, 5);
        $out = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $out .= $alphabet[bindec($chunk)];
        }
        $padLen = (8 - (strlen($out) % 8)) % 8;
        return $out . str_repeat('=', $padLen);
    }

    private function base32Decode($b32) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = strtoupper($b32);
        $b32 = preg_replace('/[^A-Z2-7]/', '', $b32);
        $binary = '';
        foreach (str_split($b32) as $c) {
            $pos = strpos($alphabet, $c);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = str_split($binary, 8);
        $out = '';
        foreach ($bytes as $byte) {
            if (strlen($byte) < 8) continue;
            $out .= chr(bindec($byte));
        }
        return $out;
    }

    public function generateSecret($len=20) {
        $raw = random_bytes($len);
        return rtrim($this->base32Encode($raw), '=');
    }

    public function totp($secretB32, $time=null) {
        $cfg = $this->CI->config->item('security_ext')['totp'] ?? ['digits'=>6,'period'=>30];
        $digits = (int)($cfg['digits'] ?? 6);
        $period = (int)($cfg['period'] ?? 30);
        $t = $time ?? time();
        $counter = pack('N*', 0) . pack('N*', floor($t / $period));
        $key = $this->base32Decode($secretB32);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncated = (ord($hash[$offset]) & 0x7F) << 24 |
                     (ord($hash[$offset+1]) & 0xFF) << 16 |
                     (ord($hash[$offset+2]) & 0xFF) << 8 |
                     (ord($hash[$offset+3]) & 0xFF);
        $code = $truncated % (10 ** $digits);
        return str_pad((string)$code, $digits, '0', STR_PAD_LEFT);
    }

    public function verify($secretB32, $code, $window=1) {
        $now = time();
        for ($i=-$window; $i<=$window; $i++) {
            if ($this->totp($secretB32, $now + $i*30) === str_pad((string)$code, 6, '0', STR_PAD_LEFT)) return true;
        }
        return false;
    }

    public function otpauthUri($userEmail, $secretB32) {
        $cfg = $this->CI->config->item('security_ext')['totp'] ?? [];
        $issuer = rawurlencode($cfg['issuer'] ?? 'Archmage');
        $label = rawurlencode(($cfg['issuer'] ?? 'Archmage').':'.$userEmail);
        $secret = $secretB32;
        $digits = (int)($cfg['digits'] ?? 6);
        $period = (int)($cfg['period'] ?? 30);
        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&digits={$digits}&period={$period}";
    }
}
