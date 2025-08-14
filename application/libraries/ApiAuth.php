<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ApiAuth {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('api');
    }

    private function hashToken(string $token): string {
        return hash('sha256', $token);
    }

    public function mint(int $userId, ?int $ttlDays=null, string $name='', string $scopes='read'): array {
        $raw = bin2hex(random_bytes(24)); // 48 hex chars
        $hash = $this->hashToken($raw);
        $exp = $ttlDays ? (time() + $ttlDays*86400) : null;
        $this->CI->db->insert('api_tokens',[
            'user_id'=>$userId,'token_hash'=>$hash,'name'=>$name ?: null,'scopes'=>$scopes ?: null,
            'created_at'=>time(),'expires_at'=>$exp
        ]);
        $id = (int)$this->CI->db->insert_id();
        return ['id'=>$id,'token'=>$raw,'expires_at'=>$exp];
    }

    public function fromHeader(): ?array {
        $hdr = $this->CI->input->get_request_header('Authorization', TRUE) ?: '';
        if (stripos($hdr, 'Bearer ') !== 0) return null;
        $raw = trim(substr($hdr, 7));
        if ($raw==='') return null;
        return $this->validate($raw);
    }

    public function validate(string $raw): ?array {
        $hash = $this->hashToken($raw);
        $now = time();
        $row = $this->CI->db->get_where('api_tokens',['token_hash'=>$hash,'revoked'=>0])->row_array();
        if (!$row) return null;
        if (!empty($row['expires_at']) && (int)$row['expires_at'] < $now) return null;
        $this->CI->db->update('api_tokens',['last_used_at'=>$now],['id'=>$row['id']]);
        $user = $this->CI->db->get_where('users',['id'=>$row['user_id']])->row_array();
        if (!$user) return null;
        return ['token'=>$row,'user'=>$user];
    }

    public function revoke(int $tokenId): void {
        $this->CI->db->update('api_tokens',['revoked'=>1],['id'=>$tokenId]);
    }

    public function enforceScope(array $token, string $need): void {
        $sc = $token['scopes'] ?? '';
        if ($sc === '' || $sc === null) return; // no scopes means allow all
        $parts = array_map('trim', explode(',', $sc));
        if (!in_array($need, $parts, true)) show_error('Forbidden (scope)', 403);
    }
}
