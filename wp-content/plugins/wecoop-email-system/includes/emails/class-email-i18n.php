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
            'thanks' => 'Grazie',
            'username' => 'Username',
            'password' => 'Password',
            'membership_card' => 'Tessera',
            'view_card' => 'Visualizza Tessera'
        ],
        'en' => [
            'welcome' => 'Welcome',
            'hello' => 'Hello',
            'thanks' => 'Thank you',
            'username' => 'Username',
            'password' => 'Password',
            'membership_card' => 'Membership Card',
            'view_card' => 'View Card'
        ],
        'fr' => [
            'welcome' => 'Bienvenue',
            'hello' => 'Bonjour',
            'thanks' => 'Merci',
            'username' => 'Nom d\'utilisateur',
            'password' => 'Mot de passe',
            'membership_card' => 'Carte',
            'view_card' => 'Voir la Carte'
        ],
        'es' => [
            'welcome' => 'Bienvenido',
            'hello' => 'Hola',
            'thanks' => 'Gracias',
            'username' => 'Usuario',
            'password' => 'Contraseña',
            'membership_card' => 'Tarjeta',
            'view_card' => 'Ver Tarjeta'
        ],
        'ar' => [
            'welcome' => 'مرحبا',
            'hello' => 'مرحبا',
            'thanks' => 'شكرا',
            'username' => 'اسم المستخدم',
            'password' => 'كلمة المرور',
            'membership_card' => 'البطاقة',
            'view_card' => 'عرض البطاقة'
        ]
    ];
    
    public static function translate($key, $lang = 'it') {
        return self::$translations[$lang][$key] ?? $key;
    }
}
