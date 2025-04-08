<?php
/**
 * Plugin Name: Kadence Insights - A/B Testing
 * Description: No-code solution for A/B testing content on your site.
 * Author: Kadence WP
 * Author URI: https://kadencewp.com
 * Version: 1.0.2
 * Text Domain: kadence-insights
 * Domain Path: /languages
 * License: GPLv2-or-later
 * Requires at least: 6.4
 * Requires PHP: 7.4
 *
 * @package KadenceWP\Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'KADENCE_INSIGHTS_PATH', realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR );
define( 'KADENCE_INSIGHTS_URL', plugin_dir_url( __FILE__ ) );
define( 'KADENCE_INSIGHTS_VERSION', '1.0.2' );
define( 'KADENCE_INSIGHTS_FILE', __FILE__ );

require_once KADENCE_INSIGHTS_PATH . 'vendor/autoload.php';
require_once KADENCE_INSIGHTS_PATH . 'vendor/vendor-prefixed/autoload.php';
require_once KADENCE_INSIGHTS_PATH . 'inc/uplink/Helper.php';
require_once KADENCE_INSIGHTS_PATH . 'inc/uplink/Connect.php';
require_once KADENCE_INSIGHTS_PATH . 'inc/functions/app.php';
require_once KADENCE_INSIGHTS_PATH . 'inc/KadenceSettings/load.php';

/**
 * Boots the plugin.
 *
 * @since 0.1.0
 *
 * @return void
 */
add_action(
	'plugins_loaded',
	static function (): void {
		// Fully boot the plugin and its service providers.
		$core = kadence_insights_plugin();
		$core->init();
	}
);
