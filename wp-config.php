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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'fcjmp');

/** Database username */
define('DB_USER', 'root');

/** Database password */
define('DB_PASSWORD', '');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

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
define('AUTH_KEY',         'I]sP]_ ^4o8L:bLGPFXlwedDrjL6 5A9O}*YNd5ZzoRS4:APIt[%~{zoCbSa~NG:');
define('SECURE_AUTH_KEY',  'rAa3m%&XYN(w|L_s^s.PKaaZ)K,.9/Wyxl(|@sY[w|!IfM[c6;a) `gkK6WN3hf4');
define('LOGGED_IN_KEY',    'N ]X#y2ZBaXKra<_]O^8Ds>hBV5`+U{EX$RFkrmjQ4WAy$;O|D$&YpRW2Mm}3C1J');
define('NONCE_KEY',        'y<e0G g@?wtmb`o7;ah[s{$G9H/*R=Js5~fzEhW6m=47vwZ+bYh/.@saHI4>^FOJ');
define('AUTH_SALT',        'J[LFJW{G{(1utUqj!!aj(ut8<]M<I(}/$UVz@OqD*V-`Y=Q./_k<qo}yUn0idUYu');
define('SECURE_AUTH_SALT', ']#.ISzkEi)SRYHnNMiOgoz6&Yqo$!Hv:I;JNqs&PC@??|+^2|+!X?KtfEpTohelz');
define('LOGGED_IN_SALT',   'N$~EDrr5}Ym9-BTF)SIbVEsXAw%@fJosZ:g;vLMu_RVq1tZnWN/fswYWjur<yv=3');
define('NONCE_SALT',       '|D8Py/CT8E-%p-]21S&Z-vAJ$Wi<d@8}JGMO:g^eGSA$]t~x}2:u>MVpY4BQ(>?M');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);

/* Add any custom values between this line and the "stop editing" line. */
define('WP_ENVIRONMENT_TYPE', 'development');

/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
