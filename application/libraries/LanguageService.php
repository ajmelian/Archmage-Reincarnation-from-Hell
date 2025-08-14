<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LanguageService {
    private $CI;
    private $lang;
    private $fallback;
    private $supported;
    private $loaded = [];

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('i18n');
        $this->CI->load->library('session');
        $this->CI->load->helper(['cookie','url']);
        $cfg = $this->CI->config->item('i18n');
        $this->fallback = $cfg['fallback_lang'] ?? 'en';
        $this->supported = $cfg['supported'] ?? ['en'=>'English'];
        $this->lang = $this->detect();
        $this->CI->config->set_item('language', $this->lang);
        // CI fallback a application/language/{lang}/
    }

    public function current() { return $this->lang; }

    public function supported(): array { return $this->supported; }

    public function set(string $lang): void {
        if (!isset($this->supported[$lang])) $lang = $this->CI->config->item('i18n')['default_lang'];
        $this->lang = $lang;
        $this->CI->config->set_item('language', $lang);
        $this->CI->session->set_userdata('lang', $lang);
        $cookieName = $this->CI->config->item('i18n')['cookie_name'] ?? 'am_lang';
        $days = (int)($this->CI->config->item('i18n')['cookie_days'] ?? 365);
        set_cookie($cookieName, $lang, $days*86400);
        // si el usuario está logado, persiste en users.locale
        $uid = (int)$this->CI->session->userdata('userId');
        if ($uid) $this->CI->db->update('users', ['locale'=>$lang], ['id'=>$uid]);
    }

    private function detect(): string {
        $cfg = $this->CI->config->item('i18n');
        $def = $cfg['default_lang'] ?? 'en';
        // 1) GET ?lang=
        $q = $this->CI->input->get('lang', TRUE);
        if ($q && isset($this->supported[$q])) { $this->set($q); return $q; }
        // 2) session
        $s = $this->CI->session->userdata('lang');
        if ($s && isset($this->supported[$s])) return $s;
        // 3) cookie
        $c = get_cookie($cfg['cookie_name'] ?? 'am_lang', TRUE);
        if ($c && isset($this->supported[$c])) return $c;
        // 4) user preference
        $uid = (int)$this->CI->session->userdata('userId');
        if ($uid) {
            $u = $this->CI->db->get_where('users',['id'=>$uid])->row_array();
            if ($u && !empty($u['locale']) && isset($this->supported[$u['locale']])) return $u['locale'];
        }
        // 5) Accept-Language
        if (!empty($cfg['auto_detect'])) {
            $al = $this->CI->input->server('HTTP_ACCEPT_LANGUAGE');
            if ($al) {
                foreach (explode(',', $al) as $part) {
                    $code = strtolower(substr(trim($part),0,2));
                    if (isset($this->supported[$code])) return $code;
                }
            }
        }
        return $def;
    }

    // Carga un fichero de idioma con fallback transparente
    public function load(string $file): void {
        $key = $this->lang.':'.$file;
        if (isset($this->loaded[$key])) return;
        $this->CI->lang->load($file, $this->lang);
        // fallback si algunos textos no existen
        if ($this->lang !== $this->fallback) {
            $this->CI->lang->load($file, $this->fallback);
        }
        $this->loaded[$key] = true;
    }

    // Recupera una cadena con soporte de parámetros y pluralización simple
    public function line(string $key, array $params=[]) {
        // detect file from prefix: "game.chat.title" -> file "game"
        $parts = explode('.', $key);
        if (count($parts) > 1) { $this->load(array_shift($parts)); $lineKey = implode('.', $parts); }
        else { $this->load('game'); $lineKey = $key; }
        $val = $this->CI->lang->line($lineKey);
        if (is_array($val)) {
            $n = isset($params['count']) ? (int)$params['count'] : (isset($params['n'])?(int)$params['n']:0);
            $form = ($n==1) ? ($val['one'] ?? reset($val)) : ($val['other'] ?? end($val));
            $val = $form;
        }
        if ($val === FALSE || $val === null) $val = $key;
        foreach ($params as $k=>$v) {
            $val = str_replace('{'.$k.'}', (string)$v, $val);
        }
        return $val;
    }
}
