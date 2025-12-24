<?php
if ( !is_user_logged_in() ) {
    echo '<p>Devi essere loggato per richiedere la cancellazione dei dati.</p>';
    return;
}

$current_user = wp_get_current_user();
?>

<form method="post" action="">
    <?php wp_nonce_field( 'request_delete_data_action', 'request_delete_data_nonce' ); ?>
    <p>Se vuoi richiedere la cancellazione dei tuoi dati personali, clicca il pulsante qui sotto.</p>
    <input type="hidden" name="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>">
    <input type="submit" name="request_delete_data" value="Richiedi eliminazione dati">
</form>
