<?php
//Begin Really Simple Security key
define('RSSSL_KEY', 'kj2WK1xyB8AlcXSg2HdhcO0PFfsuvpt9WtAWw3PwFTrcdX97yLSf6rvcD1l8p9UJ');
//END Really Simple Security key
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u703617904_4qtmg' );

/** Database username */
define( 'DB_USER', 'u703617904_CsAZC' );

/** Database password */
define( 'DB_PASSWORD', 'GMD4BMbeTd' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'd$5@O)2V)ZV7Z=6b>^Z@ygPUgf6(XNtm0q[_a36_)!r~6U;dX_1g])3k:o}9W=wr' );
define( 'SECURE_AUTH_KEY',   'CS#&DotSC^A{uJ8sJ,1aH@0Ltq$fC *_T*($4eoB$b/VY!b%oW1qJrIfcI6O2EzG' );
define( 'LOGGED_IN_KEY',     'lN;tDw<at!@?~ON)[.aZBJhi3Sl/B$Bovx9qz`oxdL%xdi~|uq!bp )%|kLKIY<Y' );
define( 'NONCE_KEY',         'uyGtAV}p^~G<pSbpd7t<@I3^h)`dOPm{=LJAE#+F#87o:OfVZguW|zYz#Jc,l}A&' );
define( 'AUTH_SALT',         '?]8kD1#:w}p(4,FPacd$&ot1/?&/qUEE2 VvLQ8u[Y/D8I`4SMn&/ke]_Mzy?AdS' );
define( 'SECURE_AUTH_SALT',  ',^YTX:cSc<6dD1Z]Ws01Ib<5S.#kLeu|vhxH[br_GE^^j!/uy]4%IuB#|?Ou!ZC;' );
define( 'LOGGED_IN_SALT',    'ylg&WQ08R*w9Z|#MjuELlX.,kh-VbskUOc84#d~Z9xf%+$ u-y#8oC|0w|F+U*m4' );
define( 'NONCE_SALT',        'H8k/MQqY5GJOm<)oxuBGo3;5oHf@}}a^Mv|G<Gs J#z+>>TDP>PFAiFjCRo.VG$(' );
define( 'WP_CACHE_KEY_SALT', '=dM**L2zGo<4`Zvu{9anAL^2S%H7DYl4I,a2,iGfoXcjpb(J-hrD]#fRGp>}w[zO' );

/**
 * JWT Authentication Configuration
 */
define('JWT_AUTH_SECRET_KEY', 'wecoop-jwt-2025-' . md5('kj2WK1xyB8AlcXSg2HdhcO0PFfsuvpt9WtAWw3PwFTrcdX97yLSf6rvcD1l8p9UJ'));
define('JWT_AUTH_CORS_ENABLE', true);
define('JWT_AUTH_ISS', 'https://www.wecoop.org');
/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp57384_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}

// Log errors to wp-content/debug.log instead of displaying them
if ( ! defined( 'WP_DEBUG_LOG' ) ) {
	define( 'WP_DEBUG_LOG', true );
}

// Don't display errors on the site
if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
	define( 'WP_DEBUG_DISPLAY', false );
}

// Suppress deprecated notices (optional - removes the specific notices you're seeing)
@ini_set( 'display_errors', 0 );

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', 'a89c81cd3eeea5503dc0907f67d44e6e' );
define( 'WP_AUTO_UPDATE_CORE', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';