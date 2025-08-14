<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Caching {
    private $driver;
    private $prefix;
    private $path;
    private $ttls;
    private $redis;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('cache');
        $cfg = $this->CI->config->item('cache') ?? [];
        $this->driver = $cfg['driver'] ?? 'file';
        $this->prefix = ($cfg['namespace'] ?? 'archmage') . ':';
        $this->ttls   = $cfg['ttls'] ?? [];
        $this->path   = rtrim($cfg['path'] ?? (APPPATH.'cache/archmage'), '/');
        if ($this->driver === 'file' && !is_dir($this->path)) @mkdir($this->path, 0775, true);
        if ($this->driver === 'redis' && class_exists('Redis')) {
            $this->redis = new Redis();
            $r = $cfg['redis'] ?? [];
            @$this->redis->connect($r['host'] ?? '127.0.0.1', $r['port'] ?? 6379, $r['timeout'] ?? 1.0);
            if (!empty($r['prefix'])) $this->redis->setOption(Redis::OPT_PREFIX, $r['prefix']);
        }
    }

    private function ttlFor($key, $ttl=null) {
        if ($ttl) return $ttl;
        foreach ($this->ttls as $k=>$v) {
            if (strpos($key, $k) === 0) return (int)$v;
        }
        return 60;
    }

    public function get($key) {
        $k = $this->prefix.$key;
        if ($this->driver === 'apcu' && function_exists('apcu_fetch')) {
            $ok = false; $val = apcu_fetch($k, $ok); return $ok ? $val : null;
        } elseif ($this->driver === 'redis' && $this->redis) {
            $val = $this->redis->get($k); return $val ? json_decode($val, true) : null;
        } else { // file
            $file = $this->path.'/'.md5($k).'.cache.json';
            if (!is_file($file)) return null;
            $raw = @file_get_contents($file);
            if ($raw === false) return null;
            $arr = json_decode($raw, true);
            if (!$arr) return null;
            if (!empty($arr['e']) && $arr['e'] < time()) { @unlink($file); return null; }
            return $arr['v'];
        }
    }

    public function set($key, $value, $ttl=null) {
        $k = $this->prefix.$key; $ttl = $this->ttlFor($key, $ttl);
        if ($this->driver === 'apcu' && function_exists('apcu_store')) {
            return apcu_store($k, $value, $ttl);
        } elseif ($this->driver === 'redis' && $this->redis) {
            return $this->redis->setex($k, $ttl, json_encode($value));
        } else {
            $file = $this->path.'/'.md5($k).'.cache.json';
            $payload = json_encode(['e'=>time()+$ttl,'v'=>$value]);
            return @file_put_contents($file, $payload) !== false;
        }
    }

    public function delete($key) {
        $k = $this->prefix.$key; // importante: concatenación (.) no suma (+)
        if ($this->driver === 'apcu' && function_exists('apcu_delete')) return apcu_delete($k);
        elseif ($this->driver === 'redis' && $this->redis) return $this->redis->del($k);
        else {
            $file = $this->path.'/'.md5($k).'.cache.json';
            if (is_file($file)) @unlink($file);
            return true;
        }
    }

    public function deleteByPrefix($prefix) {
        if ($this->driver === 'redis' && $this->redis) {
            $it = NULL;
            while ($arr = $this->redis->scan($it, $this->prefix.$prefix.'*')) {
                foreach ($arr as $k) { $this->redis->del($k); }
            }
            return true;
        }
        // driver file: no listado de claves; usa TTLs cortos o vaciado selectivo por namespace si fuese necesario
        // (si necesitas purgar agresivo, elimina todos los *.cache.json del directorio).
        return true;
    }
}
