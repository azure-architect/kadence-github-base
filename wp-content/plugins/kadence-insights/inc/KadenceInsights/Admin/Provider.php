<?php
/**
 * The provider hooking Admin class methods to WordPress events.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */

namespace KadenceWP\Insights\Admin;

use KadenceWP\Insights\Contracts\Service_Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all Admin related functionality.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */
class Provider extends Service_Provider {

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		// Always load translations.
		add_action( 'plugins_loaded', $this->container->callback( Translations::class, 'load_textdomain' ) );
		// Register the settings.
		add_action( 'after_setup_theme', $this->container->callback( Settings::class, 'add_sections' ), 20 );

		add_action( 'admin_menu', $this->container->callback( Settings::class, 'add_menu' ) );

	}
}
