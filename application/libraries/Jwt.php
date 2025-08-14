<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jwt {
    private string $secret;
    public function __construct() {
        $this->secret = getenv('JWT_SECRET') ?: 'change-me';
    }
    public function encode(array $payload, int $ttl=3600): string {
        $header = ['alg'=>'HS256','typ'=>'JWT'];
        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;
        $segments = [
            $this->b64(json_encode($header)),
            $this->b64(json_encode($payload))
        ];
        $signing_input = implode('.', $segments);
        $signature = hash_hmac('sha256', $signing_input, $this->secret, true);
        $segments[] = $this->b64($signature);
        return implode('.', $segments);
    }
    public function decode(string $jwt): ?array {
        $parts = explode('.', $jwt);
        if (count($parts)!==3) return null;
        list($h, $p, $s) = $parts;
        $sig = $this->ub64($s);
        $check = hash_hmac('sha256', $h.'.'.$p, $this->secret, true);
        if (!hash_equals($sig, $check)) return null;
        $payload = json_decode($this->ub64($p), true);
        if (!$payload || ($payload['exp'] ?? 0) < time()) return null;
        return $payload;
    }
    private function b64($str){ return rtrim(strtr(base64_encode($str), '+/', '-_'), '='); }
    private function ub64($str){ return base64_decode(strtr($str, '-_', '+/')); }
}
