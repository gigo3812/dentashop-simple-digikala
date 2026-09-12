<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dentashop' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'V:SqLGy(HIvb8*hD|f/3j5ex_evrq(qf$YWsEcD!nQP#:-Zz,]!{b!t#wla-TxTs' );
define( 'SECURE_AUTH_KEY',  '~^%0F6Y+kGYU)wsu>5aI:aWvIXXbNMR:S4~hrBndSvqcL)v+5LDKMqGXz$^{BWW:' );
define( 'LOGGED_IN_KEY',    'W/J7Mg3nb@+X*|30H%y=R+rCG`4j=scTlyk)o8nJYh-q7vlbiBJ^W|>b|AslS-}Y' );
define( 'NONCE_KEY',        'kDGFP+;85 LN3)y!Qv]MyNi_W*XepzWs6cP4&6dI@4ASw:;;rSgn1`dQTdxH 1|A' );
define( 'AUTH_SALT',        'V/ q72K%f s05?z4Hjqc}3Z)nt1U,|T<x7*1u9Z:E!vdO2,yd>n~4nxty#{kD~Th' );
define( 'SECURE_AUTH_SALT', 'onZcW#b?S)[HT6hJ_bM<TK!>3mv)b_a:ju^7) @2b&Zj-[x!50[&/;yKQ;/sZ;6P' );
define( 'LOGGED_IN_SALT',   'D<z7AeoT~mBN>`/2yiwtu#bNuhtJ%*}EU}CDDm@~m3zFO,9IW3rw3|V;5+Y=}ip&' );
define( 'NONCE_SALT',       '^M>O,BwchDjZ2&2;J4^%Q#ChUxY%!e7Hj2 Cv~16[%=>ZuesNhA#U#DD$HQ>{cgL' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
