<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Caching {
    private $CI;
    private $prefix;
    private $ttl;
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->driver('cache');
        $this->CI->load->config('cache_ext');
        $cfg = $this->CI->config->item('cache_ext');
        $this->prefix = $cfg['key_prefix'] ?? 'am_';
        $this->ttl = (int)($cfg['default_ttl'] ?? 60);
    }
    private function k($key) { return $this->prefix.$key; }

    public function get($key) { return $this->CI->cache->get($this->k($key)); }
    public function set($key, $val, $ttl=null) {
        return $this->CI->cache->save($this->k($key), $val, $ttl ?? $this->ttl);
    }
    public function delete($key) { return $this->CI->cache->delete($this->k($key)); }

    public function remember($key, $ttl, callable $cb) {
        $v = $this->get($key);
        if ($v !== FALSE && $v !== null) return $v;
        $v = $cb();
        $this->set($key, $v, $ttl);
        return $v;
    }

    // Tagging (simple): mantiene una lista de claves por tag para invalidar
    public function tagSet(array $tags, $key, $ttl=null) {
        foreach ($tags as $t) {
            $listKey = $this->k('_tag_'.$t);
            $list = $this->CI->cache->get($listKey) ?: [];
            if (!in_array($key, $list, true)) $list[] = $key;
            $this->CI->cache->save($listKey, $list, $ttl ?? $this->ttl*10);
        }
    }
    public function invalidateTag($tag) {
        $listKey = $this->k('_tag_'.$tag);
        $list = $this->CI->cache->get($listKey) ?: [];
        foreach ($list as $k) $this->delete($k);
        $this->CI->cache->delete($listKey);
    }
}
