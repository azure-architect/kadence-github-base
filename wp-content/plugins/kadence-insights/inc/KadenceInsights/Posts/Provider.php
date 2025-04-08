<?php
/**
 * The provider hooking Admin class methods to WordPress events.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */

namespace KadenceWP\Insights\Posts;

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
		// Register the post type.
		add_action( 'init', $this->container->callback( AB_Post::class, 'register_post_type' ), 2 );
		// Register the meta settings for ab post.
		add_action( 'init', $this->container->callback( AB_Post::class, 'register_meta' ), 20 );
		// Define the ab post gutenberg template.
		add_action( 'init', $this->container->callback( AB_Post::class, 'gutenberg_template' ) );
		// Build user permissions settings.
		add_filter( 'user_has_cap', $this->container->callback( AB_Post::class, 'filter_post_type_user_caps' ) );
		add_action( 'refresh_all_insights_ab_block_usages', $this->container->callback( AB_Post::class, 'refresh_all_block_usages' ) );
		if ( is_admin() ) {
			// Filter Kadence Theme to give the correct admin editor layout.
			add_filter( 'kadence_post_layout', $this->container->callback( AB_Post::class, 'kadence_editor_single_layout' ), 99 );

			add_action( 'enqueue_block_assets', $this->container->callback( AB_Post::class, 'title_styles_enqueue' ) );
			add_filter( 'manage_kadence_ab_test_posts_columns', $this->container->callback( AB_Post::class, 'add_usage_column' ), 99 );
			// Populate the custom column with usage data.
			add_action( 'manage_kadence_ab_test_posts_custom_column', $this->container->callback( AB_Post::class, 'populate_usage_column' ), 10, 2 );
			add_action( 'admin_init', $this->container->callback( AB_Post::class, 'run_usage_schedule' ) );
			add_action( 'admin_enqueue_scripts', $this->container->callback( AB_Post::class, 'admin_enqueue_scripts' ) );
			add_action( 'wp_ajax_kad_insights_ab_refresh_block_usage', $this->container->callback( AB_Post::class, 'ajax_update_usage_column' ) );
		}
		// add_action( 'rest_api_init', $this->container->callback( AB_Post::class, 'register_api_endpoints' ) );
	}
}
