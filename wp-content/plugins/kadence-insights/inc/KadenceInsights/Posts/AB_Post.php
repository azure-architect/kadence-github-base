<?php
/**
 * The provider hooking Admin class methods to WordPress events.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */

namespace KadenceWP\Insights\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all functionality related to the AB Post.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */
class AB_Post {
	const SLUG = 'kadence_ab_test';
	const CACHE_KEY_PREFIX = 'kad_ab_block_usage_';

	/**
	 * Registers the Conversion post type.
	 */
	public function register_post_type() {
		$labels  = array(
			'name'                  => __( 'A/B Test Campaign', 'kadence-insights' ),
			'singular_name'         => __( 'A/B Test Item', 'kadence-insights' ),
			'menu_name'             => _x( 'A/B Tests', 'Admin Menu text', 'kadence-insights' ),
			'add_new'               => _x( 'Add New', 'A/B Test Item', 'kadence-insights' ),
			'add_new_item'          => __( 'Add New A/B Test', 'kadence-insights' ),
			'new_item'              => __( 'New A/B Test', 'kadence-insights' ),
			'edit_item'             => __( 'Edit A/B Test', 'kadence-insights' ),
			'view_item'             => __( 'View A/B Test', 'kadence-insights' ),
			'all_items'             => __( 'A/B Tests', 'kadence-insights' ),
			'search_items'          => __( 'Search A/B Tests', 'kadence-insights' ),
			'parent_item_colon'     => __( 'Parent A/B Test:', 'kadence-insights' ),
			'not_found'             => __( 'No A/B Tests found.', 'kadence-insights' ),
			'not_found_in_trash'    => __( 'No A/B Tests found in Trash.', 'kadence-insights' ),
			'archives'              => __( 'A/B Test archives', 'kadence-insights' ),
			'insert_into_item'      => __( 'Insert into A/B Test', 'kadence-insights' ),
			'uploaded_to_this_item' => __( 'Uploaded to this A/B Test', 'kadence-insights' ),
			'filter_items_list'     => __( 'Filter A/B Tests list', 'kadence-insights' ),
			'items_list_navigation' => __( 'A/B Tests list navigation', 'kadence-insights' ),
			'items_list'            => __( 'A/B Tests list', 'kadence-insights' ),
		);
		$rewrite = apply_filters( 'kadence_ab_test_post_type_url_rewrite', array( 'slug' => 'kadence-ab-test' ) );
		$args = [
			'labels'             => $labels,
			'description'        => __( 'No-code solution for A/B testing content on your site', 'kadence-insights' ),
			'public'             => false,
			'publicly_queryable' => false,
			'has_archive'        => false,
			'exclude_from_search'=> true,
			'show_ui'            => true,
			'show_in_menu'       => 'kadence-insights',
			'menu_icon'          => $this->get_icon_svg(),
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'can_export'         => true,
			'show_in_rest'       => true,
			'rewrite'            => $rewrite,
			'rest_base'          => self::SLUG,
			'rest_controller_class' => AB_Post_REST_Controller::class,
			'capabilities'          => array(
				'edit_others_posts'      => 'edit_theme_options',
				'delete_posts'           => 'edit_theme_options',
				'publish_posts'          => 'edit_theme_options',
				'create_posts'           => 'edit_theme_options',
				'read_private_posts'     => 'edit_theme_options',
				'delete_private_posts'   => 'edit_theme_options',
				'delete_published_posts' => 'edit_theme_options',
				'delete_others_posts'    => 'edit_theme_options',
				'edit_private_posts'     => 'edit_theme_options',
				'edit_published_posts'   => 'edit_theme_options',
				'edit_posts'             => 'edit_theme_options',
			),
			'map_meta_cap'       => true,
			'supports'           => [
				'title',
				'editor',
				'author',
				'custom-fields',
				'revisions',
			],
		];
		register_post_type( self::SLUG, $args );
	}
	/**
	 * Returns a base64 URL for the SVG for use in the menu.
	 *
	 * @param  bool $base64 Whether or not to return base64-encoded SVG.
	 * @return string
	 */
	private function get_icon_svg( $base64 = true ) {
		$svg = '<svg width="100%" height="100%" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M12.408,2.759c-0.146,-0.185 -0.233,-0.418 -0.233,-0.671c0,-0.599 0.486,-1.085 1.085,-1.085c0.599,-0 1.085,0.486 1.085,1.085c0,0.525 -0.374,0.963 -0.87,1.063l-1.65,4.768c0.122,0.175 0.194,0.389 0.194,0.619c-0,0.599 -0.486,1.085 -1.085,1.085c-0.599,-0 -1.085,-0.486 -1.085,-1.085c-0,-0.095 0.012,-0.187 0.035,-0.274l-1.197,-1.497l0.005,-0.007c-0.013,0.002 -0.027,0.003 -0.041,0.004l-1.503,2.446c0.103,0.166 0.162,0.361 0.162,0.571c0,0.599 -0.486,1.085 -1.085,1.085c-0.598,0 -1.085,-0.486 -1.085,-1.085c0,-0.565 0.434,-1.03 0.986,-1.08l1.513,-2.462c-0.097,-0.163 -0.153,-0.354 -0.153,-0.557c-0,-0.599 0.486,-1.085 1.085,-1.085c0.598,-0 1.085,0.486 1.085,1.085c-0,0.129 -0.023,0.253 -0.065,0.369l1.137,1.421c0.017,-0.003 0.034,-0.006 0.051,-0.008l1.629,-4.705Zm-6.183,6.374c0.358,0 0.648,0.291 0.648,0.648c0,0.358 -0.29,0.648 -0.648,0.648c-0.357,-0 -0.647,-0.29 -0.647,-0.648c-0,-0.357 0.29,-0.648 0.647,-0.648Zm4.709,-1.243c0.357,0 0.648,0.29 0.648,0.648c-0,0.357 -0.291,0.647 -0.648,0.647c-0.357,0 -0.648,-0.29 -0.648,-0.647c0,-0.358 0.291,-0.648 0.648,-0.648Zm-2.363,-2.856c0.357,0 0.647,0.29 0.647,0.648c0,0.357 -0.29,0.647 -0.647,0.647c-0.358,0 -0.648,-0.29 -0.648,-0.647c-0,-0.358 0.29,-0.648 0.648,-0.648Zm4.689,-3.594c0.358,0 0.648,0.29 0.648,0.648c-0,0.357 -0.29,0.647 -0.648,0.647c-0.357,0 -0.647,-0.29 -0.647,-0.647c-0,-0.358 0.29,-0.648 0.647,-0.648Z"/><path d="M2.207,3.209l-0.4,0.401l-0.804,-0.804l1.803,-1.803l0.005,0.005l0.005,-0.005l1.802,1.803l-0.804,0.804l-0.47,-0.471l0,9.482l9.482,-0l-0.436,-0.435l0.804,-0.804l1.803,1.802l-0.005,0.005l0.005,0.005l-1.803,1.803l-0.804,-0.804l0.436,-0.435l-10.619,-0l-0,-10.549Z"/></svg>';
		if ( $base64 ) {
			return 'data:image/svg+xml;base64,' . base64_encode( $svg );
		}

		return $svg;
	}
	/**
	 * Check that user can edit these.
	 */
	public function meta_auth_callback() {
		return current_user_can( 'edit_others_pages' );
	}
	/**
	 * Register Post Meta options
	 */
	public function register_meta() {
		register_post_meta(
			self::SLUG,
			'_kad_ab_variants',
			[
				'single'        => true,
				'type'          => 'array',
				'default'       => [],
				'auth_callback' => [ $this, 'meta_auth_callback' ],
				'show_in_rest'  => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'variantID' => [ 'type' => 'string' ],
								'name'      => [ 'type' => 'string' ],
							],
						],
					],
				],
			]
		);
		register_post_meta(
			self::SLUG,
			'_kad_ab_variants_count',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'integer',
				'default'       => 1,
				'auth_callback' => [ $this, 'meta_auth_callback' ],
			]
		);
		register_post_meta(
			self::SLUG,
			'_kad_ab_goals',
			[
				'single'        => true,
				'type'          => 'array',
				'default'       => [
					[
						'type'   => 'click',
						'label'  => '',
						'class'  => '',
						'time'   => 20,
						'slug'   => '',
						'target' => 'inside',
					],
				],
				'auth_callback' => [ $this, 'meta_auth_callback' ],
				'show_in_rest'  => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'type'   => [ 'type' => 'string' ],
								'label'  => [ 'type' => 'string' ],
								'class'  => [ 'type' => 'string' ],
								'time'   => [ 'type' => 'number' ],
								'slug'   => [ 'type' => 'string' ],
								'target' => [ 'type' => 'string' ],
							],
						],
					],
				],
			]
		);
		register_post_meta(
			self::SLUG,
			'_kad_ab_custom_classes',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => [ $this, 'meta_auth_callback' ],
			]
		);
		register_post_meta(
			self::SLUG,
			'_kad_ab_usage_locations',
			[
				'single'        => true,
				'type'          => 'array',
				'sanitize_callback' => [ $this, 'sanitize_block_usage_locations' ],
				'auth_callback' => [ $this, 'meta_auth_callback' ],
				'show_in_rest'  => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'integer',
						],
					],
				],
			]
		);
	}
	/**
	 * Sanitize the block usage locations meta field.
	 *
	 * @param mixed $meta_value The meta value to sanitize.
	 * @return array The sanitized meta value.
	 */
	public function sanitize_block_usage_locations( $meta_value ) {
		if ( ! is_array( $meta_value ) ) {
			return [];
		}

		// Ensure all values in the array are integers.
		return array_map( 'intval', $meta_value );
	}
	/**
	 * Add filters for element content output.
	 */
	public function gutenberg_template() {
		$post_type_object               = get_post_type_object( self::SLUG );
		$post_type_object->template     = [
			[
				'kadence-insights/ab-test',
			],
		];
		$post_type_object->template_lock = 'all';
	}
	/**
	 * Filters the capabilities of a user to conditionally grant them capabilities for managing ab tests.
	 *
	 * Any user who can 'edit_others_pages' will have access to manage ab tests.
	 *
	 * @param array $allcaps A user's capabilities.
	 * @return array Filtered $allcaps.
	 */
	public function filter_post_type_user_caps( $allcaps ) {
		if ( isset( $allcaps['edit_others_pages'] ) ) {
			$allcaps['edit_kadence_ab_test']             = $allcaps['edit_others_pages'];
			$allcaps['edit_others_kadence_ab_test']      = $allcaps['edit_others_pages'];
			$allcaps['edit_published_kadence_ab_test']   = $allcaps['edit_others_pages'];
			$allcaps['edit_private_kadence_ab_test']     = $allcaps['edit_others_pages'];
			$allcaps['delete_kadence_ab_test']           = $allcaps['edit_others_pages'];
			$allcaps['delete_others_kadence_ab_test']    = $allcaps['edit_others_pages'];
			$allcaps['delete_published_kadence_ab_test'] = $allcaps['edit_others_pages'];
			$allcaps['delete_private_kadence_ab_test']   = $allcaps['edit_others_pages'];
			$allcaps['publish_kadence_ab_test']          = $allcaps['edit_others_pages'];
			$allcaps['read_private_kadence_ab_test']     = $allcaps['edit_others_pages'];
		}
		return $allcaps;
	}
	/**
	 * Renders the single template on the front end.
	 *
	 * @param array $layout the layout array.
	 */
	public function kadence_editor_single_layout( $layout ) {
		global $post;
		if ( is_singular( self::SLUG ) || ( is_admin() && is_object( $post ) && self::SLUG === $post->post_type ) ) {
			$layout = wp_parse_args(
				array(
					'layout'           => 'fullwidth',
					'boxed'            => 'unboxed',
					'feature'          => 'hide',
					'feature_position' => 'above',
					'comments'         => 'hide',
					'navigation'       => 'hide',
					'title'            => 'hide',
					'transparent'      => 'disable',
					'sidebar'          => 'disable',
					'vpadding'         => 'hide',
					'footer'           => 'disable',
					'header'           => 'disable',
					'content'          => 'enable',
				),
				$layout
			);
		}

		return $layout;
	}
	/**
	 * Setup the post select API endpoint.
	 *
	 * @return void
	 */
	public function register_api_endpoints() {
		$posts_controller = new AB_Post_REST_Controller();
		$posts_controller->register_routes();
	}
	/**
	 * Enqueue Title Styles
	 */
	public function title_styles_enqueue() {

		$post_type = get_post_type();
		if ( self::SLUG !== $post_type ) {
			return;
		}
		$output = '.post-type-kadence_ab_test.block-editor-page .editor-styles-wrapper .editor-post-title__block .editor-post-title__input, .post-type-kadence_ab_test .edit-post-visual-editor__post-title-wrapper {
	font-size: 1.5em;
    line-height: 1;
    padding-left: 0.5em;
    padding-right: 0.5em;
    border: 1px solid var(--wp-admin-theme-color);
	margin-top: 0 !important;
	padding-top: 24px;
    padding-bottom: 20px;
    margin-bottom: 20px;
	font-size: 1em;
	position: relative;
}
.post-type-kadence_ab_test .editor-styles-wrapper .edit-post-visual-editor__post-title-wrapper:not(.specificity)  {
	padding-top: 24px !important;
    padding-bottom: 20px !important;
    margin-bottom: 20px !important;
    margin-top: 0 !important;
	font-size: 1em;
	position: relative;
}
.post-type-kadence_ab_test .editor-styles-wrapper .edit-post-visual-editor__post-title-wrapper .editor-post-title:before, .post-type-kadence_ab_test .edit-post-visual-editor__post-title-wrapper:before {
    content: "Title";
    position: absolute;
    top: 0px;
    left: 0;
    font-size: 12px;
    font-weight: normal;
    line-height: 1;
    background: var(--wp-admin-theme-color);
    padding: 4px 6px;
    color: white;
    text-transform: uppercase;
}
	/* Iframe CSS */
