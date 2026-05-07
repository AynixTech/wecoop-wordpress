<?php
/**
 * Template Name: Elimina Account
 * Template Post Type: page
 *
 * Pagina GDPR – richiesta eliminazione account e dati personali.
 * URL consigliato: /elimina-account/
 *
 * @package WeCoop
 */

get_header();

$tr = 'translate_string';
wecoop_ws_page_shell_start( $tr( 'delete_account.aria.page', 'Elimina Account - WeCoop' ) );

$status       = isset( $_GET['delete_status'] ) ? sanitize_key( $_GET['delete_status'] ) : '';
$is_logged_in = is_user_logged_in();
$current_user = $is_logged_in ? wp_get_current_user() : null;
$privacy_url  = get_privacy_policy_url() ?: home_url( '/privacy-policy/' );
?>

<!-- HERO ─────────────────────────────────────────────────────────────────────── -->
<section class="cw-hero wda-hero" id="elimina-account-hero">
    <div class="ws-container">
        <div class="cw-hero__inner">
            <div class="cw-hero__text">
                <span class="cw-eyebrow wda-eyebrow">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <?php echo esc_html( $tr( 'delete_account.hero.eyebrow', 'GDPR – Diritto alla cancellazione' ) ); ?>
                </span>
                <h1><?php echo esc_html( $tr( 'delete_account.hero.title', 'Elimina il tuo account' ) ); ?></h1>
                <p class="cw-hero__lead">
                    <?php echo esc_html( $tr( 'delete_account.hero.lead', 'Hai il diritto di richiedere la cancellazione del tuo account WeCoop e di tutti i dati personali associati, in conformità al GDPR – Art. 17.' ) ); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- BANNER DI STATO ──────────────────────────────────────────────────────────── -->
<?php if ( in_array( $status, [ 'sent', 'no_confirm', 'error' ], true ) ) : ?>
<section class="ws-section wda-status-section">
    <div class="ws-container">
        <?php if ( 'sent' === $status ) : ?>
            <div class="wda-alert wda-alert--success" role="alert">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <div>
                    <strong><?php echo esc_html( $tr( 'delete_account.status.sent.title', 'Richiesta inviata con successo' ) ); ?></strong>
                    <p><?php echo esc_html( $tr( 'delete_account.status.sent.body', 'Abbiamo ricevuto la tua richiesta e riceverai una email di conferma. Il nostro team la elaborerà entro 30 giorni come previsto dal GDPR.' ) ); ?></p>
                </div>
            </div>
        <?php elseif ( 'no_confirm' === $status ) : ?>
            <div class="wda-alert wda-alert--warning" role="alert">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                <div>
                    <strong><?php echo esc_html( $tr( 'delete_account.status.no_confirm.title', 'Conferma richiesta' ) ); ?></strong>
                    <p><?php echo esc_html( $tr( 'delete_account.status.no_confirm.body', 'Devi spuntare la casella di conferma per procedere con la richiesta.' ) ); ?></p>
                </div>
            </div>
        <?php else : ?>
            <div class="wda-alert wda-alert--error" role="alert">
                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                <div>
                    <strong><?php echo esc_html( $tr( 'delete_account.status.error.title', 'Si è verificato un errore' ) ); ?></strong>
                    <p><?php echo esc_html( $tr( 'delete_account.status.error.body', 'Non è stato possibile elaborare la richiesta. Contattaci a privacy@wecoop.org.' ) ); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- CONTENUTO PRINCIPALE ─────────────────────────────────────────────────────── -->
