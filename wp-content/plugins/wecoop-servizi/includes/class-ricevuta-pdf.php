<?php
/**
 * Generatore Ricevute PDF
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Ricevuta_PDF {
    
    /**
     * Genera ricevuta PDF per erogazione liberale
     */
    public static function generate_ricevuta($payment_id) {
        global $wpdb;
        
        // Recupera dati pagamento
        $table = $wpdb->prefix . 'wecoop_pagamenti';
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $payment_id
        ));
        
        if (!$payment || !in_array($payment->stato, ['paid', 'completed'])) {
            return [
                'success' => false,
                'message' => 'Pagamento non trovato o non completato'
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
        $provincia = get_user_meta($user_id, 'provincia', true);
        $codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
        
        // Dati associazione (configurabili)
        $nome_associazione = get_option('wecoop_nome_associazione', 'WeCoop APS');
        $rappresentante_legale = get_option('wecoop_rappresentante_legale', 'Mario Rossi');
        $data_iscrizione_runts = get_option('wecoop_data_runts', '01/01/2023');
        
        // Metodo pagamento
        $metodo_map = [
            'card' => 'Carta di credito',
            'sepa_debit' => 'Bonifico SEPA',
            'bancontact' => 'Bancontact',
            'ideal' => 'iDEAL',
            'stripe' => 'Carta di credito'
        ];
        $metodo_pagamento = $metodo_map[$payment->payment_method] ?? 'Carta di credito';
        
        // Genera numero ricevuta
        $anno = date('Y', strtotime($payment->paid_at));
        $numero_ricevuta = $payment_id . '/' . $anno;
        
        // Data pagamento
        $data_pagamento = date('d/m/Y', strtotime($payment->paid_at));
        
        // Importo
        $importo_num = number_format($payment->amount, 2, ',', '.');
        $importo_lettere = self::numero_in_lettere($payment->amount);
        
        // Genera HTML ricevuta
        $html = self::genera_html_ricevuta([
            'numero_ricevuta' => $numero_ricevuta,
            'anno' => $anno,
            'data_pagamento' => $data_pagamento,
            'nome_associazione' => $nome_associazione,
            'rappresentante_legale' => $rappresentante_legale,
            'data_runts' => $data_iscrizione_runts,
            'importo_cifre' => $importo_num,
            'importo_lettere' => $importo_lettere,
            'metodo_pagamento' => $metodo_pagamento,
            'nominativo' => trim($nome . ' ' . $cognome),
            'indirizzo' => $indirizzo,
            'cap' => $cap,
            'comune' => $comune,
            'provincia' => $provincia,
            'codice_fiscale' => $codice_fiscale,
            'transaction_id' => $payment->transaction_id,
            'numero_pratica' => $numero_pratica,
            'servizio' => $servizio
        ]);
        
        // Genera PDF
        $result = self::html_to_pdf($html, "Ricevuta_{$numero_ricevuta}");
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'message' => $result->get_error_message()
            ];
        }
        
        if (!$result['success']) {
            return [
                'success' => false,
                'message' => 'Errore durante la generazione del PDF'
            ];
        }
        
        // Salva URL ricevuta nel database
        $wpdb->update(
            $table,
            ['receipt_url' => $result['url']],
            ['id' => $payment_id],
            ['%s'],
            ['%d']
        );
        
        return [
            'success' => true,
            'message' => 'Ricevuta generata con successo',
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
                body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { font-size: 16pt; margin: 10px 0; }
                .header h2 { font-size: 14pt; margin: 5px 0; font-weight: normal; }
                .section { margin: 20px 0; }
                .label { font-weight: bold; }
                .box { border: 1px solid #000; padding: 15px; margin: 15px 0; }
                .checkbox { display: inline-block; width: 15px; height: 15px; border: 1px solid #000; margin-right: 5px; text-align: center; }
                .checkbox.checked::before { content: '✓'; }
                .footer { margin-top: 50px; font-size: 10pt; line-height: 1.4; }
                .firma { margin-top: 60px; text-align: right; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 5px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>RICEVUTA PER EROGAZIONI LIBERALI A APS E ETS</h1>
                <h2>da persone fisiche, aziende o enti</h2>
            </div>
            
            <div class="section">
                <table>
                    <tr>
                        <td style="width: 50%;">Data: <strong><?php echo esc_html($data['data_pagamento']); ?></strong></td>
                        <td style="width: 50%; text-align: right;">
                            Ricevuta N. <strong><?php echo esc_html($data['numero_ricevuta']); ?></strong> / Anno <strong><?php echo esc_html($data['anno']); ?></strong>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="section">
                <p>
                    L'Associazione <strong><?php echo esc_html($data['nome_associazione']); ?></strong> 
                    nella persona del suo rappresentante legale <strong><?php echo esc_html($data['rappresentante_legale']); ?></strong> 
                    dichiara di aver ricevuto quale erogazione liberale in data odierna 
                    Euro (in cifre) <strong>€ <?php echo esc_html($data['importo_cifre']); ?></strong> 
                    (in lettere) <strong><?php echo esc_html($data['importo_lettere']); ?></strong> tramite:
                </p>
            </div>
            
            <div class="box">
                <table>
                    <tr>
                        <td style="width: 50%;">
                            <span class="checkbox <?php echo in_array($data['metodo_pagamento'], ['Bonifico bancario', 'Bonifico SEPA']) ? 'checked' : ''; ?>"></span> Bonifico bancario<br>
                            <span class="checkbox <?php echo $data['metodo_pagamento'] === 'Versamento postale' ? 'checked' : ''; ?>"></span> Versamento con bollettino postale<br>
                            <span class="checkbox"></span> Assegno circolare<br>
                            <span class="checkbox"></span> Assegno bancario non trasferibile
                        </td>
                        <td style="width: 50%;">
                            <span class="checkbox <?php echo strpos($data['metodo_pagamento'], 'Carta') !== false ? 'checked' : ''; ?>"></span> Carta di credito<br>
                            <span class="checkbox"></span> Carta di debito<br>
                            <span class="checkbox"></span> Bonifico postale<br>
                            <span class="checkbox"></span> _________________________
                        </td>
                    </tr>
                </table>
                <p style="margin-top: 10px; font-size: 10pt; color: #666;">
                    <em>Riferimento: Pratica <?php echo esc_html($data['numero_pratica']); ?> - <?php echo esc_html($data['servizio']); ?></em><br>
                    <em>ID Transazione: <?php echo esc_html($data['transaction_id']); ?></em>
                </p>
            </div>
            
            <div class="section">
                <p class="label">Da:</p>
                <table>
                    <tr>
                        <td>Nominativo (o denominazione azienda o ente):</td>
                        <td><strong><?php echo esc_html($data['nominativo']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Indirizzo:</td>
                        <td><strong><?php echo esc_html($data['indirizzo']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Cap:</td>
                        <td><strong><?php echo esc_html($data['cap']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Comune:</td>
                        <td><strong><?php echo esc_html($data['comune']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Provincia:</td>
                        <td><strong><?php echo esc_html($data['provincia']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>C.F. o P. IVA:</td>
                        <td><strong><?php echo esc_html($data['codice_fiscale']); ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <div class="section">
                <p>
                    L'Associazione/Ente <strong><?php echo esc_html($data['nome_associazione']); ?></strong> 
                    è ente non commerciale iscritta nel <strong>RUNTS</strong> (Registro Unico Nazionale del Terzo Settore) 
                    di cui all'art. 45 del D.Lgs. 117/2017 e s.m.i. in data <strong><?php echo esc_html($data['data_runts']); ?></strong>.
                </p>
            </div>
            
            <div class="box" style="background-color: #f9f9f9;">
                <p style="margin: 5px 0;">
                    <strong>Per le persone fisiche:</strong> l'erogazione liberale è <strong>detraibile al 30%</strong> 
                    fino a € 30.000 (art. 83 co. 1 del D.Lgs. n. 117/2017) o, in alternativa, 
                    è <strong>deducibile nel limite del 10%</strong> reddito complessivo dichiarato 
                    (art. 83 co. 2 del D.Lgs. n. 117/2017).
                </p>
                <p style="margin: 5px 0;">
                    <strong>Per gli enti e le aziende:</strong> l'erogazione liberale è <strong>deducibile 
                    nel limite del 10%</strong> reddito complessivo dichiarato (art. 83 co. 2 del D.Lgs. n. 117/2017).
                </p>
            </div>
            
            <div class="section">
                <p>
                    Si rammenta che è condizione di deducibilità o detraibilità delle donazioni 
                    l'erogazione delle stesse tramite banca, posta o altro sistema tracciabile previsto dalle norme.
                </p>
            </div>
            
            <div class="section">
                <p>
                    <strong>La presente ricevuta è esente da imposta di bollo ex art. 82 co. 5 del D.Lgs. n. 117/2017.</strong>
                </p>
            </div>
            
            <div class="firma">
                <p><strong>FIRMA LEGALE RAPPRESENTANTE e TIMBRO</strong></p>
                <br><br>
                <p>____________________________________</p>
            </div>
            
            <div class="footer">
                <hr style="border: 0; border-top: 1px solid #ccc; margin: 20px 0;">
                <p style="font-size: 9pt; color: #666;">
                    I dati personali collegati alla donazione verranno trattati nel rispetto del GDPR 679/2016 e D.lgs 196/03. 
                    Per l'informativa completa si rimanda alla privacy policy sui canali istituzionali dell'ente.
                </p>
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
        // Carica mPDF dal vendor del plugin
        $autoload = dirname(WECOOP_SERVIZI_FILE) . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
        
        if (!class_exists('Mpdf\Mpdf')) {
            return [
                'success' => false,
                'message' => 'Libreria mPDF non disponibile. Esegui "composer install" nella directory del plugin.'
            ];
        }
        
        try {
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
            
            $mpdf->SetTitle('Ricevuta Erogazione Liberale - ' . $filename);
            $mpdf->SetAuthor('WeCoop APS');
            $mpdf->SetDisplayMode('fullpage');
            
            $mpdf->WriteHTML($html);
            
            // Salva in wp-content/uploads/ricevute/
            $upload_dir = wp_upload_dir();
            $ricevute_dir = $upload_dir['basedir'] . '/ricevute';
            
            if (!file_exists($ricevute_dir)) {
                wp_mkdir_p($ricevute_dir);
            }
            
            $filepath = $ricevute_dir . '/' . sanitize_file_name($filename) . '.pdf';
            $mpdf->Output($filepath, 'F');
            
            return [
                'success' => true,
                'filepath' => $filepath,
                'url' => $upload_dir['baseurl'] . '/ricevute/' . sanitize_file_name($filename) . '.pdf'
            ];
            
        } catch (Exception $e) {
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
        $result = self::genera_ricevuta($payment_id);
        
        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
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