.post-type-kadence_ab_test .edit-post-visual-editor__post-title-wrapper .editor-post-title {
	font-size: 1.2em;
    font-weight: 500;
    line-height: 1;
	margin: 0;
}
.post-type-kadence_ab_test .editor-styles-wrapper .edit-post-visual-editor__post-title-wrapper .editor-post-title {
	font-size: 1.2em;

    font-weight: 500;
    line-height: 1;
}
.post-type-kadence_ab_test .editor-styles-wrapper {
	padding:8px;
	margin: 0;
}
	.post-type-kadence_ab_test .is-root-container {
		padding: 0 !important;
	}
.post-type-kadence_ab_test .is-root-container > .wp-block {max-width: none;} .post-type-kadence_ab_test .is-root-container > .wp-block.wp-block-kadence-header.wp-block-kadence-header.wp-block-kadence-header.wp-block-kadence-header.wp-block-kadence-header.wp-block-kadence-header:not(.specificity) {
    max-width: none !important;
    margin-left: unset !important;
    margin-right: unset !important;
}.post-type-kadence_ab_test .editor-styles-wrapper .is-root-container > .wp-block {max-width: none;}
.post-type-kadence_ab_test .editor-styles-wrapper .has-global-padding {
	padding: 0;
}
:where(.post-type-kadence_ab_test) :where(.wp-block) {max-width: none;} :where(.post-type-kadence_ab_test) :where(.editor-styles-wrapper) :where(.wp-block) {max-width: none;}';
		wp_register_style( 'kadence_ab_test_css', false );
		wp_enqueue_style( 'kadence_ab_test_css' );
		wp_add_inline_style( 'kadence_ab_test_css', $output );
	}
	/**
	 * Enqueue the admin post list scripts.
	 */
	public function admin_enqueue_scripts() {
		$output = 'button.refresh-usage-button {
    appearance: none;
    border: 0;
    padding: 4px;
    display: inline-flex;
    background: #f2f2f2;
    color: #444;
    font-size: 13px;
    border-radius: 4px;
    align-items: center;
}

