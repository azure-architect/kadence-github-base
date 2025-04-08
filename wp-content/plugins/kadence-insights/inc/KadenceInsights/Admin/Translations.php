<?php
/**
 * Handles all functionality related to the Database Events.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */

declare( strict_types=1 );

namespace KadenceWP\Insights\Admin;

/**
 * Handles all functionality related to the settings page.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */
class Translations {

	/**
	 * Plugin specific text-domain loader.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		// Load the default language files.
		load_plugin_textdomain( 'kadence-insights', false, KADENCE_INSIGHTS_PATH . 'languages/' );
	}
}
