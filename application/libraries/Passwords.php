<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Passwords {
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->config('security'); $this->CI->load->database(); }
    public function hash($plain) {
        $opts = $this->CI->config->item('passwords') ?? [];
        $algo = $opts['algo'] ?? PASSWORD_DEFAULT;
        $options = $opts['options'] ?? [];
        return password_hash($plain, $algo, $options);
    }
    public function verifyAndRehash(array $userRow, string $plain): bool {
        if (!password_verify($plain, $userRow['password'])) return false;
        $opts = $this->CI->config->item('passwords') ?? [];
        if (password_needs_rehash($userRow['password'], $opts['algo'] ?? PASSWORD_DEFAULT, $opts['options'] ?? [])) {
            $new = $this->hash($plain);
            $this->CI->db->update('users',['password'=>$new],['id'=>$userRow['id']]);
        }
        return true;
    }
}
