<?php
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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'k09244812141242' );

/** Database username */
define( 'DB_USER', 'k09244812141242' );

/** Database password */
define( 'DB_PASSWORD', 'eO9+tSyWAVI4' );

/** Database hostname */
define( 'DB_HOST', '10.205.25.176:3308' );

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
define( 'AUTH_KEY',         '-mGRVQRbC%%yy9y@-FTL' );
define( 'SECURE_AUTH_KEY',  'PsD(9c@a50S)DUpqNLR4' );
define( 'LOGGED_IN_KEY',    'j2O3f*3XQsI ZJU8tX)R' );
define( 'NONCE_KEY',        'n&CLFsL$D@9cGNT3=waW' );
define( 'AUTH_SALT',        'zzdEEA$1Nf1ybQ8+HNkD' );
define( 'SECURE_AUTH_SALT', 'T6T7 BV1maKsD@wz#_LB' );
define( 'LOGGED_IN_SALT',   ' 0sgH)#QA#gFRyJn7T56' );
define( 'NONCE_SALT',       '&sWt0K*1)KkvhTc$=aTB' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_faps0k5f33_';

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
define( 'WP_DEBUG', false )
ini_set('display_errors','On');

define( 'WP_MEMORY_LIMIT' , '96M');
require_once( dirname( __FILE__ ) . '/gd-config.php' );
define( 'FS_METHOD', 'direct' );
define( 'FS_CHMOD_DIR', (0705 & ~ umask()) );
define( 'FS_CHMOD_FILE', (0604 & ~ umask()) );


/* Add any custom values between this line and the "stop editing" line. */



define( 'FORCE_SSL_ADMIN', true );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';