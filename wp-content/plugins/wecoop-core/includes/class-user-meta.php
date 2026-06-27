<?php
/**
 * User Meta Handler
 */

if (!defined('ABSPATH')) exit;

class WeCoop_User_Meta {
    public static function init() {
        // Shared helpers only; no runtime hooks needed yet.
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
            'indirizzo', 'civico', 'citta', 'cap', 'provincia', 'nazione', 'numero_figli',
            'figli_minori', 'figli_minori_numero', 'persone_a_carico', 'tipo_lavoro',
            'contratto', 'settore', 'anni_lavoro', 'reddito_annuo', 'reddito_mensile',
            'rate_mensili', 'categoria_profilazione', 'capacita_economica', 'interesse',
            'professione', 'paese_provenienza', 'note_dataentry',
        ];

        $boolean_fields = [
            'altri_redditi',
            'prestiti_attivi',
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
            } elseif (in_array($field, ['provincia'], true)) {
                $value = strtoupper(self::normalize_text($value));
            } elseif (in_array($field, ['numero_figli', 'figli_minori_numero', 'persone_a_carico', 'anni_lavoro', 'rate_mensili'], true)) {
                $value = self::normalize_text($value);
            } elseif (in_array($field, ['reddito_annuo', 'reddito_mensile'], true)) {
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
