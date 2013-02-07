<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'formBuilder');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'A+#S4>(}l;O};`lcIv8pw6*lH||5I-}yDz(O0`jZ5P48_uyg-7j&3<LCc2c(FFfc');
define('SECURE_AUTH_KEY',  'odP6bsLiG{!ZtvJ|@;KRQe$O;Ai+*S]c)kL(@#,ok#Mq?9&nzQxk{`SCcMX[OvQ!');
define('LOGGED_IN_KEY',    'Q8vMyhr[6 +x^m93eXQAf+n>@qB ,R,+7KL#2#z(s/zGbw|h1t_.Frj~b$s_?x>f');
define('NONCE_KEY',        'AijqV8.D8;*kxO:R%>_Ho8-v:VUx^3C7V<S02Ez8.`]t$_j#twzQc}sjDLY2/Z_}');
define('AUTH_SALT',        ';e 9!T; HUueJI1_/kLH]93{O)_3A=TlRp{LgfO8bn<jzdodpSkqM^-yZT1D6G@z');
define('SECURE_AUTH_SALT', '?jn>;^i(Ojszr&=85B6)t,;iJy?<z_!> ;|eDwv;k3LTwn=H$n*|Ia)19({{f(,+');
define('LOGGED_IN_SALT',   'zn70NFlK]}{&hlw`Vu`^pF2wg;b8e),^`Uv]b5?q)_}sT4+M_*{o5@F0#!F!&7Zf');
define('NONCE_SALT',       '-EOn=jYmWM$x-H`!Y[:s|F^AToYcAgHXdmh#n8}X1I8[v0({|6cfuL/[CY:FMCy!');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
