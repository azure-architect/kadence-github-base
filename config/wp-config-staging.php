<?php

/**
 * WordPress Template Configuration File
 *
 * This file contains the base configuration for WordPress, customized for
 * {{CLIENT_NAME}} in the staging environment.
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', '{{DB_NAME}}');

/** Database username */
define('DB_USER', '{{DB_USER}}');

/** Database password */
define('DB_PASSWORD', '{{DB_PASSWORD}}');

/** Database hostname */
define('DB_HOST', '{{DB_HOST}}');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * These will be replaced for each client automatically by the setup script.
 */
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

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
 * Staging environment has limited debugging, enabled for administrators only.
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);

/**
 * Disable automatic updates and file edits in the admin
 */
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);

/**
 * Define site URL and home URL for staging environment
 */
define('WP_SITEURL', 'https://staging.{{CLIENT_SLUG}}.com');
define('WP_HOME', 'https://staging.{{CLIENT_SLUG}}.com');

/**
 * Memory limits
 */
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

/**
 * Environment-specific settings
 */
define('WP_ENVIRONMENT_TYPE', 'staging');

/**
 * Limit revisions to reduce database size
 */
define('WP_POST_REVISIONS', 5);

/**
 * Prevent staging sites from sending emails
 * Uncomment the following line if you want to disable emails
 */
// define( 'WPMS_ON', true );
// define( 'WPMS_MAIL_FROM', 'no-reply@{{CLIENT_SLUG}}.com' );
// define( 'WPMS_MAILER', 'smtp' );
// define( 'WPMS_SET_RETURN_PATH', 'false' );

/**
 * Client-specific constants (to be customized)
 */
define('CLIENT_NAME', '{{CLIENT_NAME}}');
define('ENVIRONMENT', 'staging');

/**
 * That's all, stop editing! Happy publishing.
 */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
  define('ABSPATH', dirname(__FILE__) . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
