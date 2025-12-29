<?php
/**
 * WhatsApp Settings Page
 * 
 * @package WeCoop_WhatsApp
 */

if (!defined('ABSPATH')) exit;

class WECOOP_WhatsApp_Settings {
    
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }
    
    public static function add_admin_menu() {
        add_options_page(
            'Impostazioni WhatsApp',
            'WhatsApp',
            'manage_options',
            'wecoop-whatsapp-settings',
            [__CLASS__, 'render_settings_page']
        );
    }
    
    public static function register_settings() {
        register_setting('wecoop_whatsapp_settings', 'wecoop_whatsapp_api_key');
        register_setting('wecoop_whatsapp_settings', 'wecoop_whatsapp_phone_number_id');
        register_setting('wecoop_whatsapp_settings', 'wecoop_whatsapp_enable_welcome');
        
        add_settings_section(
            'wecoop_whatsapp_api_section',
            'Configurazione API WhatsApp Business',
            [__CLASS__, 'render_section_info'],
            'wecoop-whatsapp-settings'
        );
        
        add_settings_field(
            'wecoop_whatsapp_api_key',
            'API Key (Token di accesso)',
            [__CLASS__, 'render_api_key_field'],
            'wecoop-whatsapp-settings',
            'wecoop_whatsapp_api_section'
        );
        
        add_settings_field(
            'wecoop_whatsapp_phone_number_id',
            'Phone Number ID',
            [__CLASS__, 'render_phone_number_id_field'],
            'wecoop-whatsapp-settings',
            'wecoop_whatsapp_api_section'
        );
        
        add_settings_field(
            'wecoop_whatsapp_enable_welcome',
            'Invia messaggio di benvenuto',
            [__CLASS__, 'render_enable_welcome_field'],
            'wecoop-whatsapp-settings',
            'wecoop_whatsapp_api_section'
        );
    }
    
    public static function render_section_info() {
        ?>
        <p>Configura le credenziali API di WhatsApp Business per inviare messaggi automatici.</p>
        <p>Per ottenere le credenziali:</p>
        <ol>
            <li>Vai su <a href="https://developers.facebook.com/apps" target="_blank">Facebook for Developers</a></li>
            <li>Seleziona la tua app WhatsApp Business</li>
            <li>Vai su WhatsApp → API Setup</li>
            <li>Copia il <strong>Token di accesso temporaneo</strong> (o genera un token permanente)</li>
            <li>Copia il <strong>Phone number ID</strong></li>
        </ol>
        <?php
    }
    
    public static function render_api_key_field() {
        $value = get_option('wecoop_whatsapp_api_key', '');
        ?>
        <input type="text" 
               name="wecoop_whatsapp_api_key" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               placeholder="EAAxxxxxxxxxxxxxxxx">
        <p class="description">Token di accesso dalla console WhatsApp Business API</p>
        <?php
    }
    
    public static function render_phone_number_id_field() {
        $value = get_option('wecoop_whatsapp_phone_number_id', '');
        ?>
        <input type="text" 
               name="wecoop_whatsapp_phone_number_id" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               placeholder="123456789012345">
        <p class="description">ID del numero di telefono WhatsApp Business</p>
        <?php
    }
    
    public static function render_enable_welcome_field() {
        $value = get_option('wecoop_whatsapp_enable_welcome', '1');
        ?>
        <label>
            <input type="checkbox" 
                   name="wecoop_whatsapp_enable_welcome" 
                   value="1" 
                   <?php checked($value, '1'); ?>>
            Invia automaticamente messaggio di benvenuto con credenziali dopo la registrazione
        </label>
        <?php
    }
    
    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Salva impostazioni
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'wecoop_whatsapp_messages',
                'wecoop_whatsapp_message',
                'Impostazioni salvate',
                'updated'
            );
        }
        
        // Messaggi test
        if (isset($_GET['test'])) {
            if ($_GET['test'] === 'success') {
                add_settings_error(
                    'wecoop_whatsapp_messages',
                    'wecoop_whatsapp_test_success',
                    '✅ Messaggio di test inviato con successo!',
                    'updated'
                );
            } else {
                add_settings_error(
                    'wecoop_whatsapp_messages',
                    'wecoop_whatsapp_test_error',
                    '❌ Errore nell\'invio del messaggio. Controlla i log per dettagli.',
                    'error'
                );
            }
        }
        
        if (isset($_GET['error']) && $_GET['error'] === 'missing_phone') {
            add_settings_error(
                'wecoop_whatsapp_messages',
                'wecoop_whatsapp_missing_phone',
                'Inserisci un numero di telefono',
                'error'
            );
        }
        
        settings_errors('wecoop_whatsapp_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('wecoop_whatsapp_settings');
                do_settings_sections('wecoop-whatsapp-settings');
                submit_button('Salva impostazioni');
                ?>
            </form>
            
            <hr>
            
            <h2>Test Invio Messaggio</h2>
            <p>Invia un messaggio di test per verificare la configurazione.</p>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="wecoop_whatsapp_test">
                <?php wp_nonce_field('wecoop_whatsapp_test'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="test_phone">Numero di telefono</label></th>
                        <td>
                            <input type="text" 
                                   id="test_phone" 
                                   name="test_phone" 
                                   class="regular-text" 
                                   placeholder="+393331234567"
                                   required>
                            <p class="description">Numero con prefisso internazionale (es: +39 per Italia)</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Invia messaggio di test', 'secondary'); ?>
            </form>
        </div>
        <?php
    }
}

WECOOP_WhatsApp_Settings::init();
