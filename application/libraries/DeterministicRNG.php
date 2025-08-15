<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RNG determinista basado en HMAC-SHA256 (CTR) para reproducibilidad por batalla.
 */
class DeterministicRNG {
    private $seed;
    private $counter = 0;

    public function seed($seed) {
        $this->seed = (string)$seed;
        $this->counter = 0;
    }

    private function bytes($n) {
        $out = '';
        while (strlen($out) < $n) {
            $block = hash_hmac('sha256', pack('N', $this->counter), $this->seed, true);
            $out .= $block;
            $this->counter++;
        }
        return substr($out, 0, $n);
    }

    public function nextFloat() {
        $b = $this->bytes(8);
        $v = unpack('Q', $b)[1];
        // Normalizar a [0,1)
        return ($v & ((1<<53)-1)) / floatval(1<<53);
    }
}
