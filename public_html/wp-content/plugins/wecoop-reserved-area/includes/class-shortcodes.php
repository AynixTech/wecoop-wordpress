<?php
/**
 * Shortcodes
 * 
 * @package WeCoop_Reserved_Area
 */

if (!defined('ABSPATH')) exit;

class WeCoop_RA_Shortcodes {
    
    public static function init() {
        // Inizializzazione se necessaria
    }
    
    /**
     * Shortcode: Login Form
     * Uso: [wecoop_login]
     */
    public static function login_form($atts) {
        // Se già loggato, redirect
        if (is_user_logged_in()) {
            return '<p>Sei già autenticato. <a href="' . home_url('/area-riservata/dashboard/') . '">Vai alla Dashboard</a></p>';
        }
        
        ob_start();
        ?>
        <div class="wecoop-login-wrapper">
            <div class="wecoop-login-container">
                <h2>Accedi alla tua area riservata</h2>
                
                <div id="wecoop-login-message" class="wecoop-message" style="display:none;"></div>
                
                <form id="wecoop-login-form" class="wecoop-form">
                    <div class="form-group">
                        <label for="username">Email, Username o Telefono</label>
                        <input type="text" id="username" name="username" required autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="remember" name="remember" value="1">
                            Ricordami
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Accedi</span>
                        <span class="btn-loading" style="display:none;">
                            <span class="spinner"></span> Accesso in corso...
                        </span>
                    </button>
                </form>
                
                <div class="wecoop-form-links">
                    <p>Non hai un account? <a href="<?php echo home_url('/area-riservata/registrazione/'); ?>">Registrati</a></p>
                    <p><a href="<?php echo home_url('/area-riservata/password-dimenticata/'); ?>">Password dimenticata?</a></p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Register Form
     * Uso: [wecoop_register]
     */
    public static function register_form($atts) {
        if (is_user_logged_in()) {
            return '<p>Sei già registrato. <a href="' . home_url('/area-riservata/dashboard/') . '">Vai alla Dashboard</a></p>';
        }
        
        ob_start();
        ?>
        <div class="wecoop-register-wrapper">
            <div class="wecoop-register-container">
                <h2>Registrati</h2>
                
                <div id="wecoop-register-message" class="wecoop-message" style="display:none;"></div>
                
                <form id="wecoop-register-form" class="wecoop-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome *</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cognome">Cognome *</label>
                            <input type="text" id="cognome" name="cognome" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required autocomplete="email">
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Telefono</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="+39 333 1234567">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password * <small>(minimo 8 caratteri)</small></label>
                        <input type="password" id="reg_password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Conferma Password *</label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="privacy" name="privacy" required>
                            Accetto la <a href="<?php echo home_url('/privacy-policy/'); ?>" target="_blank">Privacy Policy</a> *
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Registrati</span>
                        <span class="btn-loading" style="display:none;">
                            <span class="spinner"></span> Registrazione in corso...
                        </span>
                    </button>
                </form>
                
                <div class="wecoop-form-links">
                    <p>Hai già un account? <a href="<?php echo home_url('/area-riservata/login/'); ?>">Accedi</a></p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Dashboard
     * Uso: [wecoop_dashboard]
     */
    public static function dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>Devi effettuare il login. <a href="' . home_url('/area-riservata/login/') . '">Accedi</a></p>';
        }
        
        $user_id = get_current_user_id();
        $user = wp_get_current_user();
        $nome = get_user_meta($user_id, 'nome', true);
        $cognome = get_user_meta($user_id, 'cognome', true);
        
        ob_start();
        ?>
        <div class="wecoop-dashboard">
            <div class="dashboard-header">
                <h2>Benvenuto, <?php echo esc_html($nome . ' ' . $cognome); ?></h2>
                <button id="wecoop-logout-btn" class="btn btn-secondary">Logout</button>
            </div>
            
            <div class="dashboard-content">
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h3>Il tuo profilo</h3>
                        <p><strong>Email:</strong> <?php echo esc_html($user->user_email); ?></p>
                        <p><a href="<?php echo home_url('/area-riservata/profilo/'); ?>" class="btn btn-link">Modifica profilo</a></p>
                    </div>
                    
                    <div class="widget">
                        <h3>Link utili</h3>
                        <ul>
                            <li><a href="<?php echo home_url('/area-riservata/tessera/'); ?>">La mia tessera</a></li>
                            <li><a href="<?php echo home_url('/area-riservata/documenti/'); ?>">I miei documenti</a></li>
                            <li><a href="<?php echo home_url('/area-riservata/richieste/'); ?>">Le mie richieste</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Profile Page
     * Uso: [wecoop_profile]
     */
    public static function profile_page($atts) {
        if (!is_user_logged_in()) {
            return '<p>Devi effettuare il login. <a href="' . home_url('/area-riservata/login/') . '">Accedi</a></p>';
        }
        
        $user_id = get_current_user_id();
        $user = wp_get_current_user();
        
        ob_start();
        ?>
        <div class="wecoop-profile">
            <h2>Il mio profilo</h2>
            
            <div id="wecoop-profile-message" class="wecoop-message" style="display:none;"></div>
            
            <form id="wecoop-profile-form" class="wecoop-form">
                <h3>Informazioni personali</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="profile_nome">Nome</label>
                        <input type="text" id="profile_nome" name="nome" value="<?php echo esc_attr(get_user_meta($user_id, 'nome', true)); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_cognome">Cognome</label>
                        <input type="text" id="profile_cognome" name="cognome" value="<?php echo esc_attr(get_user_meta($user_id, 'cognome', true)); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="profile_email">Email</label>
                    <input type="email" id="profile_email" name="email" value="<?php echo esc_attr($user->user_email); ?>">
                </div>
                
                <div class="form-group">
                    <label for="profile_telefono">Telefono</label>
                    <input type="tel" id="profile_telefono" name="telefono" value="<?php echo esc_attr(get_user_meta($user_id, 'telefono', true)); ?>">
                </div>
                
                <h3>Cambia password</h3>
                
                <div class="form-group">
                    <label for="current_password">Password attuale</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nuova password</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <span class="btn-text">Salva modifiche</span>
                    <span class="btn-loading" style="display:none;">
                        <span class="spinner"></span> Salvataggio...
                    </span>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
