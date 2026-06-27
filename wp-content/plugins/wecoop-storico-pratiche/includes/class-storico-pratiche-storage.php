<?php
/**
 * Storage protetto per i documenti dello storico pratiche.
 *
 * I file (730/ISEE: dati sensibili) NON vanno nella Media Library pubblica.
 * Vengono salvati in /uploads/wecoop-pratiche-protette/ con .htaccess deny,
 * e serviti solo via endpoint REST con controllo di proprieta'.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Storico_Pratiche_Storage {

    const SUBDIR = 'wecoop-pratiche-protette';

    const ALLOWED_EXT  = ['pdf', 'jpg', 'jpeg', 'png'];
    const ALLOWED_MIME = ['application/pdf', 'image/jpeg', 'image/png'];
    const MAX_SIZE     = 15728640; // 15 MB

    /**
     * Percorso assoluto della cartella protetta.
     */
    public static function base_dir() {
        $uploads = wp_upload_dir();
        return trailingslashit($uploads['basedir']) . self::SUBDIR;
    }

    /**
     * Crea la cartella protetta con .htaccess + index.php se non esiste.
     */
    public static function ensure_protected_dir() {
        $dir = self::base_dir();

        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }

        $htaccess = $dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            $rules  = "# Accesso diretto negato: i file sono serviti via REST con controllo proprieta'.\n";
            $rules .= "Order allow,deny\nDeny from all\n";
            $rules .= "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n";
            @file_put_contents($htaccess, $rules);
        }

        $index = $dir . '/index.php';
        if (!file_exists($index)) {
            @file_put_contents($index, "<?php // Silence is golden.\n");
        }

        return $dir;
    }

    /**
     * Valida un file ($_FILES entry) per tipo, mime reale e dimensione.
     *
     * @return true|WP_Error
     */
    public static function validate_upload(array $file) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'Errore durante il caricamento del file.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return new WP_Error('upload_invalid', 'File non valido.');
        }

        if ((int) $file['size'] <= 0 || (int) $file['size'] > self::MAX_SIZE) {
            return new WP_Error('upload_size', 'Il file supera la dimensione massima consentita (15 MB).');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            return new WP_Error('upload_ext', 'Formato non consentito. Ammessi: PDF, JPG, PNG.');
        }

        // MIME reale dal contenuto (non si fida del client).
        $real_mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $real_mime = (string) finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }

        if ($real_mime !== '' && !in_array($real_mime, self::ALLOWED_MIME, true)) {
            return new WP_Error('upload_mime', 'Il contenuto del file non corrisponde a un formato consentito.');
        }

        return true;
    }

    /**
     * Sposta il file caricato nella cartella protetta con nome univoco.
     *
     * @return array{file_name:string, file_path:string, file_size:int, mime_type:string}|WP_Error
     *         file_path e' relativo alla base_dir (solo il nome file).
     */
    public static function store_upload(array $file, $user_id, $tipo) {
        $valid = self::validate_upload($file);
        if (is_wp_error($valid)) {
            return $valid;
        }

        $dir = self::ensure_protected_dir();
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $safe_tipo = preg_replace('/[^a-z0-9_\-]/', '', strtolower($tipo));
        $unique = sprintf(
            '%d_%s_%s_%s.%s',
            (int) $user_id,
            $safe_tipo !== '' ? $safe_tipo : 'doc',
            gmdate('Ymd-His'),
            wp_generate_password(8, false, false),
            $ext
        );

        $dest = trailingslashit($dir) . $unique;

        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            return new WP_Error('upload_move', 'Impossibile salvare il file sul server.');
        }

        @chmod($dest, 0640);

        $real_mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $real_mime = (string) finfo_file($finfo, $dest);
                finfo_close($finfo);
            }
        }

        return [
            'file_name' => sanitize_file_name($file['name']),
            'file_path' => $unique, // relativo alla cartella protetta
            'file_size' => (int) filesize($dest),
            'mime_type' => $real_mime !== '' ? $real_mime : null,
        ];
    }

    /**
     * Percorso assoluto di un file dato il path relativo memorizzato.
     */
    public static function absolute_path($relative) {
        // Evita traversal: usa solo il basename.
        $relative = basename((string) $relative);
        return trailingslashit(self::base_dir()) . $relative;
    }

    /**
     * Elimina il file fisico.
     */
    public static function delete_file($relative) {
        $path = self::absolute_path($relative);
        if (is_file($path)) {
            return @unlink($path);
        }
        return true;
    }
}
