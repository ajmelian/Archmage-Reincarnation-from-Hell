<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * TwoFA: TOTP RFC 6238 con secreto Base32
 */
class TwoFA {
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); }

    public function base32encode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $out=''; $buffer=0; $bitsLeft=0;
        for ($i=0; $i<strlen($data); $i++) { $buffer = ($buffer<<8) | ord($data[$i]); $bitsLeft += 8;
            while ($bitsLeft >= 5) { $index = ($buffer >> ($bitsLeft-5)) & 31; $bitsLeft -= 5; $out .= $alphabet[$index]; } }
        if ($bitsLeft>0) { $out .= $alphabet[($buffer << (5-$bitsLeft)) & 31]; }
        return $out;
    }
    public function base32decode($s) {
        $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $map=array_flip(str_split($alphabet));
        $buffer=0; $bitsLeft=0; $out='';
        $s = strtoupper(preg_replace('/[^A-Z2-7]/','',$s));
        for ($i=0;$i<strlen($s);$i++){ $buffer=($buffer<<5)|$map[$s[$i]]; $bitsLeft+=5;
            if ($bitsLeft>=8){ $bitsLeft-=8; $out.=chr(($buffer>>$bitsLeft)&255);} }
        return $out;
    }
    public function randomSecret($bytes=20) { return $this->base32encode(openssl_random_pseudo_bytes($bytes)); }

    public function totp($secret, $timeStep=30, $digits=6, $t0=0) {
        $key = $this->base32decode($secret);
        $counter = floor((time()-$t0)/$timeStep);
        $binCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $binCounter, $key, true);
        $offset = ord($hash[19]) & 0xf;
        $code = ((ord($hash[$offset]) & 0x7f) << 24) |
                ((ord($hash[$offset+1]) & 0xff) << 16) |
                ((ord($hash[$offset+2]) & 0xff) << 8) |
                (ord($hash[$offset+3]) & 0xff);
        $otp = $code % (10 ** $digits);
        return str_pad((string)$otp, $digits, '0', STR_PAD_LEFT);
    }
    public function verify($secret, $code, $window=1) {
        $code = trim($code);
        for ($w=-$window; $w<=$window; $w++) {
            $t = $this->totpAt($secret, time() + ($w*30));
            if (hash_equals($t, $code)) return true;
        }
        return false;
    }
    private function totpAt($secret, $timestamp) {
        $key = $this->base32decode($secret);
        $counter = floor($timestamp/30);
        $binCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $binCounter, $key, true);
        $offset = ord($hash[19]) & 0xf;
        $code = ((ord($hash[$offset]) & 0x7f) << 24) |
                ((ord($hash[$offset+1]) & 0xff) << 16) |
                ((ord($hash[$offset+2]) & 0xff) << 8) |
                (ord($hash[$offset+3]) & 0xff);
        $otp = $code % (10 ** 6);
        return str_pad((string)$otp, 6, '0', STR_PAD_LEFT);
    }
    public function otpauthURI($issuer, $account, $secret) {
        $issuer = rawurlencode($issuer); $account = rawurlencode($account);
        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }
}
