<?php
// C:\xampp\htdocs\workshopos\helpers\lang_helper.php

if (!function_exists('getAppLocale')) {
    function getAppLocale(): string {
        if (class_exists('Session') && Session::has('locale')) {
            return Session::get('locale');
        }
        if (class_exists('Auth')) {
            $user = Auth::user();
            if ($user && !empty($user['preferred_language'])) {
                return $user['preferred_language'];
            }
            $participant = Auth::participant();
            if ($participant && !empty($participant['preferred_language'])) {
                return $participant['preferred_language'];
            }
        }
        // Default to Khmer as requested
        return 'km';
    }
}

if (!function_exists('setAppLocale')) {
    function setAppLocale(string $locale): void {
        if (in_array($locale, ['km', 'en']) && class_exists('Session')) {
            Session::set('locale', $locale);
        }
    }
}

if (!function_exists('__')) {
    function __(string $key, ?string $default = null): string {
        static $translations = [];
        $locale = getAppLocale();

        if (!isset($translations[$locale])) {
            $file = defined('ROOT_PATH') ? ROOT_PATH . "/lang/{$locale}.php" : __DIR__ . "/../lang/{$locale}.php";
            if (file_exists($file)) {
                $translations[$locale] = require $file;
            } else {
                $translations[$locale] = [];
            }
        }

        if (isset($translations[$locale][$key])) {
            return $translations[$locale][$key];
        }

        // Fallback to English dictionary if exists
        if ($locale !== 'en') {
            if (!isset($translations['en'])) {
                $enFile = defined('ROOT_PATH') ? ROOT_PATH . "/lang/en.php" : __DIR__ . "/../lang/en.php";
                $translations['en'] = file_exists($enFile) ? require $enFile : [];
            }
            if (isset($translations['en'][$key])) {
                return $translations['en'][$key];
            }
        }

        return $default ?? $key;
    }
}
