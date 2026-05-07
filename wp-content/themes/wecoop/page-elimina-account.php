<?php
/**
 * Template Name: Elimina Account
 * Template Post Type: page
 *
 * Pagina GDPR per la richiesta di eliminazione account e dati.
 * URL consigliato: /elimina-account/
 *
 * @package WeCoop
 */

get_header();

$tr = 'translate_string';
wecoop_ws_page_shell_start( $tr( 'delete_account.aria.page', 'Elimina Account - WeCoop' ) );

// ── Stato dal redirect ────────────────────────────────────────────────────────
$status = isset( $_GET['delete_status'] ) ? sanitize_key( $_GET['delete_status'] ) : '';

// ── Stato utente ─────────────────────────────────────────────────────────────
$is_logged_in   = is_user_logged_in();
$current_user   = $is_logged_in ? wp_get_current_user() : null;
$privacy_url    = get_privacy_policy_url() ?: home_url( '/privacy-policy/' );
?>

    <!-- HERO ─────────────────────────────────────────────────────────────────── -->
    <section class="cw-hero" id="elimina-account-hero" style="background:linear-gradient(135deg,#fff5f5 0%,#fff 60%);">
        <div class="ws-container">
            <div class="cw-hero__inner" style="align-items:center;">
                <div class="cw-hero__text">
                    <span class="cw-eyebrow" style="color:#e53935;">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <?php echo esc_html( $tr( 'delete_account.hero.eyebrow', 'GDPR – Diritto alla cancellazione' ) ); ?>
                    </span>
                    <h1><?php echo esc_html( $tr( 'delete_account.hero.title', 'Elimina il tuo account' ) ); ?></h1>
                    <p class="cw-hero__lead">
                        <?php echo esc_html( $tr( 'delete_account.hero.lead', 'Hai il diritto di richiedere la cancellazione del tuo account WeCoop e di tutti i dati personali associati, in conformità al Regolamento Generale sulla Protezione dei Dati (GDPR – Art. 17).' ) ); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- MESSAGGIO DI STATO ───────────────────────────────────────────────────── -->
    <?php if ( 'sent' === $status ) : ?>
    <section class="ws-section" style="padding-top:0;">
        <div class="ws-container">
            <div class="ws-alert ws-alert--success" role="alert" style="background:#e8f5e9;border-left:4px solid #43a047;padding:20px 24px;border-radius:10px;display:flex;gap:14px;align-items:flex-start;max-width:700px;margin:0 auto;">
                <i class="fa-solid fa-circle-check" style="color:#43a047;font-size:22px;margin-top:2px;" aria-hidden="true"></i>
                <div>
                    <strong style="display:block;margin-bottom:4px;"><?php echo esc_html( $tr( 'delete_account.status.sent.title', 'Richiesta inviata con successo' ) ); ?></strong>
                    <p style="margin:0;"><?php echo esc_html( $tr( 'delete_account.status.sent.body', 'Abbiamo ricevuto la tua richiesta e riceverai una email di conferma. Il nostro team la elaborerà entro 30 giorni come previsto dal GDPR.' ) ); ?></p>
                </div>
            </div>
        </div>
    </section>
    <?php elseif ( 'no_confirm' === $status ) : ?>
    <section class="ws-section" style="padding-top:0;">
        <div class="ws-container">
            <div class="ws-alert ws-alert--warning" role="alert" style="background:#fff8e1;border-left:4px solid #ffa000;padding:20px 24px;border-radius:10px;display:flex;gap:14px;align-items:flex-start;max-width:700px;margin:0 auto;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ffa000;font-size:22px;margin-top:2px;" aria-hidden="true"></i>
                <div>
                    <strong style="display:block;margin-bottom:4px;"><?php echo esc_html( $tr( 'delete_account.status.no_confirm.title', 'Conferma richiesta' ) ); ?></strong>
                    <p style="margin:0;"><?php echo esc_html( $tr( 'delete_account.status.no_confirm.body', 'Devi spuntare la casella di conferma per procedere con la richiesta.' ) ); ?></p>
                </div>
            </div>
        </div>
    </section>
    <?php elseif ( 'error' === $status ) : ?>
    <section class="ws-section" style="padding-top:0;">
        <div class="ws-container">
            <div class="ws-alert ws-alert--error" role="alert" style="background:#ffebee;border-left:4px solid #e53935;padding:20px 24px;border-radius:10px;display:flex;gap:14px;align-items:flex-start;max-width:700px;margin:0 auto;">
                <i class="fa-solid fa-circle-xmark" style="color:#e53935;font-size:22px;margin-top:2px;" aria-hidden="true"></i>
                <div>
                    <strong style="display:block;margin-bottom:4px;"><?php echo esc_html( $tr( 'delete_account.status.error.title', 'Si è verificato un errore' ) ); ?></strong>
                    <p style="margin:0;"><?php echo esc_html( $tr( 'delete_account.status.error.body', 'Non è stato possibile elaborare la richiesta. Contattaci direttamente a privacy@wecoop.org.' ) ); ?></p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CONTENUTO PRINCIPALE ─────────────────────────────────────────────────── -->
    <section class="ws-section" id="elimina-account-main">
        <div class="ws-container">
            <div class="ws-grid-2" style="gap:48px;align-items:start;">

                <!-- Colonna sinistra: informazioni ───────────────────────────── -->
                <div class="ws-delete-info">
                    <h2><?php echo esc_html( $tr( 'delete_account.info.title', 'Cosa succederà dopo la richiesta?' ) ); ?></h2>
                    <p><?php echo esc_html( $tr( 'delete_account.info.lead', 'La tua richiesta verrà elaborata dal nostro team nel rispetto del GDPR. Ecco cosa avviene:' ) ); ?></p>

                    <ul class="ws-contact-list ws-contact-list--icons" style="margin-top:24px;">
                        <li>
                            <span class="ws-contact-list__icon" style="background:#e8f5e9;color:#43a047;"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                            <div>
                                <strong><?php echo esc_html( $tr( 'delete_account.info.step1.title', 'Conferma immediata' ) ); ?></strong>
                                <span><?php echo esc_html( $tr( 'delete_account.info.step1.body', 'Ricevi subito una email di conferma all\'indirizzo registrato.' ) ); ?></span>
                            </div>
                        </li>
                        <li>
                            <span class="ws-contact-list__icon" style="background:#e3f2fd;color:#1e88e5;"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
                            <div>
                                <strong><?php echo esc_html( $tr( 'delete_account.info.step2.title', 'Elaborazione entro 30 giorni' ) ); ?></strong>
                                <span><?php echo esc_html( $tr( 'delete_account.info.step2.body', 'Il nostro team processa la richiesta entro 30 giorni come previsto dal GDPR Art. 17.' ) ); ?></span>
                            </div>
                        </li>
                        <li>
                            <span class="ws-contact-list__icon" style="background:#fce4ec;color:#e53935;"><i class="fa-solid fa-trash" aria-hidden="true"></i></span>
                            <div>
                                <strong><?php echo esc_html( $tr( 'delete_account.info.step3.title', 'Dati eliminati' ) ); ?></strong>
                                <span><?php echo esc_html( $tr( 'delete_account.info.step3.body', 'Vengono cancellati: account, profilo, documenti caricati, messaggi, preferenze e tutti i dati associati.' ) ); ?></span>
                            </div>
                        </li>
                        <li>
                            <span class="ws-contact-list__icon" style="background:#f3e5f5;color:#8e24aa;"><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i></span>
                            <div>
                                <strong><?php echo esc_html( $tr( 'delete_account.info.step4.title', 'Eccezioni di legge' ) ); ?></strong>
                                <span><?php echo esc_html( $tr( 'delete_account.info.step4.body', 'Alcuni dati possono essere conservati se richiesto da obblighi legali o fiscali (es. transazioni Stripe).' ) ); ?></span>
                            </div>
                        </li>
                    </ul>

                    <div style="margin-top:32px;padding:20px;background:#f5f5f5;border-radius:10px;">
                        <p style="margin:0;font-size:0.9rem;color:#555;">
                            <i class="fa-solid fa-circle-info" aria-hidden="true" style="margin-right:6px;color:#1e88e5;"></i>
                            <?php
                            printf(
                                /* translators: %s: link privacy policy */
                                esc_html( $tr( 'delete_account.info.privacy_note', 'Per maggiori dettagli su come trattiamo i tuoi dati, consulta la nostra %s.' ) ),
                                '<a href="' . esc_url( $privacy_url ) . '" style="color:#1e88e5;">' . esc_html( $tr( 'delete_account.info.privacy_link', 'Informativa Privacy' ) ) . '</a>'
                            );
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Colonna destra: form / stato ─────────────────────────────── -->
                <div class="ws-form-shell">

                    <?php if ( 'sent' === $status ) : ?>
                        <!-- Stato: richiesta già inviata -->
                        <div style="text-align:center;padding:40px 20px;">
                            <i class="fa-solid fa-circle-check" style="font-size:56px;color:#43a047;" aria-hidden="true"></i>
                            <h2 style="margin-top:16px;"><?php echo esc_html( $tr( 'delete_account.sent.title', 'Richiesta ricevuta' ) ); ?></h2>
                            <p><?php echo esc_html( $tr( 'delete_account.sent.body', 'Controlla la tua email per la conferma. Se non ricevi nulla entro pochi minuti, scrivi a privacy@wecoop.org.' ) ); ?></p>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ws-btn ws-btn--primary" style="margin-top:20px;display:inline-flex;">
                                <i class="fa-solid fa-house" aria-hidden="true"></i>
                                <?php echo esc_html( $tr( 'delete_account.sent.home_cta', 'Torna alla home' ) ); ?>
                            </a>
                        </div>

                    <?php elseif ( ! $is_logged_in ) : ?>
                        <!-- Stato: non loggato -->
                        <div style="text-align:center;padding:40px 20px;background:#fff8e1;border-radius:14px;">
                            <i class="fa-solid fa-lock" style="font-size:48px;color:#ffa000;" aria-hidden="true"></i>
                            <h2 style="margin-top:16px;"><?php echo esc_html( $tr( 'delete_account.not_logged.title', 'Accedi per continuare' ) ); ?></h2>
                            <p><?php echo esc_html( $tr( 'delete_account.not_logged.body', 'Devi essere connesso al tuo account WeCoop per richiedere la cancellazione dei dati.' ) ); ?></p>
                            <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="ws-btn ws-btn--primary" style="margin-top:20px;display:inline-flex;">
                                <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                                <?php echo esc_html( $tr( 'delete_account.not_logged.cta', 'Accedi al tuo account' ) ); ?>
                            </a>
                            <p style="margin-top:16px;font-size:0.9rem;color:#777;">
                                <?php echo esc_html( $tr( 'delete_account.not_logged.alt', 'Non hai un account? Scrivi direttamente a' ) ); ?>
                                <a href="mailto:privacy@wecoop.org" style="color:#1e88e5;">privacy@wecoop.org</a>
                            </p>
                        </div>

                    <?php else : ?>
                        <!-- Stato: utente loggato → mostra form -->
                        <div style="background:#fff;border:1px solid #e0e0e0;border-radius:14px;padding:32px;">
                            <h2 style="margin-top:0;"><?php echo esc_html( $tr( 'delete_account.form.title', 'Richiedi eliminazione account' ) ); ?></h2>
                            <p>
                                <?php
                                printf(
                                    /* translators: %s: user display name */
                                    esc_html( $tr( 'delete_account.form.greeting', 'Stai richiedendo la cancellazione dell\'account di: %s' ) ),
                                    '<strong>' . esc_html( $current_user->display_name ) . ' (' . esc_html( $current_user->user_email ) . ')</strong>'
                                );
                                ?>
                            </p>

                            <div style="background:#ffebee;border-radius:10px;padding:16px;margin:20px 0;display:flex;gap:12px;align-items:flex-start;">
                                <i class="fa-solid fa-triangle-exclamation" style="color:#e53935;font-size:20px;margin-top:2px;" aria-hidden="true"></i>
                                <p style="margin:0;font-size:0.9rem;">
                                    <?php echo esc_html( $tr( 'delete_account.form.warning', 'Attenzione: questa azione è irreversibile. Una volta eliminato, il tuo account e tutti i dati associati non potranno essere recuperati.' ) ); ?>
                                </p>
                            </div>

                            <form method="post" action="" id="wecoop-delete-account-form">
                                <?php wp_nonce_field( 'wecoop_delete_account_action', 'wecoop_delete_account_nonce' ); ?>
                                <input type="hidden" name="wecoop_user_id" value="<?php echo esc_attr( $current_user->ID ); ?>">

                                <label style="display:flex;gap:12px;align-items:flex-start;cursor:pointer;margin-bottom:24px;padding:16px;background:#f9f9f9;border-radius:8px;">
                                    <input type="checkbox" name="wecoop_confirm_delete" value="1" required style="margin-top:3px;width:18px;height:18px;cursor:pointer;flex-shrink:0;">
                                    <span style="font-size:0.95rem;">
                                        <?php echo esc_html( $tr( 'delete_account.form.confirm_label', 'Confermo di voler eliminare il mio account WeCoop e tutti i dati personali associati in modo permanente e irreversibile.' ) ); ?>
                                    </span>
                                </label>

                                <button type="submit" class="ws-btn" style="background:#e53935;color:#fff;border:none;cursor:pointer;width:100%;justify-content:center;font-size:1rem;padding:14px 24px;border-radius:8px;display:flex;align-items:center;gap:10px;">
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    <?php echo esc_html( $tr( 'delete_account.form.submit', 'Invia richiesta di eliminazione account' ) ); ?>
                                </button>

                                <p style="text-align:center;margin-top:16px;font-size:0.85rem;color:#777;">
                                    <i class="fa-solid fa-lock" aria-hidden="true" style="margin-right:4px;"></i>
                                    <?php echo esc_html( $tr( 'delete_account.form.footer_note', 'La richiesta è protetta e trattata in conformità al GDPR.' ) ); ?>
                                </p>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>

    <!-- CONTATTO DIRETTO ─────────────────────────────────────────────────────── -->
    <section class="ws-section ws-section--soft" id="elimina-account-contact">
        <div class="ws-container" style="text-align:center;max-width:680px;">
            <h2><?php echo esc_html( $tr( 'delete_account.contact.title', 'Hai bisogno di assistenza?' ) ); ?></h2>
            <p><?php echo esc_html( $tr( 'delete_account.contact.body', 'Se hai domande sulla cancellazione dei tuoi dati o preferisci contattarci direttamente, il nostro team Privacy è disponibile per aiutarti.' ) ); ?></p>
            <a href="mailto:privacy@wecoop.org" class="ws-btn ws-btn--primary" style="display:inline-flex;margin-top:8px;">
                <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                privacy@wecoop.org
            </a>
        </div>
    </section>

<?php
wecoop_ws_page_shell_end();
get_footer();
