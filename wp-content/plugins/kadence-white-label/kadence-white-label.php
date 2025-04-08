<?php
/**
 * Plugin Name: Kadence White Label
 * Description: Control the branding of the Kadence Theme.
 * Version: 1.0.1
 * Author: Kadence WP
 * Author URI: http://kadencewp.com/
 * License: GPLv2 - http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kadence-white-label
 * Requires PHP: 7.4
 *
 * @package Kadence White Label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KADENCE_WHITE_PATH', realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR );
define( 'KADENCE_WHITE_URL', plugin_dir_url( __FILE__ ) );
define( 'KADENCE_WHITE_VERSION', '1.0.1' );

require_once plugin_dir_path( __FILE__ ) . 'vendor/vendor-prefixed/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

require_once KADENCE_WHITE_PATH . 'inc/uplink/Helper.php';
require_once KADENCE_WHITE_PATH . 'inc/uplink/Connect.php';
require_once KADENCE_WHITE_PATH . 'class-kadence-white-label.php';
require_once KADENCE_WHITE_PATH . 'class-kadence-white-label-icon.php';

/**
 * Load Plugin
 */
function kadence_white_init() {
	require_once KADENCE_WHITE_PATH . 'inc/class-kadence-white-label-settings.php';
}
add_action( 'plugins_loaded', 'kadence_white_init' );

/**
 * Load the plugin textdomain
 */
function kadence_white_label_lang() {
	load_plugin_textdomain( 'kadence-white-label', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'kadence_white_label_lang' );
