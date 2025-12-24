<?php
/**
 * Email i18n
 * 
 * @package WeCoop_Email_System
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Email_i18n {
    
    private static $translations = [
        'it' => [
            'welcome' => 'Benvenuto',
            'hello' => 'Ciao',
            'thanks' => 'Grazie'
        ],
        'en' => [
            'welcome' => 'Welcome',
            'hello' => 'Hello',
            'thanks' => 'Thank you'
        ],
        'fr' => [
            'welcome' => 'Bienvenue',
            'hello' => 'Bonjour',
            'thanks' => 'Merci'
        ],
        'es' => [
            'welcome' => 'Bienvenido',
            'hello' => 'Hola',
            'gracias' => 'Gracias'
        ],
        'ar' => [
            'welcome' => 'مرحبا',
            'hello' => 'مرحبا',
            'thanks' => 'شكرا'
        ]
    ];
    
    public static function translate($key, $lang = 'it') {
        return self::$translations[$lang][$key] ?? $key;
    }
}
