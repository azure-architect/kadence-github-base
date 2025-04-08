<?php
/**
 * WordPress Template Configuration File
 *
 * This file contains the base configuration for WordPress, customized for
 * {{CLIENT_NAME}} in the local development environment.
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '{{DB_NAME}}' );

/** Database username */
define( 'DB_USER', '{{DB_USER}}' );

/** Database password */
define( 'DB_PASSWORD', '{{DB_PASSWORD}}' );

/** Database hostname */
define( 'DB_HOST', '{{DB_HOST}}' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * These will be replaced for each client automatically by the setup script.
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

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
 * Local environment has debugging enabled by default.
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'SCRIPT_DEBUG', true );

/**
 * Disable automatic updates and file edits
 */
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'DISALLOW_FILE_EDIT', false );  // Allow file edits in local environment

/**
 * Define site URL and home URL for local environment
 */
define( 'WP_SITEURL', 'http://{{CLIENT_SLUG}}.local' );
define( 'WP_HOME', 'http://{{CLIENT_SLUG}}.local' );

/**
 * Memory limits
 */
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );

/**
 * Environment-specific settings
 */
define( 'WP_ENVIRONMENT_TYPE', 'local' );

/**
 * Local environment-specific settings
 */
define( 'FS_METHOD', 'direct' );  // Use direct file system access in local environment

/**
 * Custom content directory (optional)
 * Uncomment to use a custom content directory path
 */
// define( 'WP_CONTENT_DIR', dirname(__FILE__) . '/wp-content' );
// define( 'WP_CONTENT_URL', WP_HOME . '/wp-content' );

/**
 * Custom plugin directory (optional)
 * Uncomment to use a custom plugin directory path
 */
// define( 'WP_PLUGIN_DIR', dirname(__FILE__) . '/wp-content/plugins' );
// define( 'WP_PLUGIN_URL', WP_HOME . '/wp-content/plugins' );

/**
 * Client-specific constants (to be customized)
 */
define( 'CLIENT_NAME', '{{CLIENT_NAME}}' );
define( 'ENVIRONMENT', 'local' );

/**
 * That's all, stop editing! Happy publishing.
 */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';