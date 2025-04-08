<?php
/**
 * The provider hooking Analytics class methods to WordPress events.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */

namespace KadenceWP\Insights\Analytics;

use KadenceWP\Insights\Contracts\Service_Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all Analytics related functionality.
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
		add_action( 'plugins_loaded', $this->container->callback( Database_Events::class, 'custom_tables_init' ), 40 );
		// Events.
		add_action( 'wp_ajax_kadence_ab_test_event', $this->container->callback( Events_Util::class, 'log_ab_event' ) );
		add_action( 'wp_ajax_nopriv_kadence_ab_test_event', $this->container->callback( Events_Util::class, 'log_ab_event' ) );
		// Sales Events.
		add_action( 'wp_ajax_kadence_ab_sales_event', $this->container->callback( Events_Util::class, 'log_sales_event' ) );
		add_action( 'wp_ajax_nopriv_kadence_ab_sales_event', $this->container->callback( Events_Util::class, 'log_sales_event' ) );
		// Analytics.
		add_action( 'admin_menu', $this->container->callback( Dashboard::class, 'add_single_analytics_view' ) );
		add_action( 'wp_ajax_kadence_insights_get_analytics_data', $this->container->callback( Dashboard::class, 'get_analytics_data' ) );
		add_filter( 'submenu_file', $this->container->callback( Dashboard::class, 'hide_analytics_submenu' ) );
		add_filter( 'manage_kadence_ab_test_posts_columns', $this->container->callback( Dashboard::class, 'filter_post_type_columns' ) );
		add_action( 'manage_kadence_ab_test_posts_custom_column', $this->container->callback( Dashboard::class, 'render_post_type_column' ), 10, 2 );
		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'init', $this->container->callback( Woocommerce::class, 'on_init' ) );
			add_action( 'woocommerce_checkout_order_created', $this->container->callback( Woocommerce::class, 'save_classic_checkout' ), 10, 2 );
			add_action( 'kadence_order_save_insights_data', $this->container->callback( Woocommerce::class, 'save_meta_data' ), 10, 2 );
			add_action( 'admin_init', $this->container->callback( Woocommerce::class, 'export_orders_to_csv' ) );
		}
		if ( class_exists( 'SureCart' ) ) {
			add_action( 'init', $this->container->callback( Surecart::class, 'on_init' ) );
		}
	}
}
