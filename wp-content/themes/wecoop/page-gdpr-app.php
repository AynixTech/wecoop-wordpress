<?php
/**
 * Template Name: GDPR App – Uso dei dati
 * Template Post Type: page
 *
 * Informativa completa sul trattamento dei dati personali nell'app WeCoop.
 * URL: /gdpr-app/
 *
 * @package WeCoop
 */

get_header();
wecoop_ws_page_shell_start( get_the_title() );

$_t      = 'translate_string';
$wl_slug = 'gdpr-app';
$delete_url = home_url( '/elimina-account/' );
$privacy_url = home_url( '/privacy-policy/' );

$wl_sections = [
    [
        'title'  => $_t( 'gdpr.s1.title', '1. Premessa e ambito di applicazione' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s1.p1', 'La presente Informativa descrive come <strong>WECOOP APS</strong> tratta i dati personali degli utenti dell\'applicazione mobile WeCoop (iOS e Android) e dei relativi servizi digitali collegati, ai sensi del Regolamento (UE) 2016/679 (GDPR) e del D.Lgs. 196/2003 come modificato dal D.Lgs. 101/2018.' ) ],
            [ 'p' => $_t( 'gdpr.s1.p2', 'Questa pagina integra la Privacy Policy generale del sito e si concentra specificamente sull\'uso dei dati nell\'app: registrazione, profilo, documenti, richieste di servizio, pagamenti, notifiche e funzionalità di supporto.' ) ],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s2.title', '2. Titolare del trattamento' ),
        'blocks' => [
            [ 'p' => '<strong>WECOOP APS</strong><br>Via Populonia 8, 20133 Milano (MI), Italia<br>Email: <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a><br>Tel: +39 351 511 2113' ],
            [ 'p' => $_t( 'gdpr.s2.p2', 'Per qualsiasi richiesta relativa al trattamento dei dati personali (accesso, rettifica, cancellazione, opposizione, portabilità, reclamo) scrivere a privacy@wecoop.org.' ) ],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s3.title', '3. Categorie di dati trattati nell\'app' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s3.p1', 'A seconda delle funzionalità utilizzate, l\'app può trattare le seguenti categorie di dati:' ) ],
            [ 'ul' => [
                '<strong>' . $_t( 'gdpr.s3.li1a', 'Dati anagrafici e di contatto:' ) . '</strong> ' . $_t( 'gdpr.s3.li1b', 'nome, cognome, prefisso e numero di telefono, email, nazionalità, data di nascita, codice fiscale (se fornito).' ),
                '<strong>' . $_t( 'gdpr.s3.li2a', 'Dati di account e autenticazione:' ) . '</strong> ' . $_t( 'gdpr.s3.li2b', 'credenziali di accesso, token di sessione, impostazioni biometriche locali sul dispositivo (se attivate dall\'utente), preferenze lingua.' ),
                '<strong>' . $_t( 'gdpr.s3.li3a', 'Dati di profilo e adesione:' ) . '</strong> ' . $_t( 'gdpr.s3.li3b', 'stato socio/utente, numero pratica o tessera, avatar, dati di completamento profilo.' ),
                '<strong>' . $_t( 'gdpr.s3.li4a', 'Documenti e contenuti caricati:' ) . '</strong> ' . $_t( 'gdpr.s3.li4b', 'documento di identità, documenti amministrativi, CV, file allegati alle pratiche, firme digitali e relative evidenze (es. OTP).' ),
                '<strong>' . $_t( 'gdpr.s3.li5a', 'Dati di servizio:' ) . '</strong> ' . $_t( 'gdpr.s3.li5b', 'richieste di orientamento, lavoro, studio, abitazione, eventi, annunci, messaggi di supporto e storico pratiche.' ),
                '<strong>' . $_t( 'gdpr.s3.li6a', 'Dati di pagamento:' ) . '</strong> ' . $_t( 'gdpr.s3.li6b', 'importo, stato pagamento, riferimenti transazione. I dati della carta sono trattati da Stripe e non sono memorizzati integralmente sui server WeCoop.' ),
                '<strong>' . $_t( 'gdpr.s3.li7a', 'Dati tecnici e di dispositivo:' ) . '</strong> ' . $_t( 'gdpr.s3.li7b', 'identificativi push (Firebase Cloud Messaging), tipo di dispositivo/sistema operativo, log tecnici e di sicurezza, dati di utilizzo necessari al funzionamento.' ),
                '<strong>' . $_t( 'gdpr.s3.li8a', 'Consensi:' ) . '</strong> ' . $_t( 'gdpr.s3.li8b', 'accettazione informativa GDPR/privacy, eventuali consensi specifici per servizi (es. candidature lavoro, condivisioni con partner).' ),
            ]],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s4.title', '4. Finalità del trattamento' ),
        'blocks' => [
            [ 'ul' => [
                '<strong>' . $_t( 'gdpr.s4.li1a', 'Creazione e gestione dell\'account:' ) . '</strong> ' . $_t( 'gdpr.s4.li1b', 'registrazione, login, recupero accesso, sicurezza dell\'account.' ),
                '<strong>' . $_t( 'gdpr.s4.li2a', 'Erogazione dei servizi WeCoop:' ) . '</strong> ' . $_t( 'gdpr.s4.li2b', 'gestione pratiche, documenti, eventi, annunci, orientamento e supporto territoriale.' ),
                '<strong>' . $_t( 'gdpr.s4.li3a', 'Comunicazioni operative:' ) . '</strong> ' . $_t( 'gdpr.s4.li3b', 'notifiche push, email o messaggi relativi allo stato delle richieste e agli aggiornamenti di servizio.' ),
                '<strong>' . $_t( 'gdpr.s4.li4a', 'Pagamenti e ricevute:' ) . '</strong> ' . $_t( 'gdpr.s4.li4b', 'gestione pagamenti per servizi richiedenti corrispettivo e conservazione delle evidenze richieste dalla legge.' ),
                '<strong>' . $_t( 'gdpr.s4.li5a', 'Adempimenti legali e associativi:' ) . '</strong> ' . $_t( 'gdpr.s4.li5b', 'obblighi di legge, documento unico / informative, gestione diritti GDPR, prevenzione abusi.' ),
                '<strong>' . $_t( 'gdpr.s4.li6a', 'Miglioramento del servizio:' ) . '</strong> ' . $_t( 'gdpr.s4.li6b', 'analisi aggregate e anonimizzate per migliorare stabilità, usabilità e qualità dell\'app.' ),
            ]],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s5.title', '5. Base giuridica' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s5.p1', 'Il trattamento avviene sulla base di una o più delle seguenti basi giuridiche (art. 6 GDPR):' ) ],
            [ 'ul' => [
                $_t( 'gdpr.s5.li1', '(a) Consenso dell\'interessato — es. presa visione dell\'informativa in registrazione, marketing opzionale, alcune condivisioni verso terzi.' ),
                $_t( 'gdpr.s5.li2', '(b) Esecuzione di un contratto o misure precontrattuali — erogazione dei servizi richiesti tramite l\'app.' ),
                $_t( 'gdpr.s5.li3', '(c) Obbligo legale — conservazione fiscale/amministrativa, risposte a richieste delle autorità.' ),
                $_t( 'gdpr.s5.li4', '(f) Legittimo interesse — sicurezza dell\'account, prevenzione frodi, miglioramento tecnico del servizio, nei limiti dei diritti dell\'interessato.' ),
            ]],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s6.title', '6. Natura del conferimento' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s6.p1', 'Il conferimento di nome, cognome e telefono è necessario per la registrazione e l\'uso dell\'app. Senza questi dati non è possibile creare l\'account.' ) ],
            [ 'p' => $_t( 'gdpr.s6.p2', 'Altri dati (email, documenti, CV, pagamenti, preferenze) sono richiesti solo per funzionalità specifiche: il mancato conferimento può impedire l\'uso di quel singolo servizio, ma non blocca necessariamente l\'intero account.' ) ],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s7.title', '7. Modalità di trattamento e sicurezza' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s7.p1', 'I dati sono trattati con strumenti elettronici e misure organizzative adeguate: accesso autenticato, cifratura in transit (HTTPS/TLS), segregazione dei ruoli operativi, logging di sicurezza e backup.' ) ],
            [ 'p' => $_t( 'gdpr.s7.p2', 'Le password sono memorizzate in forma hash. Token e credenziali sul dispositivo possono essere salvati nel secure storage del sistema operativo. L\'autenticazione biometrica, se attivata, resta gestita localmente dal dispositivo.' ) ],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s8.title', '8. Destinatari e responsabili del trattamento' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s8.p1', 'I dati non vengono venduti. Possono essere comunicati, nei limiti delle finalità indicate, a:' ) ],
            [ 'ul' => [
                $_t( 'gdpr.s8.li1', 'Personale autorizzato WeCoop e operatori di sportello coinvolti nella gestione delle pratiche.' ),
                $_t( 'gdpr.s8.li2', 'Fornitori tecnici (hosting, infrastruttura cloud, email, storage documenti) nominati responsabili del trattamento ove richiesto.' ),
                $_t( 'gdpr.s8.li3', 'Firebase / Google (notifiche push) e Stripe (pagamenti con carta), secondo le rispettive condizioni e garanzie contrattuali.' ),
                $_t( 'gdpr.s8.li4', 'Partner istituzionali o di progetto, solo se necessario all\'erogazione del servizio richiesto dall\'utente o con consenso specifico.' ),
                $_t( 'gdpr.s8.li5', 'Autorità pubbliche e organi di controllo, ove obbligatorio per legge.' ),
            ]],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s9.title', '9. Trasferimenti extra-UE' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s9.p1', 'I dati sono trattati preferibilmente in Italia e nello Spazio Economico Europeo. Qualora alcuni fornitori trattino dati fuori dallo SEE (es. infrastrutture globali di notifiche o pagamenti), il trasferimento avviene con garanzie adeguate ai sensi degli artt. 46-49 GDPR (clausole contrattuali standard, decisioni di adeguatezza o altre misure previste dalla normativa).' ) ],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s10.title', '10. Periodo di conservazione' ),
        'blocks' => [
            [ 'ul' => [
                $_t( 'gdpr.s10.li1', 'Dati di account e profilo: per tutta la durata del rapporto e, dopo la chiusura, per il tempo necessario a gestire richieste residue o obblighi di legge.' ),
                $_t( 'gdpr.s10.li2', 'Documenti e pratiche: fino a conclusione del servizio e per eventuali termini di conservazione documentale/amministrativa.' ),
                $_t( 'gdpr.s10.li3', 'Dati di pagamento e fiscali: fino a 10 anni, ove richiesto dalla normativa.' ),
                $_t( 'gdpr.s10.li4', 'Log tecnici e di sicurezza: di regola non oltre 12 mesi, salvo esigenze difensive o investigative.' ),
                $_t( 'gdpr.s10.li5', 'Token push e preferenze dispositivo: fino a disinstallazione, logout o revoca delle autorizzazioni.' ),
            ]],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s11.title', '11. Diritti dell\'interessato' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s11.p1', 'Ai sensi degli artt. 15-22 GDPR, l\'utente può in qualsiasi momento:' ) ],
            [ 'ul' => [
                $_t( 'gdpr.s11.li1', 'Accedere ai propri dati personali' ),
                $_t( 'gdpr.s11.li2', 'Chiederne la rettifica o l\'aggiornamento' ),
                $_t( 'gdpr.s11.li3', 'Richiedere la cancellazione (diritto all\'oblio), nei limiti di legge' ),
                $_t( 'gdpr.s11.li4', 'Limitare o opporsi al trattamento' ),
                $_t( 'gdpr.s11.li5', 'Richiedere la portabilità dei dati' ),
                $_t( 'gdpr.s11.li6', 'Revocare i consensi prestati, senza pregiudicare la liceità del trattamento precedente' ),
                $_t( 'gdpr.s11.li7', 'Proporre reclamo al Garante per la protezione dei dati personali (<a href="https://www.garanteprivacy.it" target="_blank" rel="noopener noreferrer">garanteprivacy.it</a>)' ),
            ]],
            [ 'p' => $_t( 'gdpr.s11.p2', 'Per esercitare i diritti:' ) . ' <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a>. ' . $_t( 'gdpr.s11.p3', 'Per richiedere l\'eliminazione dell\'account dall\'app o dal sito:' ) . ' <a href="' . esc_url( $delete_url ) . '">' . esc_html( $_t( 'gdpr.s11.delete_link', 'Elimina account' ) ) . '</a>.' ],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s12.title', '12. Consenso in registrazione e presa visione' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s12.p1', 'In fase di registrazione nell\'app, l\'utente deve dichiarare di aver letto la presente Informativa GDPR sull\'uso dei dati. La spunta è obbligatoria per completare la creazione dell\'account.' ) ],
            [ 'p' => $_t( 'gdpr.s12.p2', 'Il link all\'Informativa resta sempre disponibile nel profilo dell\'app e può essere consultato anche prima dell\'accesso. L\'accettazione viene registrata insieme ai dati di registrazione per finalità di accountability.' ) ],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s13.title', '13. Minori' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s13.p1', 'I servizi dell\'app WeCoop non sono destinati a minori di 16 anni. Non raccogliamo consapevolmente dati di minori. Se ritieni che un minore abbia fornito dati personali, contattaci immediatamente a privacy@wecoop.org per la rimozione.' ) ],
        ],
    ],
    [
        'title'  => $_t( 'gdpr.s14.title', '14. Aggiornamenti' ),
        'blocks' => [
            [ 'p' => $_t( 'gdpr.s14.p1', 'WeCoop può aggiornare questa Informativa per adeguamenti normativi, organizzativi o tecnici. La versione aggiornata è pubblicata su questa pagina con data di modifica. In caso di modifiche rilevanti, potrà essere richiesta una nuova presa visione in-app.' ) ],
            [ 'p' => $_t( 'gdpr.s14.p2', 'Per la policy generale del sito web consulta anche la' ) . ' <a href="' . esc_url( $privacy_url ) . '">' . esc_html( $_t( 'privacy.title', 'Privacy Policy' ) ) . '</a>.' ],
        ],
    ],
];

require __DIR__ . '/inc/legal-page-renderer.php';

wecoop_ws_page_shell_end();
get_footer();