<section class="ws-section" id="elimina-account-main">
    <div class="ws-container">
        <div class="ws-grid-2 wda-grid">

            <!-- Colonna informazioni ────────────────────────────────────────── -->
            <div class="wda-info">
                <h2><?php echo esc_html( $tr( 'delete_account.info.title', 'Cosa succederà dopo la richiesta?' ) ); ?></h2>
                <p><?php echo esc_html( $tr( 'delete_account.info.lead', 'La tua richiesta verrà elaborata dal nostro team nel rispetto del GDPR. Ecco cosa avviene:' ) ); ?></p>

                <ul class="ws-contact-list ws-contact-list--icons wda-steps">
                    <li>
                        <span class="ws-contact-list__icon wda-icon--green"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                        <div>
                            <strong><?php echo esc_html( $tr( 'delete_account.info.step1.title', 'Conferma immediata' ) ); ?></strong>
                            <span><?php echo esc_html( $tr( 'delete_account.info.step1.body', 'Ricevi subito una email di conferma all\'indirizzo registrato.' ) ); ?></span>
                        </div>
                    </li>
                    <li>
                        <span class="ws-contact-list__icon wda-icon--blue"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
                        <div>
                            <strong><?php echo esc_html( $tr( 'delete_account.info.step2.title', 'Elaborazione entro 30 giorni' ) ); ?></strong>
                            <span><?php echo esc_html( $tr( 'delete_account.info.step2.body', 'Il nostro team processa la richiesta entro 30 giorni come previsto dal GDPR Art. 17.' ) ); ?></span>
                        </div>
                    </li>
                    <li>
                        <span class="ws-contact-list__icon wda-icon--red"><i class="fa-solid fa-trash" aria-hidden="true"></i></span>
                        <div>
                            <strong><?php echo esc_html( $tr( 'delete_account.info.step3.title', 'Dati eliminati' ) ); ?></strong>
                            <span><?php echo esc_html( $tr( 'delete_account.info.step3.body', 'Vengono cancellati: account, profilo, documenti caricati, messaggi, preferenze e tutti i dati associati.' ) ); ?></span>
                        </div>
                    </li>
                    <li>
                        <span class="ws-contact-list__icon wda-icon--purple"><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i></span>
                        <div>
                            <strong><?php echo esc_html( $tr( 'delete_account.info.step4.title', 'Eccezioni di legge' ) ); ?></strong>
                            <span><?php echo esc_html( $tr( 'delete_account.info.step4.body', 'Alcuni dati possono essere conservati se richiesto da obblighi legali o fiscali (es. transazioni Stripe).' ) ); ?></span>
                        </div>
                    </li>
                </ul>

                <div class="wda-privacy-note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <?php
                    $privacy_link = '<a href="' . esc_url( $privacy_url ) . '">' . esc_html( $tr( 'delete_account.info.privacy_link', 'Informativa Privacy' ) ) . '</a>';
                    printf(
                        wp_kses( $tr( 'delete_account.info.privacy_note', 'Per maggiori dettagli su come trattiamo i tuoi dati, consulta la nostra %s.' ), [ 'a' => [ 'href' => [] ] ] ),
                        $privacy_link // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    );
                    ?>
                </div>
            </div>

            <!-- Colonna form / stato ─────────────────────────────────────────── -->
            <div class="ws-form-shell wda-form-shell">

                <?php if ( 'sent' === $status ) : ?>
                    <!-- Richiesta già inviata ─────── -->
                    <div class="wda-sent-state">
                        <i class="fa-solid fa-circle-check wda-sent-icon" aria-hidden="true"></i>
                        <h2><?php echo esc_html( $tr( 'delete_account.sent.title', 'Richiesta ricevuta' ) ); ?></h2>
                        <p><?php echo esc_html( $tr( 'delete_account.sent.body', 'Controlla la tua email per la conferma. Se non ricevi nulla entro pochi minuti, scrivi a privacy@wecoop.org.' ) ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ws-btn ws-btn--primary wda-home-btn">
                            <i class="fa-solid fa-house" aria-hidden="true"></i>
                            <?php echo esc_html( $tr( 'delete_account.sent.home_cta', 'Torna alla home' ) ); ?>
                        </a>
                    </div>

                <?php elseif ( ! $is_logged_in ) : ?>
                    <!-- Utente non loggato ────────── -->
                    <div class="wda-login-state">
                        <i class="fa-solid fa-lock wda-login-icon" aria-hidden="true"></i>
                        <h2><?php echo esc_html( $tr( 'delete_account.not_logged.title', 'Accedi per continuare' ) ); ?></h2>
                        <p><?php echo esc_html( $tr( 'delete_account.not_logged.body', 'Devi essere connesso al tuo account WeCoop per richiedere la cancellazione dei dati.' ) ); ?></p>
                        <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="ws-btn ws-btn--primary">
                            <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                            <?php echo esc_html( $tr( 'delete_account.not_logged.cta', 'Accedi al tuo account' ) ); ?>
                        </a>
                        <p class="wda-alt-contact">
                            <?php echo esc_html( $tr( 'delete_account.not_logged.alt', 'Non hai un account? Scrivi direttamente a' ) ); ?>
                            <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a>
                        </p>
                    </div>

                <?php else : ?>
                    <!-- Form eliminazione ─────────── -->
                    <div class="wda-form-card">
                        <h2><?php echo esc_html( $tr( 'delete_account.form.title', 'Richiedi eliminazione account' ) ); ?></h2>
                        <p>
                            <?php
                            printf(
                                wp_kses( $tr( 'delete_account.form.greeting', 'Stai richiedendo la cancellazione dell\'account di: %s' ), [ 'strong' => [] ] ),
                                '<strong>' . esc_html( $current_user->display_name ) . ' (' . esc_html( $current_user->user_email ) . ')</strong>'
                            );
                            ?>
                        </p>

                        <div class="wda-warning" role="alert">
                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                            <p><?php echo esc_html( $tr( 'delete_account.form.warning', 'Attenzione: questa azione è irreversibile. Una volta eliminato, il tuo account e tutti i dati associati non potranno essere recuperati.' ) ); ?></p>
                        </div>

                        <form method="post" action="" id="wecoop-delete-account-form">
                            <?php wp_nonce_field( 'wecoop_delete_account_action', 'wecoop_delete_account_nonce' ); ?>
                            <input type="hidden" name="wecoop_user_id" value="<?php echo esc_attr( $current_user->ID ); ?>">

                            <label class="wda-confirm-label">
                                <input type="checkbox" name="wecoop_confirm_delete" value="1" required>
                                <span><?php echo esc_html( $tr( 'delete_account.form.confirm_label', 'Confermo di voler eliminare il mio account WeCoop e tutti i dati personali associati in modo permanente e irreversibile.' ) ); ?></span>
                            </label>

                            <button type="submit" class="ws-btn wda-btn-delete">
                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                <?php echo esc_html( $tr( 'delete_account.form.submit', 'Invia richiesta di eliminazione account' ) ); ?>
                            </button>

                            <p class="wda-gdpr-note">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                <?php echo esc_html( $tr( 'delete_account.form.footer_note', 'La richiesta è protetta e trattata in conformità al GDPR.' ) ); ?>
                            </p>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<!-- CONTATTO DIRETTO ─────────────────────────────────────────────────────────── -->
<section class="ws-section ws-section--soft wda-contact-section" id="elimina-account-contact">
    <div class="ws-container wda-contact-inner">
        <h2><?php echo esc_html( $tr( 'delete_account.contact.title', 'Hai bisogno di assistenza?' ) ); ?></h2>
        <p><?php echo esc_html( $tr( 'delete_account.contact.body', 'Se hai domande sulla cancellazione dei tuoi dati o preferisci contattarci direttamente, il nostro team Privacy è disponibile per aiutarti.' ) ); ?></p>
        <a href="mailto:privacy@wecoop.org" class="ws-btn ws-btn--primary">
            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
            privacy@wecoop.org
        </a>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
