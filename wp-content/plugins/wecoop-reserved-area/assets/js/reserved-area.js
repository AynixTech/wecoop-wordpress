/**
 * WeCoop Reserved Area JS
 */

jQuery(document).ready(function($) {
    
    /**
     * Login Form
     */
    $('#wecoop-login-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $message = $('#wecoop-login-message');
        
        // Disable button
        $btn.prop('disabled', true);
        $btn.find('.btn-text').hide();
        $btn.find('.btn-loading').show();
        
        $.ajax({
            url: wecoopRA.ajaxurl,
            type: 'POST',
            data: {
                action: 'wecoop_login',
                nonce: wecoopRA.nonce,
                username: $('#username').val(),
                password: $('#password').val(),
                remember: $('#remember').is(':checked')
            },
            success: function(response) {
                if (response.success) {
                    $message.removeClass('error').addClass('success')
                        .html(response.data.message)
                        .slideDown();
                    
                    // Redirect
                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    $message.removeClass('success').addClass('error')
                        .html(response.data.message)
                        .slideDown();
                    
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').show();
                    $btn.find('.btn-loading').hide();
                }
            },
            error: function() {
                $message.removeClass('success').addClass('error')
                    .html('Errore di connessione. Riprova.')
                    .slideDown();
                
                $btn.prop('disabled', false);
                $btn.find('.btn-text').show();
                $btn.find('.btn-loading').hide();
            }
        });
    });
    
    /**
     * Register Form
     */
    $('#wecoop-register-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $message = $('#wecoop-register-message');
        
        // Validazione password
        var password = $('#reg_password').val();
        var confirm = $('#password_confirm').val();
        
        if (password !== confirm) {
            $message.removeClass('success').addClass('error')
                .html('Le password non corrispondono')
                .slideDown();
            return;
        }
        
        if (!$('#privacy').is(':checked')) {
            $message.removeClass('success').addClass('error')
                .html('Devi accettare la Privacy Policy')
                .slideDown();
            return;
        }
        
        // Disable button
        $btn.prop('disabled', true);
        $btn.find('.btn-text').hide();
        $btn.find('.btn-loading').show();
        
        $.ajax({
            url: wecoopRA.ajaxurl,
            type: 'POST',
            data: {
                action: 'wecoop_register',
                nonce: wecoopRA.nonce,
                nome: $('#nome').val(),
                cognome: $('#cognome').val(),
                email: $('#email').val(),
                telefono: $('#telefono').val(),
                password: password
            },
            success: function(response) {
                if (response.success) {
                    $message.removeClass('error').addClass('success')
                        .html(response.data.message)
                        .slideDown();
                    
                    // Redirect
                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 1500);
                } else {
                    $message.removeClass('success').addClass('error')
                        .html(response.data.message)
                        .slideDown();
                    
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').show();
                    $btn.find('.btn-loading').hide();
                }
            },
            error: function() {
                $message.removeClass('success').addClass('error')
                    .html('Errore di connessione. Riprova.')
                    .slideDown();
                
                $btn.prop('disabled', false);
                $btn.find('.btn-text').show();
                $btn.find('.btn-loading').hide();
            }
        });
    });
    
    /**
     * Logout Button
     */
    $('#wecoop-logout-btn').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Sei sicuro di voler uscire?')) {
            return;
        }
        
        $.ajax({
            url: wecoopRA.ajaxurl,
            type: 'POST',
            data: {
                action: 'wecoop_logout',
                nonce: wecoopRA.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect;
                }
            }
        });
    });
    
    /**
     * Profile Form
     */
    $('#wecoop-profile-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $message = $('#wecoop-profile-message');
        
        $btn.prop('disabled', true);
        $btn.find('.btn-text').hide();
        $btn.find('.btn-loading').show();
        
        $.ajax({
            url: wecoopRA.ajaxurl,
            type: 'POST',
            data: {
                action: 'wecoop_update_profile',
                nonce: wecoopRA.nonce,
                nome: $('#profile_nome').val(),
                cognome: $('#profile_cognome').val(),
                email: $('#profile_email').val(),
                telefono: $('#profile_telefono').val(),
                current_password: $('#current_password').val(),
                new_password: $('#new_password').val()
            },
            success: function(response) {
                if (response.success) {
                    $message.removeClass('error').addClass('success')
                        .html(response.data.message)
                        .slideDown();
                    
                    // Reset password fields
                    $('#current_password, #new_password').val('');
                } else {
                    $message.removeClass('success').addClass('error')
                        .html(response.data.message)
                        .slideDown();
                }
                
                $btn.prop('disabled', false);
                $btn.find('.btn-text').show();
                $btn.find('.btn-loading').hide();
            },
            error: function() {
                $message.removeClass('success').addClass('error')
                    .html('Errore di connessione. Riprova.')
                    .slideDown();
                
                $btn.prop('disabled', false);
                $btn.find('.btn-text').show();
                $btn.find('.btn-loading').hide();
            }
        });
    });
});
