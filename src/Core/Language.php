<?php

declare(strict_types=1);

namespace App\Core {

    /**
     * Language — selector de idioma EN/ES, mismo patrón que Core\Language en el CMS.
     * Las traducciones se cargan desde lang/{locale}/*.php — cada archivo es una
     * sección (su nombre de archivo es la clave raíz, p.ej. lang/es/lessons.php → 'lessons').
     */
    class Language
    {
        private static array $translations = [];
        private static string $currentLocale = 'es';

        public static function init(): void
        {
            if (isset($_GET['lang'])) {
                self::$currentLocale = $_GET['lang'] === 'en' ? 'en' : 'es';
                Session::set('lang', self::$currentLocale);
            } elseif (Session::has('lang')) {
                self::$currentLocale = (string) Session::get('lang');
            } else {
                self::$currentLocale = 'es';
            }

            self::load(self::$currentLocale);
        }

        public static function load(string $locale): void
        {
            $dir = BASE_PATH . "/lang/{$locale}";
            self::$translations = [];

            if (is_dir($dir)) {
                foreach (glob($dir . '/*.php') ?: [] as $file) {
                    $section = basename($file, '.php');
                    self::$translations[$section] = require $file;
                }
            }
        }

        public static function get(string $key, array $placeholders = []): string
        {
            $keys  = explode('.', $key);
            $value = self::$translations;

            foreach ($keys as $k) {
                if (is_array($value) && isset($value[$k])) {
                    $value = $value[$k];
                } else {
                    return $key;
                }
            }

            if (is_string($value) && $placeholders) {
                foreach ($placeholders as $placeholder => $val) {
                    $value = str_replace(':' . $placeholder, (string) $val, $value);
                }
            }

            return is_string($value) ? $value : $key;
        }

        public static function getLocale(): string
        {
            return self::$currentLocale;
        }

        public static function formatDate(?int $timestamp = null): string
        {
            $timestamp ??= time();
            $locale  = self::$currentLocale === 'en' ? 'en_US' : 'es_ES';
            $pattern = self::$currentLocale === 'en' ? 'EEEE, MMMM d, y' : "EEEE, d 'de' MMMM 'de' y";

            $fmt = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, null, null, $pattern);
            return ucfirst((string) $fmt->format($timestamp));
        }
    }
}

namespace {
    if (!function_exists('__')) {
        function __(string $key, array $placeholders = []): string
        {
            return \App\Core\Language::get($key, $placeholders);
        }
    }
}
