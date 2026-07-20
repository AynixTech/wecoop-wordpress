<?php
/**
 * User Meta Handler
 */

if (!defined('ABSPATH')) exit;

class WeCoop_User_Meta {
    public static function init() {
        add_action('show_user_profile', [__CLASS__, 'render_login_contact_fields']);
        add_action('edit_user_profile', [__CLASS__, 'render_login_contact_fields']);
        add_action('personal_options_update', [__CLASS__, 'save_login_contact_fields']);
        add_action('edit_user_profile_update', [__CLASS__, 'save_login_contact_fields']);
        add_filter('authenticate', [__CLASS__, 'authenticate_phone_number'], 15, 3);
    }

    /**
     * Aggiunge i dati telefonici necessari all'accesso nella pagina utente nativa di WordPress.
     * La password rimane nel relativo pannello nativo di WordPress e non viene mai salvata in meta.
     */
    public static function render_login_contact_fields($profile_user) {
        if (!$profile_user instanceof WP_User || !current_user_can('edit_user', $profile_user->ID)) {
            return;
        }

        $prefix = (string) get_user_meta($profile_user->ID, 'prefix', true);
        $telefono = (string) get_user_meta($profile_user->ID, 'telefono', true);
        $telefono_completo = self::build_phone_complete([
            'prefix' => $prefix,
            'telefono' => $telefono,
        ]);
        $username_from_phone = ltrim($telefono_completo, '+');
        $is_phone_login = $username_from_phone !== '' && (
            $profile_user->user_login === $username_from_phone ||
            get_user_meta($profile_user->ID, 'wecoop_phone_login_enabled', true) === '1'
        );
        $notice_key = 'wecoop_profile_notice_' . get_current_user_id();
        $notice = get_transient($notice_key);
        delete_transient($notice_key);

        wp_nonce_field('wecoop_save_login_contact_fields', 'wecoop_login_contact_nonce');
        ?>
        <h2><?php esc_html_e('Accesso WeCoop', 'wecoop-core'); ?></h2>
        <?php if (is_array($notice) && !empty($notice['message'])) : ?>
            <div class="notice notice-<?php echo esc_attr($notice['type'] ?? 'error'); ?> inline"><p><?php echo esc_html($notice['message']); ?></p></div>
        <?php endif; ?>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="wecoop_prefix"><?php esc_html_e('Prefisso internazionale', 'wecoop-core'); ?></label></th>
                <td>
                    <input name="wecoop_prefix" type="text" id="wecoop_prefix" value="<?php echo esc_attr($prefix); ?>" class="regular-text" inputmode="numeric" pattern="\+?[0-9]{1,4}" placeholder="+39" />
                    <p class="description"><?php esc_html_e('Esempio: +39.', 'wecoop-core'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="wecoop_telefono"><?php esc_html_e('Numero di telefono', 'wecoop-core'); ?></label></th>
                <td>
                    <input name="wecoop_telefono" type="tel" id="wecoop_telefono" value="<?php echo esc_attr($telefono); ?>" class="regular-text" inputmode="tel" placeholder="3331234567" />
                    <p class="description"><?php esc_html_e('Inserire il numero senza prefisso internazionale.', 'wecoop-core'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Username di accesso', 'wecoop-core'); ?></th>
                <td>
                    <label for="wecoop_sync_login">
                        <input name="wecoop_sync_login" type="checkbox" id="wecoop_sync_login" value="1" <?php checked($is_phone_login); ?> />
                        <?php esc_html_e('Usa prefisso e telefono come username di accesso.', 'wecoop-core'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Lo username sarà il numero completo senza il simbolo +. Il numero completo può essere usato per accedere anche se l’account ha uno username storico. La password si modifica nella sezione “Password” di questa stessa pagina.', 'wecoop-core'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /** Salva i campi aggiunti alla pagina profilo di WordPress. */
    public static function save_login_contact_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        if (
            !isset($_POST['wecoop_login_contact_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wecoop_login_contact_nonce'])), 'wecoop_save_login_contact_fields')
        ) {
            return;
        }

        if (!isset($_POST['wecoop_prefix'], $_POST['wecoop_telefono'])) {
            return;
        }

        $prefix = preg_replace('/[^0-9]/', '', (string) wp_unslash($_POST['wecoop_prefix']));
        $telefono = preg_replace('/[^0-9]/', '', (string) wp_unslash($_POST['wecoop_telefono']));

        // I due valori sono opzionali solo se entrambi vuoti; non salviamo un contatto incompleto.
        if (($prefix === '') !== ($telefono === '') || strlen($prefix) > 4 || strlen($telefono) > 15) {
            self::set_profile_notice('error', __('Inserire sia il prefisso sia il numero di telefono in un formato valido.', 'wecoop-core'));
            return;
        }

        if ($prefix === '' && $telefono === '') {
            delete_user_meta($user_id, 'prefix');
            delete_user_meta($user_id, 'telefono');
            delete_user_meta($user_id, 'telefono_completo');
            delete_user_meta($user_id, 'wecoop_phone_login_enabled');
            return;
        }

        $telefono_completo = self::build_phone_complete([
            'prefix' => $prefix,
            'telefono' => $telefono,
        ]);

        update_user_meta($user_id, 'prefix', $prefix);
        update_user_meta($user_id, 'telefono', $telefono);
        update_user_meta($user_id, 'telefono_completo', $telefono_completo);

        if (empty($_POST['wecoop_sync_login'])) {
            delete_user_meta($user_id, 'wecoop_phone_login_enabled');
            return;
        }

        $username = ltrim($telefono_completo, '+');
        $user = get_userdata($user_id);
        $existing_user_id = username_exists($username);

        if (!$user) {
            return;
        }

        if ($user->user_login === $username) {
            update_user_meta($user_id, 'wecoop_phone_login_enabled', '1');
            return;
        }

        if ($existing_user_id && (int) $existing_user_id !== (int) $user_id) {
            self::set_profile_notice('error', __('Il numero indicato è già usato come username di accesso da un altro account.', 'wecoop-core'));
            return;
        }

        $result = wp_update_user([
            'ID' => $user_id,
            'user_login' => $username,
        ]);

        if (is_wp_error($result)) {
            self::set_profile_notice('error', __('Non è stato possibile aggiornare lo username di accesso.', 'wecoop-core'));
            return;
        }

        update_user_meta($user_id, 'wecoop_phone_login_enabled', '1');
    }

    /**
     * Consente il login con il numero completo, senza esporre o modificare la password.
     * Gli utenti creati prima dell'adozione dello username telefonico mantengono così l'accesso.
     */
    public static function authenticate_phone_number($user, $username, $password) {
        if ($user instanceof WP_User || is_wp_error($user) || $username === '' || $password === '') {
            return $user;
        }

        $phone_digits = preg_replace('/[^0-9]/', '', (string) $username);
        if ($phone_digits === '' || strlen($phone_digits) > 19) {
            return $user;
        }

        $users = get_users([
            'number' => 2,
            'fields' => 'all',
            'meta_query' => [
                [
                    'key' => 'telefono_completo',
                    'value' => ['+' . $phone_digits, $phone_digits],
                    'compare' => 'IN',
                ],
            ],
        ]);

        if (count($users) !== 1) {
            return $user;
        }

        $phone_user = $users[0];
        if (wp_check_password($password, $phone_user->user_pass, $phone_user->ID)) {
            return $phone_user;
        }

        return new WP_Error(
            'incorrect_password',
            __('Errore: la password inserita non è corretta.', 'wecoop-core')
        );
    }

    private static function set_profile_notice($type, $message) {
        set_transient(
            'wecoop_profile_notice_' . get_current_user_id(),
            [
                'type' => $type,
                'message' => $message,
            ],
            MINUTE_IN_SECONDS
        );
    }

    public static function get_schema() {
        return [
            'core' => [
                'first_name',
                'last_name',
                'display_name',
                'user_email',
                'user_pass',
            ],
            'profile' => [
                'nome',
                'cognome',
                'sesso',
                'data_nascita',
                'luogo_nascita',
                'codice_fiscale',
                'nazionalita',
                'stato_civile',
                'telefono',
                'prefix',
                'telefono_completo',
                'indirizzo',
                'civico',
                'citta',
                'cap',
                'provincia',
                'nazione',
                'cu_azienda_codice_fiscale',
                'cu_azienda_denominazione',
                'cu_azienda_indirizzo',
                'cu_azienda_cap',
                'cu_azienda_citta',
                'cu_azienda_provincia',
                'cu_azienda_codice_attivita',
                'cu_data_inizio_rapporto',
                'cu_data_fine_rapporto',
                'cu_redditi_lavoro_dipendente',
                'cu_redditi_assimilati',
                'cu_redditi_pensione',
                'cu_ritenute_irpef',
                'cu_addizionale_regionale',
                'cu_addizionale_comunale',
                'cu_contributi_previdenziali',
                'cu_trattamento_integrativo',
                'numero_figli',
                'figli_minori',
                'figli_minori_numero',
                'persone_a_carico',
                'tipo_lavoro',
                'contratto',
                'settore',
                'anni_lavoro',
                'reddito_annuo',
                'reddito_mensile',
                'altri_redditi',
                'prestiti_attivi',
                'rate_mensili',
                'ritardi_pagamenti',
                'tipo_abitazione',
                'canone_affitto_mensile',
                'spese_condominiali_mensili',
                'numero_conviventi',
                'reddito_netto_mensile_dichiarato',
                'entrate_ricorrenti_mensili',
                'spese_abitative_mensili',
                'altre_spese_ricorrenti_mensili',
                'disponibilita_mensile_dichiarata',
                'fonte_reddito',
                'anno_riferimento_reddito',
                'tipologie_impegni_finanziari',
                'data_fine_impegni_finanziari',
                'cessione_quinto',
                'assegno_mantenimento',
                'assegno_mantenimento_mensile',
                'fascia_risparmi',
                'garanzie_disponibili',
                'stato_verifica_finanziaria',
                'data_verifica_finanziaria',
                'note_verifica_finanziaria',
                'consenso_dati_finanziari',
                'doc_carta_identita',
                'doc_codice_fiscale',
                'doc_cu',
                'doc_dichiarazione_redditi',
                'categoria_profilazione',
                'capacita_economica',
                'interesse',
                'professione',
                'paese_provenienza',
                'note_dataentry',
                'wecoop_dataentry_created_at',
                'wecoop_dataentry_created_by',
                'wecoop_dataentry_updated_at',
                'profilo_completo',
                'profilo_percentuale',
                'campi_mancanti',
                'is_socio',
            ],
        ];
    }

    public static function get_required_profile_fields() {
        return [
            'nome',
            'cognome',
            'email',
            'telefono',
            'citta',
            'indirizzo',
            'codice_fiscale',
            'data_nascita',
            'nazionalita',
        ];
    }

    /**
     * Campi che concorrono al calcolo della percentuale di completamento del profilo.
     * Include i campi obbligatori piu' quelli anagrafici/contatto/lavoro rilevanti.
     */
    public static function get_completion_fields() {
        return [
            // Anagrafica
            'nome',
            'cognome',
            'email',
            'sesso',
            'data_nascita',
            'luogo_nascita',
            'codice_fiscale',
            'nazionalita',
            'stato_civile',
            // Contatti
            'telefono',
            'indirizzo',
            'civico',
            'citta',
            'cap',
            'provincia',
            'nazione',
            // Lavoro / reddito
            'tipo_lavoro',
            'professione',
            'reddito_mensile',
        ];
    }

    /**
     * Calcola la percentuale di completamento (0-100) dato un array di valori indicizzati
     * con le stesse chiavi di get_completion_fields().
     */
    public static function calculate_completion_percent(array $values) {
        $fields = self::get_completion_fields();
        $total = count($fields);

        if ($total === 0) {
            return 0;
        }

        $filled = 0;
        foreach ($fields as $field) {
            if (trim((string) ($values[$field] ?? '')) !== '') {
                $filled++;
            }
        }

        return (int) round(($filled / $total) * 100);
    }

    public static function normalize_boolean($value) {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'si', 'sì', 'on'], true) ? '1' : '0';
    }

    public static function normalize_text($value) {
        return sanitize_text_field((string) $value);
    }

    public static function normalize_textarea($value) {
        return sanitize_textarea_field((string) $value);
    }

    public static function normalize_money($value) {
        $value = str_replace(['.', ' '], '', (string) $value);
        $value = str_replace(',', '.', $value);

        return sanitize_text_field($value);
    }

    public static function build_display_name($first_name, $last_name) {
        $first_name = trim((string) $first_name);
        $last_name = trim((string) $last_name);

        return trim($first_name . ' ' . $last_name);
    }

    public static function build_phone_complete(array $data) {
        $prefix = trim((string) ($data['prefix'] ?? ''));
        $telefono = trim((string) ($data['telefono'] ?? ''));

        if ($telefono === '') {
            return '';
        }

        if ($prefix === '') {
            return $telefono;
        }

        $prefix = ltrim($prefix, '+');

        return '+' . $prefix . $telefono;
    }

    public static function get_user_profile_data($user_id) {
        $user = get_userdata($user_id);

        if (!$user) {
            return [];
        }

        $profile = [
            'first_name' => (string) $user->first_name,
            'last_name' => (string) $user->last_name,
            'display_name' => (string) $user->display_name,
            'user_email' => (string) $user->user_email,
            'user_login' => (string) $user->user_login,
        ];

        foreach (self::get_schema()['profile'] as $meta_key) {
            $profile[$meta_key] = get_user_meta($user_id, $meta_key, true);
        }

        return $profile;
    }

    public static function save_user_profile($user_id, array $data) {
        $user = get_userdata($user_id);
        if (!$user) {
            return new WP_Error('user_not_found', 'Utente non trovato');
        }

        $first_name = trim((string) ($data['first_name'] ?? $data['nome'] ?? $user->first_name));
        $last_name = trim((string) ($data['last_name'] ?? $data['cognome'] ?? $user->last_name));
        $display_name = trim((string) ($data['display_name'] ?? ''));
        $email = trim((string) ($data['user_email'] ?? $data['email'] ?? $user->user_email));
        $user_pass = trim((string) ($data['user_pass'] ?? ''));

        $update_data = ['ID' => $user_id];

        if ($email !== '' && is_email($email)) {
            $update_data['user_email'] = sanitize_email($email);
        }

        if ($first_name !== '') {
            $update_data['first_name'] = self::normalize_text($first_name);
        }

        if ($last_name !== '') {
            $update_data['last_name'] = self::normalize_text($last_name);
        }

        $computed_display_name = self::build_display_name($first_name, $last_name);
        if ($display_name === '' && $computed_display_name !== '') {
            $display_name = $computed_display_name;
        }

        if ($display_name !== '') {
            $update_data['display_name'] = self::normalize_text($display_name);
        }

        if ($user_pass !== '') {
            $update_data['user_pass'] = $user_pass;
        }

        $result = wp_update_user($update_data);
        if (is_wp_error($result)) {
            return $result;
        }

        $text_fields = [
            'nome', 'cognome', 'sesso', 'data_nascita', 'luogo_nascita', 'codice_fiscale',
            'nazionalita', 'stato_civile', 'telefono', 'prefix', 'telefono_completo',
            'indirizzo', 'civico', 'citta', 'cap', 'provincia', 'nazione', 'cu_azienda_codice_fiscale',
            'cu_azienda_denominazione', 'cu_azienda_indirizzo', 'cu_azienda_cap', 'cu_azienda_citta',
            'cu_azienda_provincia', 'cu_azienda_codice_attivita', 'cu_data_inizio_rapporto',
            'cu_data_fine_rapporto', 'cu_redditi_lavoro_dipendente', 'cu_redditi_assimilati',
            'cu_redditi_pensione', 'cu_ritenute_irpef', 'cu_addizionale_regionale',
            'cu_addizionale_comunale', 'cu_contributi_previdenziali', 'cu_trattamento_integrativo', 'numero_figli',
            'figli_minori', 'figli_minori_numero', 'persone_a_carico', 'tipo_lavoro',
            'contratto', 'settore', 'anni_lavoro', 'reddito_annuo', 'reddito_mensile',
            'rate_mensili', 'tipo_abitazione', 'canone_affitto_mensile', 'spese_condominiali_mensili',
            'numero_conviventi', 'reddito_netto_mensile_dichiarato', 'entrate_ricorrenti_mensili',
            'spese_abitative_mensili', 'altre_spese_ricorrenti_mensili', 'disponibilita_mensile_dichiarata',
            'fonte_reddito', 'anno_riferimento_reddito', 'tipologie_impegni_finanziari',
            'data_fine_impegni_finanziari', 'assegno_mantenimento_mensile', 'fascia_risparmi',
            'garanzie_disponibili', 'stato_verifica_finanziaria', 'data_verifica_finanziaria',
            'note_verifica_finanziaria', 'categoria_profilazione', 'capacita_economica', 'interesse',
            'professione', 'paese_provenienza', 'note_dataentry',
        ];

        $boolean_fields = [
            'altri_redditi',
            'prestiti_attivi',
            'cessione_quinto',
            'assegno_mantenimento',
            'consenso_dati_finanziari',
            'doc_carta_identita',
            'doc_codice_fiscale',
            'doc_cu',
            'doc_dichiarazione_redditi',
            'is_socio',
        ];

        foreach ($text_fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];
            if (is_array($value)) {
                continue;
            }

            if ($field === 'codice_fiscale') {
                $value = strtoupper(self::normalize_text($value));
            } elseif (in_array($field, ['provincia', 'cu_azienda_provincia'], true)) {
                $value = strtoupper(self::normalize_text($value));
            } elseif (in_array($field, ['numero_figli', 'figli_minori_numero', 'persone_a_carico', 'anni_lavoro', 'numero_conviventi', 'anno_riferimento_reddito'], true)) {
                $value = self::normalize_text($value);
            } elseif (in_array($field, ['reddito_annuo', 'reddito_mensile', 'rate_mensili', 'canone_affitto_mensile', 'spese_condominiali_mensili', 'reddito_netto_mensile_dichiarato', 'entrate_ricorrenti_mensili', 'spese_abitative_mensili', 'altre_spese_ricorrenti_mensili', 'disponibilita_mensile_dichiarata', 'assegno_mantenimento_mensile', 'cu_redditi_lavoro_dipendente', 'cu_redditi_assimilati', 'cu_redditi_pensione', 'cu_ritenute_irpef', 'cu_addizionale_regionale', 'cu_addizionale_comunale', 'cu_contributi_previdenziali', 'cu_trattamento_integrativo'], true)) {
                $value = self::normalize_money($value);
            } elseif ($field === 'note_dataentry') {
                $value = self::normalize_textarea($value);
            } else {
                $value = self::normalize_text($value);
            }

            update_user_meta($user_id, $field, $value);
        }

        foreach ($boolean_fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            update_user_meta($user_id, $field, self::normalize_boolean($data[$field]));
        }

        $telefono_completo = self::build_phone_complete($data);
        if ($telefono_completo !== '') {
            update_user_meta($user_id, 'telefono_completo', $telefono_completo);
        }

        $required_values = [
            'nome' => get_user_meta($user_id, 'nome', true),
            'cognome' => get_user_meta($user_id, 'cognome', true),
            'email' => $update_data['user_email'] ?? $user->user_email,
            'telefono' => get_user_meta($user_id, 'telefono', true),
            'citta' => get_user_meta($user_id, 'citta', true),
            'indirizzo' => get_user_meta($user_id, 'indirizzo', true),
            'codice_fiscale' => get_user_meta($user_id, 'codice_fiscale', true),
            'data_nascita' => get_user_meta($user_id, 'data_nascita', true),
            'nazionalita' => get_user_meta($user_id, 'nazionalita', true),
        ];

        if ($required_values['nome'] === '' && isset($update_data['first_name'])) {
            $required_values['nome'] = $update_data['first_name'];
        }
        if ($required_values['cognome'] === '' && isset($update_data['last_name'])) {
            $required_values['cognome'] = $update_data['last_name'];
        }

        $profilo_completo = true;
        $campi_mancanti = [];

        foreach ($required_values as $field => $value) {
            if (trim((string) $value) === '') {
                $profilo_completo = false;
                $campi_mancanti[] = $field;
            }
        }

        update_user_meta($user_id, 'profilo_completo', $profilo_completo);
        update_user_meta($user_id, 'campi_mancanti', $campi_mancanti);

        // Percentuale di completamento sui campi rilevanti del profilo.
        $completion_values = [];
        foreach (self::get_completion_fields() as $cf) {
            if ($cf === 'email') {
                $completion_values['email'] = $required_values['email'] ?? get_user_meta($user_id, 'user_email', true);
                continue;
            }
            $completion_values[$cf] = get_user_meta($user_id, $cf, true);
        }
        if (trim((string) ($completion_values['nome'] ?? '')) === '' && isset($update_data['first_name'])) {
            $completion_values['nome'] = $update_data['first_name'];
        }
        if (trim((string) ($completion_values['cognome'] ?? '')) === '' && isset($update_data['last_name'])) {
            $completion_values['cognome'] = $update_data['last_name'];
        }
        if (trim((string) ($completion_values['email'] ?? '')) === '') {
            $completion_values['email'] = $update_data['user_email'] ?? $user->user_email;
        }

        $profilo_percentuale = self::calculate_completion_percent($completion_values);
        update_user_meta($user_id, 'profilo_percentuale', $profilo_percentuale);

        if (get_user_meta($user_id, 'nome', true) === '' && isset($update_data['first_name']) && $update_data['first_name'] !== '') {
            update_user_meta($user_id, 'nome', $update_data['first_name']);
        }
        if (get_user_meta($user_id, 'cognome', true) === '' && isset($update_data['last_name']) && $update_data['last_name'] !== '') {
            update_user_meta($user_id, 'cognome', $update_data['last_name']);
        }

        return [
            'user_id' => $user_id,
            'profilo_completo' => $profilo_completo,
            'profilo_percentuale' => $profilo_percentuale,
            'campi_mancanti' => $campi_mancanti,
            'user_profile' => self::get_user_profile_data($user_id),
        ];
    }
}
