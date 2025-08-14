<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Format {
    private $CI; private $locale;
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('LanguageService');
        $this->locale = $this->CI->languageservice->current() === 'es' ? 'es_ES' : 'en_US';
    }

    public function dateTime($ts): string {
        if (class_exists('IntlDateFormatter')) {
            $fmt = new IntlDateFormatter($this->locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
            return $fmt->format($ts);
        }
        return date('Y-m-d H:i', $ts);
    }

    public function number($n, int $decimals=0): string {
        if (class_exists('NumberFormatter')) {
            $fmt = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
            $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
            return $fmt->format($n);
        }
        return number_format($n, $decimals, '.', ',');
    }
}
