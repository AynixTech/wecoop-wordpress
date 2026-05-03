<?php
/**
 * Generatore Ricevute PDF
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Ricevuta_PDF {

    private const VAT_RATE = 0.22;

    /**
     * Estrae l'importo corretto dal record pagamento.
     */
    private static function get_payment_amount($payment) {
        if (isset($payment->importo) && $payment->importo !== null && $payment->importo !== '') {
            return (float) $payment->importo;
        }

        if (isset($payment->amount) && $payment->amount !== null && $payment->amount !== '') {
            return (float) $payment->amount;
        }

        return 0.0;
    }

    /**
     * Rende leggibile il metodo di pagamento per la ricevuta.
     */
    private static function get_payment_method_details($payment) {
        $raw_method = '';

        if (isset($payment->metodo_pagamento) && $payment->metodo_pagamento) {
            $raw_method = strtolower((string) $payment->metodo_pagamento);
        } elseif (isset($payment->payment_method) && $payment->payment_method) {
            $raw_method = strtolower((string) $payment->payment_method);
        }

        $details_map = [
            'stripe' => 'Stripe',
            'card' => 'Carta di credito',
            'carta' => 'Carta di credito',
            'carta_di_credito' => 'Carta di credito',
            'credit_card' => 'Carta di credito',
            'sepa_debit' => 'Addebito SEPA',
            'bank_transfer' => 'Bonifico bancario',
            'bonifico' => 'Bonifico bancario',
            'bonifico_bancario' => 'Bonifico bancario',
            'paypal' => 'PayPal',
            'app' => 'App WeCoop',
        ];

        $detail = $details_map[$raw_method] ?? ucfirst(str_replace('_', ' ', $raw_method));
        if ($detail === '') {
            $detail = 'Canale non specificato';
        }

        if ($raw_method === 'stripe') {
            $detail = 'Stripe - KINTI SRL';
        }

        return [
            'label' => 'Pagamento',
            'detail' => $detail,
            'is_bank_transfer' => in_array($raw_method, ['bank_transfer', 'bonifico', 'bonifico_bancario', 'sepa_debit'], true),
            'is_card' => in_array($raw_method, ['stripe', 'card', 'carta', 'carta_di_credito', 'credit_card'], true),
        ];
    }

    private static function get_company_details() {
        $default_registered_office = 'Via San Martino di Tours, 2 - 20900 Monza (MB)';
        $default_office = 'Via Populonia, 8 - 20159 Milano (MI)';

        return [
            'name' => get_option('wecoop_kinti_company_name', 'KINTI SRL'),
            'registered_office' => get_option('wecoop_kinti_registered_office', $default_registered_office),
            'office' => get_option('wecoop_kinti_office', $default_office),
            'address' => get_option('wecoop_kinti_address', $default_office),
            'vat' => get_option('wecoop_kinti_vat', '12201260960'),
            'email' => get_option('wecoop_kinti_email', 'info@kinti.it'),
            'phone' => get_option('wecoop_kinti_phone', '+39 331 393 5170'),
            'sdi' => get_option('wecoop_kinti_sdi', 'T9K4ZHO'),
            'pec' => get_option('wecoop_kinti_pec', '{{kinti_pec}}'),
        ];
    }

    private static function format_address(array $parts) {
        $filtered = array_filter(array_map(static function ($value) {
            return trim((string) $value);
        }, $parts));

        return implode(', ', $filtered);
    }

    private static function calculate_vat_breakdown($gross_amount) {
        $total = round((float) $gross_amount, 2);
        $net = round($total / (1 + self::VAT_RATE), 2);
        $vat = round($total - $net, 2);

        return [
            'net' => $net,
            'vat' => $vat,
            'total' => $total,
            'net_formatted' => number_format($net, 2, ',', '.'),
            'vat_formatted' => number_format($vat, 2, ',', '.'),
            'total_formatted' => number_format($total, 2, ',', '.'),
        ];
    }

    private static function maybe_send_to_sdi(array $invoice_data, array $pdf_result) {
        $payload = [
            'invoice' => $invoice_data,
            'pdf_url' => $pdf_result['url'] ?? '',
            'generated_at' => current_time('mysql'),
        ];

        do_action('wecoop_kinti_invoice_ready', $payload, $pdf_result);

        $endpoint = trim((string) get_option('wecoop_kinti_sdi_endpoint', ''));
        if ($endpoint === '') {
            error_log('[WECOOP FATTURA] Endpoint SDI non configurato, payload esposto via hook wecoop_kinti_invoice_ready');
            return;
        }

        $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        $token = trim((string) get_option('wecoop_kinti_sdi_token', ''));
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = wp_remote_post($endpoint, [
            'headers' => $headers,
            'body' => wp_json_encode($payload),
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            error_log('[WECOOP FATTURA] Errore invio SDI: ' . $response->get_error_message());
            return;
        }

        error_log('[WECOOP FATTURA] Payload SDI inviato. HTTP ' . wp_remote_retrieve_response_code($response));
        do_action('wecoop_kinti_invoice_sdi_response', $payload, $response);
    }
    
    /**
     * Genera ricevuta PDF per erogazione liberale
     */
    public static function generate_ricevuta($payment_id) {
        global $wpdb;
        
        error_log("[WECOOP FATTURA] Inizio generazione per payment_id: $payment_id");
        
        // Recupera dati pagamento
        $table = $wpdb->prefix . 'wecoop_pagamenti';
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $payment_id
        ));
        
        if (!$payment) {
            error_log("[WECOOP FATTURA] Pagamento non trovato");
            return [
                'success' => false,
                'message' => 'Pagamento non trovato'
            ];
        }
        
        error_log("[WECOOP FATTURA] Pagamento trovato - stato: {$payment->stato}");
        
        if (!in_array($payment->stato, ['paid', 'completed'])) {
            error_log("[WECOOP FATTURA] Stato non valido: {$payment->stato}");
            return [
                'success' => false,
                'message' => 'Pagamento non completato (stato: ' . $payment->stato . ')'
            ];
        }
        
        // Recupera dati richiesta
        $richiesta_id = $payment->richiesta_id;
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $servizio = get_post_meta($richiesta_id, 'servizio', true);
        $numero_pratica = get_post_meta($richiesta_id, 'numero_pratica', true);
        
        // Recupera dati utente
        $user = get_userdata($user_id);
        $nome = get_user_meta($user_id, 'nome', true);
        $cognome = get_user_meta($user_id, 'cognome', true);
        $indirizzo = get_user_meta($user_id, 'indirizzo', true);
        $cap = get_user_meta($user_id, 'cap', true);
        $comune = get_user_meta($user_id, 'comune', true);
        if (empty($comune)) {
            $comune = get_user_meta($user_id, 'citta', true);
        }
        $provincia = get_user_meta($user_id, 'provincia', true);
        $codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
        if (empty($codice_fiscale)) {
            $codice_fiscale = get_user_meta($user_id, 'partita_iva', true);
        }
        
        $payment_method = self::get_payment_method_details($payment);
        $company = self::get_company_details();
        $paid_timestamp = !empty($payment->paid_at) ? strtotime($payment->paid_at) : current_time('timestamp');
        $anno = date('Y', $paid_timestamp);
        $numero_ricevuta = $payment_id . '/' . $anno;
        $data_pagamento = date('d/m/Y', $paid_timestamp);

        $importo = self::get_payment_amount($payment);
        $amounts = self::calculate_vat_breakdown($importo);
        $customer_name = trim($nome . ' ' . $cognome);
        if ($customer_name === '') {
            $customer_name = $user ? $user->display_name : 'Cliente';
        }

        $invoice_data = [
            'invoice_number' => $numero_ricevuta,
            'invoice_date' => $data_pagamento,
            'company_name' => $company['name'],
            'company_registered_office' => $company['registered_office'],
            'company_office' => $company['office'],
            'company_address' => $company['address'],
            'company_vat' => $company['vat'],
            'company_email' => $company['email'],
            'company_phone' => $company['phone'],
            'company_sdi' => $company['sdi'],
            'company_pec' => $company['pec'],
            'customer_name' => $customer_name,
            'customer_address' => self::format_address([$indirizzo, $cap, $comune, $provincia]),
            'customer_tax_id' => $codice_fiscale ?: '{{customer_tax_id}}',
            'customer_email' => $user ? $user->user_email : '',
            'service_name' => $servizio ?: 'Servizio WECOOP',
            'metodo_pagamento' => $payment_method['label'],
            'metodo_pagamento_dettaglio' => $payment_method['detail'],
            'transaction_id' => $payment->transaction_id,
            'numero_pratica' => $numero_pratica,
            'importo_imponibile' => $amounts['net_formatted'],
            'importo_iva' => $amounts['vat_formatted'],
            'importo_totale' => $amounts['total_formatted'],
            'vat_rate' => (int) round(self::VAT_RATE * 100),
        ];

        $html = self::genera_html_ricevuta($invoice_data);
        
        error_log("[WECOOP FATTURA] Generazione HTML completata");
        
        $result = self::html_to_pdf($html, "Fattura_{$numero_ricevuta}");
        
        error_log("[WECOOP FATTURA] Risultato html_to_pdf: " . json_encode($result));
        
        if (!$result['success']) {
            return [
                'success' => false,
                'message' => isset($result['message']) ? $result['message'] : 'Errore durante la generazione del PDF'
            ];
        }
        
        error_log("[WECOOP FATTURA] PDF generato: {$result['url']}");
        
        // Salva URL ricevuta nel database
        $wpdb->update(
            $table,
            ['receipt_url' => $result['url']],
            ['id' => $payment_id],
            ['%s'],
            ['%d']
        );
        
        error_log("[WECOOP FATTURA] URL salvato nel database");

        self::maybe_send_to_sdi($invoice_data, $result);
        
        return [
            'success' => true,
            'message' => 'Fattura generata con successo',
            'receipt_url' => $result['url'],
            'filepath' => $result['filepath']
        ];
    }
    
    /**
     * Genera HTML ricevuta
     */
    private static function genera_html_ricevuta($data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #202124; }
                .header { margin-bottom: 24px; }
                .header h1 { font-size: 24pt; margin: 0; letter-spacing: 1px; }
                .grid { width: 100%; margin-bottom: 20px; }
                .card { border: 1px solid #d9d9d9; padding: 14px; border-radius: 8px; vertical-align: top; }
                .section-title { font-size: 11pt; font-weight: bold; text-transform: uppercase; color: #4a4a4a; margin-bottom: 8px; }
                .muted { color: #666; }
                .legal-note { background: #f6f6f6; border-left: 4px solid #1b8f5a; padding: 12px 14px; margin: 20px 0; }
                .amount-box { background: #faf7ef; border: 1px solid #eadfca; padding: 14px; margin-top: 18px; }
                .total { font-size: 16pt; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; }
                td, th { padding: 7px 6px; text-align: left; border-bottom: 1px solid #eee; }
                .right { text-align: right; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>FATTURA</h1>
            </div>

            <table class="grid">
                <tr>
                    <td style="width: 50%; padding-right: 10px;">
                        <div class="card">
                            <div class="section-title"><?php echo esc_html($data['company_name']); ?></div>
                            <div>Sede legale: <?php echo esc_html($data['company_registered_office']); ?></div>
                            <div>Ufficio: <?php echo esc_html($data['company_office']); ?></div>
                            <div>CF/P.IVA: <?php echo esc_html($data['company_vat']); ?></div>
                            <div>Email: <?php echo esc_html($data['company_email']); ?></div>
                            <?php if (!empty($data['company_phone'])): ?>
                                <div>Tel: <?php echo esc_html($data['company_phone']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($data['company_sdi'])): ?>
                                <div>SDI: <?php echo esc_html($data['company_sdi']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($data['company_pec'])): ?>
                                <div>PEC: <?php echo esc_html($data['company_pec']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 10px;">
                        <div class="card">
                            <div class="section-title">Cliente</div>
                            <div><?php echo esc_html($data['customer_name']); ?></div>
                            <div><?php echo esc_html($data['customer_address']); ?></div>
                            <div>CF/P.IVA: <?php echo esc_html($data['customer_tax_id']); ?></div>
                            <?php if (!empty($data['customer_email'])): ?>
                                <div>Email: <?php echo esc_html($data['customer_email']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <th>Numero</th>
                    <th>Data</th>
                    <th>Pagamento</th>
                    <th>ID transazione</th>
                </tr>
                <tr>
                    <td><?php echo esc_html($data['invoice_number']); ?></td>
                    <td><?php echo esc_html($data['invoice_date']); ?></td>
                    <td><?php echo esc_html($data['metodo_pagamento_dettaglio']); ?></td>
                    <td><?php echo esc_html($data['transaction_id']); ?></td>
                </tr>
            </table>

            <div class="legal-note">
                <div class="section-title">Servizio</div>
                <div><?php echo esc_html($data['service_name']); ?></div>
                <div class="muted">Pratica: <?php echo esc_html($data['numero_pratica']); ?></div>
                <div style="margin-top: 8px;">Servizio erogato nell'ambito del progetto WECOOP.</div>
            </div>

            <table>
                <tr>
                    <th>Voce</th>
                    <th class="right">Importo</th>
                </tr>
                <tr>
                    <td>Imponibile</td>
                    <td class="right">€ <?php echo esc_html($data['importo_imponibile']); ?></td>
                </tr>
                <tr>
                    <td>IVA (<?php echo esc_html($data['vat_rate']); ?>%)</td>
                    <td class="right">€ <?php echo esc_html($data['importo_iva']); ?></td>
                </tr>
                <tr>
                    <td><strong>Totale</strong></td>
                    <td class="right"><strong>€ <?php echo esc_html($data['importo_totale']); ?></strong></td>
                </tr>
            </table>

            <div class="amount-box">
                <div class="section-title">Totale da pagare</div>
                <div class="total">€ <?php echo esc_html($data['importo_totale']); ?></div>
            </div>

            <div class="legal-note">
                <div><strong>Note</strong></div>
                <div>Servizio erogato nell'ambito del progetto WECOOP.</div>
                <div>La gestione economica e la fatturazione sono a cura di KINTI SRL (CF/P.IVA <?php echo esc_html($data['company_vat']); ?>, SDI <?php echo esc_html($data['company_sdi']); ?>, Email <?php echo esc_html($data['company_email']); ?>).</div>
                <div>Documento generato automaticamente.</div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Converte HTML in PDF usando mPDF
     */
    private static function html_to_pdf($html, $filename) {
        error_log("[WECOOP FATTURA] html_to_pdf chiamato per: $filename");
        
        // Carica mPDF dal vendor del plugin
        $autoload = dirname(WECOOP_SERVIZI_FILE) . '/vendor/autoload.php';
        error_log("[WECOOP FATTURA] Percorso autoload: $autoload");
        error_log("[WECOOP FATTURA] Autoload esiste: " . (file_exists($autoload) ? 'SI' : 'NO'));
        
        if (file_exists($autoload)) {
            require_once $autoload;
            error_log("[WECOOP FATTURA] Autoload caricato");
        }
        
        $mpdf_exists = class_exists('Mpdf\Mpdf');
        error_log("[WECOOP FATTURA] Classe Mpdf\\Mpdf esiste: " . ($mpdf_exists ? 'SI' : 'NO'));
        
        if (!$mpdf_exists) {
            return [
                'success' => false,
                'message' => 'Libreria mPDF non disponibile. Esegui "composer install" nella directory del plugin. Autoload: ' . $autoload
            ];
        }
        
        try {
            error_log("[WECOOP FATTURA] Creazione oggetto mPDF...");
            
            $mpdf = new Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_header' => 5,
                'margin_footer' => 5
            ]);
            
            error_log("[WECOOP FATTURA] Oggetto mPDF creato");
            
            $mpdf->SetTitle('Fattura Servizio WECOOP - ' . $filename);
            $mpdf->SetAuthor(self::get_company_details()['name']);
            $mpdf->SetDisplayMode('fullpage');
            
            error_log("[WECOOP FATTURA] Scrittura HTML...");
            $mpdf->WriteHTML($html);
            
            $upload_dir = wp_upload_dir();
            $ricevute_dir = $upload_dir['basedir'] . '/fatture-wecoop';
            
            error_log("[WECOOP FATTURA] Directory fatture: $ricevute_dir");
            
            if (!file_exists($ricevute_dir)) {
                error_log("[WECOOP FATTURA] Creazione directory fatture...");
                wp_mkdir_p($ricevute_dir);
            }
            
            $filepath = $ricevute_dir . '/' . sanitize_file_name($filename) . '.pdf';
            error_log("[WECOOP FATTURA] Salvataggio PDF in: $filepath");
            
            $mpdf->Output($filepath, 'F');
            
            error_log("[WECOOP FATTURA] PDF salvato con successo");
            
            $url = $upload_dir['baseurl'] . '/fatture-wecoop/' . sanitize_file_name($filename) . '.pdf';
            
            return [
                'success' => true,
                'filepath' => $filepath,
                'url' => $url
            ];
            
        } catch (Exception $e) {
            error_log("[WECOOP FATTURA] ERRORE EXCEPTION: " . $e->getMessage());
            error_log("[WECOOP FATTURA] Stack trace: " . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Errore generazione PDF: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Converte numero in lettere (italiano)
     */
    private static function numero_in_lettere($numero) {
        $numero = floatval($numero);
        $intero = floor($numero);
        $decimali = round(($numero - $intero) * 100);
        
        $unita = ['', 'uno', 'due', 'tre', 'quattro', 'cinque', 'sei', 'sette', 'otto', 'nove'];
        $decine = ['', 'dieci', 'venti', 'trenta', 'quaranta', 'cinquanta', 'sessanta', 'settanta', 'ottanta', 'novanta'];
        $teens = ['dieci', 'undici', 'dodici', 'tredici', 'quattordici', 'quindici', 'sedici', 'diciassette', 'diciotto', 'diciannove'];
        
        if ($intero === 0) {
            $lettere = 'zero';
        } elseif ($intero < 10) {
            $lettere = $unita[$intero];
        } elseif ($intero < 20) {
            $lettere = $teens[$intero - 10];
        } elseif ($intero < 100) {
            $dec = floor($intero / 10);
            $uni = $intero % 10;
            $lettere = $decine[$dec];
            if ($uni > 0) {
                if ($uni === 1 || $uni === 8) {
                    $lettere = substr($lettere, 0, -1) . $unita[$uni];
                } else {
                    $lettere .= $unita[$uni];
                }
            }
        } elseif ($intero < 1000) {
            $cent = floor($intero / 100);
            $resto = $intero % 100;
            if ($cent === 1) {
                $lettere = 'cento';
            } else {
                $lettere = $unita[$cent] . 'cento';
            }
            if ($resto > 0) {
                $lettere .= self::numero_in_lettere($resto);
            }
        } else {
            $lettere = number_format($intero, 0, ',', '.');
        }
        
        $result = ucfirst($lettere) . ' euro';
        if ($decimali > 0) {
            $result .= ' e ' . $decimali . ' centesimi';
        }
        
        return $result;
    }
    
    /**
     * Scarica ricevuta PDF
     */
    public static function download_ricevuta($payment_id) {
        $result = self::generate_ricevuta($payment_id);
        
        if (!$result['success']) {
            wp_die($result['message']);
        }
        
        if (file_exists($result['filepath'])) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($result['filepath']) . '"');
            header('Content-Length: ' . filesize($result['filepath']));
            readfile($result['filepath']);
            exit;
        }
    }
}