button.refresh-usage-button .dashicons {
    font-size: 10px;
    line-height: 10px;
    height: auto;
    width: auto;
}
button.refresh-usage-button:disabled {
	opacity: 0.5;
}
button.refresh-usage-button.loading .dashicons {
	animation: kb-insights-rotate 2s linear infinite;
}
.kad-ab-usage {
    display: flex;
    gap: 10px;
    align-items: center;
}@keyframes kb-insights-rotate {
  from {
    transform: rotate(360deg);
  }
  to {
    transform: rotate(0deg);
  }
}';
		wp_register_script( 'kadence-insights-ab-post-list', KADENCE_INSIGHTS_URL . 'assets/js/kadence-admin-ab-test-list.min.js', array( 'jquery' ), KADENCE_INSIGHTS_VERSION, true );
		wp_localize_script( 'kadence-insights-ab-post-list', 'refreshUsageData', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'refresh_usage_nonce' ),
		] );
		wp_register_style( 'kadence-insights-ab-post-list', false );
		wp_add_inline_style( 'kadence-insights-ab-post-list', $output );
	}
	/**
	 * Filters the block area post type columns in the admin list table.
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns Columns to display.
	 * @return array Filtered $columns.
	 */
	public function add_usage_column( array $columns ) : array {

		$add = array(
			'usage' => esc_html__( 'Usage Locations', 'kadence-insights' ),
		);

		$new_columns = array();
		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;
			if ( 'title' == $key ) {
				$new_columns = array_merge( $new_columns, $add );
			}
		}

		return $new_columns;
	}

	/**
	 * Handle the ajax request to refresh the location cache.
	 */
	public function ajax_update_usage_column() {
		 // Verify nonce for security
		 if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'refresh_usage_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce. Permission denied.' ] );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'You are not allowed to perform this action.' ] );
		}
	
		$post_id = intval( $_POST['post_id'] ?? 0 );
		if ( ! $post_id ) {
			wp_send_json_error( [ 'message' => 'Invalid post ID.' ] );
		}

		$locations = $this->get_ab_block_usage( $post_id );
		 // Prepare the updated usage locations data
		 $formatted_locations = [];
		 if ( $locations ) {
			 foreach ( $locations as $location ) {
				 $formatted_locations[] = [
					 'edit_link' => get_edit_post_link( $location ),
					 'title'     => get_the_title( $location ),
				 ];
			 }
		 }
	 
		 // Return the updated usage locations
		 wp_send_json_success( [ 'locations' => $formatted_locations ] );
	}
	/** 
	 * Populate the custom column with usage data
	 */
	public function populate_usage_column( $column, $post_id ) {
		if ( 'usage' === $column ) {
			wp_enqueue_style( 'kadence-insights-ab-post-list' );
			wp_enqueue_script( 'kadence-insights-ab-post-list' );
			echo '<div class="kad-ab-usage">';
			echo '<div class="kad-ab-usage-items">';
			$locations = $this->get_block_usage_from_meta( $post_id );
	
			if ( $locations && is_array( $locations ) ) {
				foreach ( $locations as $location ) {
					echo '<a href="' . esc_url( get_edit_post_link( $location ) ) . '" target="_blank">' . esc_html( get_the_title( $location ) ) . '</a><br>';
				}
			} else {
				echo 'Not used';
			}
			echo '</div>';
			echo '<div class="kad-ab-usage-reload">';
			echo '<button class="refresh-usage-button" data-post-id="' . esc_attr( $post_id ) . '" title="' . esc_html__( 'Refresh', 'kadence-insights' ) . '"><span class="dashicons dashicons-image-rotate"></span></button>';
			echo '</div>';
			echo '</div>';
		}
	}
	/**
	 * Get block usage locations from post meta.
	 *
	 * @param int $block_post_id The ID of the block (custom post).
	 * @return array List of post IDs where the block is used.
	 */
	function get_block_usage_from_meta( $block_post_id ) {
		$locations = get_post_meta( $block_post_id, '_kad_ab_usage_locations', true );
		return $locations && is_array( $locations ) ? $locations : [];
	}
	/**
	 * Hook for scheduled event to process block usage
	 */
	public function get_ab_block_usage( $block_post_id ) {
		$locations = $this->process_block_usage_in_batches( $block_post_id );
		update_post_meta( $block_post_id, '_kad_ab_usage_locations', $locations );

		return $locations;
	}
	/**
	 * Hook for scheduled event to process block usage
	 */
	public function update_ab_block_usage( $block_post_id ) {
		$locations = $this->process_block_usage_in_batches( $block_post_id );
		update_post_meta( $block_post_id, '_kad_ab_usage_locations', $locations );
	}

	/**
	 * Process block usage in batches.
	 *
	 * @param int $block_post_id The ID of the block (custom post).
	 * @return array List of post IDs where the block is used.
	 */
	public function process_block_usage_in_batches( $block_post_id ) {
		global $wpdb;
		$block_pattern = sprintf( '<!-- wp:kadence-insights/ab-test {"id":%d', $block_post_id );
		$batch_size = 100;
		$offset = 0;
		$locations = [];

		do {
			$results = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} 
					WHERE post_status = 'publish' AND post_content LIKE %s 
					LIMIT %d OFFSET %d",
					'%' . $wpdb->esc_like( $block_pattern ) . '%',
					$batch_size,
					$offset
				)
			);

			if ( $results ) {
				$locations = array_merge( $locations, $results );
			}

			$offset += $batch_size;
		} while ( count( $results ) === $batch_size );

		return $locations;
	}

	/**
	 * Refresh all cached block usage data periodically.
	 */
	public function run_usage_schedule() {
		if ( ! wp_next_scheduled( 'refresh_all_insights_ab_block_usages' ) ) {
			wp_schedule_event( time(), 'daily', 'refresh_all_insights_ab_block_usages' );
		}
	}

	/** 
	 * Hook for scheduled event to refresh all block usage caches
	 */
	public function refresh_all_block_usages() {
		$block_query = new WP_Query( [
			'post_type'      => self::SLUG,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		] );

		if ( $block_query->have_posts() ) {
			foreach ( $block_query->posts as $block_post_id ) {
				$locations = $this->process_block_usage_in_batches( $block_post_id );
				update_post_meta( $block_post_id, '_kad_ab_usage_locations', $locations );
			}
		}
		wp_reset_postdata();
	}

}
