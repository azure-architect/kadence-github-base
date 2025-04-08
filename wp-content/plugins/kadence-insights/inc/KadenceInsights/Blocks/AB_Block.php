<?php
/**
 * Handles all functionality related to the A/B Testing Block
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */

declare( strict_types=1 );

namespace KadenceWP\Insights\Blocks;

/**
 * Handles all functionality related to the A/B Testing Block.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */
class AB_Block {
	/**
	 * Holds ab_blocks data array.
	 *
	 * @var Data for all the ab_blocks.
	 */
	protected static $ab_blocks = [];
	/**
	 * Holds the meta attributes.
	 *
	 * @var array
	 */
	protected $meta_attributes = [];

	/**
	 * Holds all the rendered blocks so you don't render inside of another render.
	 *
	 * @var null
	 */
	private static $seen_refs = [];
	/**
	 * Get the asset file produced by wp scripts.
	 *
	 * @param string $filepath the file path.
	 * @return array
	 */
	public function get_asset_file( $filepath ) {
		$asset_path = KADENCE_INSIGHTS_PATH . $filepath . '.asset.php';

		return file_exists( $asset_path )
			? include $asset_path
			: array(
				'dependencies' => [ 'lodash', 'react', 'react-dom', 'wp-block-editor', 'wp-blocks', 'wp-data', 'wp-element', 'wp-i18n', 'wp-polyfill', 'wp-primitives', 'wp-api' ],
				'version'      => KADENCE_INSIGHTS_VERSION,
			);
	}
	/**
	 * Register the A/B Testing Block.
	 *
	 * @return void
	 */
	public function ab_testing_block() {
		// Load the default language files.
		$plugin_asset_meta = $this->get_asset_file( 'build/blocks-ab-test' );
		// Register the block.
		wp_register_script(
			'kadence-ab-test-block',
			KADENCE_INSIGHTS_URL . 'build/blocks-ab-test.js',
			$plugin_asset_meta['dependencies'],
			$plugin_asset_meta['version']
		);
		wp_register_style(
			'kadence-ab-test-block',
			KADENCE_INSIGHTS_URL . 'build/blocks-ab-test.css',
			[],
			$plugin_asset_meta['version']
		);
		register_block_type(
			'kadence-insights/ab-test',
			[
				'editor_script' => 'kadence-ab-test-block',
				'editor_style'  => 'kadence-ab-test-block',
				'render_callback' => [ $this, 'render_ab_test' ],
			]
		);
		register_block_type(
			'kadence-insights/ab-test-item',
			[
				'editor_script' => '',
				'editor_style'  => '',
				'render_callback' => [ $this, 'render_ab_test_item' ],
			]
		);
	}
	/**
	 * Render Inline CSS helper function
	 *
	 * @param array  $css the css for each rendered block.
	 * @param string $style_id the unique id for the rendered style.
	 * @param bool   $in_content the bool for whether or not it should run in content.
	 */
	public function render_inline_css( $css, $style_id, $in_content = false ) {
		if ( ! is_admin() ) {
			wp_register_style( $style_id, false );
			wp_enqueue_style( $style_id );
			wp_add_inline_style( $style_id, $css );
			if ( 1 === did_action( 'wp_head' ) && $in_content ) {
				wp_print_styles( $style_id );
			}
		}
	}
	/**
	 * Get meta attributes.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_meta_attributes( $post_id ) {

		if ( ! empty( $this->meta_attributes[ $post_id ] ) ) {
			return $this->meta_attributes[ $post_id ];
		}

		$post_meta = get_post_meta( $post_id );
		$optimize_meta = [];
		if ( is_array( $post_meta ) ) {
			foreach ( $post_meta as $meta_key => $meta_value ) {
				if ( strpos( $meta_key, '_kad_ab_' ) === 0 && isset( $meta_value[0] ) ) {
					$optimize_meta[ str_replace( '_kad_ab_', '', $meta_key ) ] = maybe_unserialize( $meta_value[0] );
				}
			}
		}

		if ( $this->meta_attributes[ $post_id ] = $optimize_meta ) {
			return $this->meta_attributes[ $post_id ];
		}

		return [];
	}
	/**
	 * Render A/B Test Block
	 *
	 * @param array    $attributes Blocks attribtues.
	 * @param string   $content    Block content.
	 * @param WP_Block $block      Block instance.
	 */
	public function render_ab_test( $attributes, $content, $block ) {
		if ( empty( $attributes['id'] ) ) {
			return '';
		}
		$ab_block = get_post( $attributes['id'] );

		if ( ! $ab_block || 'kadence_ab_test' !== $ab_block->post_type ) {
			return '';
		}

		if ( 'publish' !== $ab_block->post_status || ! empty( $ab_block->post_password ) ) {
			return '';
		}

		// Prevent a nav block from being rendered inside itself.
		if ( isset( self::$seen_refs[ $attributes['id'] ] ) ) {
			// WP_DEBUG_DISPLAY must only be honored when WP_DEBUG. This precedent
			// is set in `wp_debug_mode()`.
			$is_debug = WP_DEBUG && WP_DEBUG_DISPLAY;

			return $is_debug ?
				// translators: Visible only in the front end, this warning takes the place of a faulty block.
				__( '[block rendering halted]', 'kadence-insights' ) :
				'';
		}
		self::$seen_refs[ $attributes['id'] ] = true;
		$post_id = ( isset( $attributes['id'] ) ? $attributes['id'] : 'unset' );

		if ( ! isset( self::$ab_blocks[ $post_id ] ) ) {
			self::$ab_blocks[ $post_id ] = [];
		}
		$meta_attributes = $this->get_meta_attributes( $post_id );
		$ab_test_settings = [
			'name'       => esc_html( $ab_block->post_title ),
			'goals'      => ( ! empty( $meta_attributes['goals'] ) ? $meta_attributes['goals'] : [ [ 'type' => 'click', 'class' => '', 'name' => 'name', 'slug' => '', 'time' => 20 ] ] ),
			'id'         => strval( $post_id ),
			'variants'   => ! empty( $meta_attributes['variants'] ) ? $meta_attributes['variants'] : [],
		];
		self::$ab_blocks[ $post_id ] = array_merge( self::$ab_blocks[ $post_id ], $ab_test_settings );
		// Remove the ab testing so it doesn't try and re-render.
		$content = preg_replace( '/<!-- wp:kadence-insights\/ab-test {.*?} -->/', '', $ab_block->post_content );
		$content = str_replace( '<!-- wp:kadence-insights/ab-test  -->', '', $content );
		$content = str_replace( '<!-- wp:kadence-insights/ab-test -->', '', $content );
		$content = str_replace( '<!-- /wp:kadence-insights/ab-test -->', '', $content );
		// Handle embeds for ab testing block.
		global $wp_embed;
		$content = $wp_embed->run_shortcode( $content );
		$content = $wp_embed->autoembed( $content );
		$content = do_blocks( $content );

		unset( self::$seen_refs[ $attributes['id'] ] );

		$wrapper_classes = [ 'kadence-insights-ab-test' . $post_id ];
		if ( ! empty( $meta_attributes['custom_classes'] ) ) {
			$wrapper_classes[] = $meta_attributes['custom_classes'];
		}
		$wrapper_args = [
			'class' => implode( ' ', $wrapper_classes ),
		];
		$wrapper_attributes = get_block_wrapper_attributes( $wrapper_args );
		$content = sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			$content
		);
		// $style_id = 'kadence-ab-test-' . esc_attr( $post_id );
		// $css_class = Minified_CSS::get_instance();
		// if ( ! $css_class->has_styles( $style_id ) ) {
		// 	$css = $this->output_css( $css_class, $attributes, $post_id );
		// 	if ( ! empty( $css ) ) {
		// 		if ( doing_filter( 'the_content' ) ) {
		// 			$content = '<style>' . $css . '</style>' . $content;
		// 		} else {
		// 			$this->render_inline_css( $css, $style_id, true );
		// 		}
		// 	}
		// }
		wp_enqueue_style( 'kadence-ab-test' );
		wp_enqueue_script( 'kadence-ab-test' );
		return $content;
	}
	/**
	 * Output CSS styling for when output_css_when_rendered_outside_post_content
	 */
	public function output_css_when_rendered_outside_post_content( $block ) {
		if ( ! empty( $block['blockName'] ) && 'kadence-insights/ab-test' === $block['blockName'] ) {
			wp_enqueue_style( 'kadence-ab-test' );
		}
		return $block;
	}
	/**
	 * Output CSS styling for Ab Block
	 *
	 * @param array  $attr the block attributes.
	 * @param string $post_id the block post id.
	 */
	public function output_css( $css, $attributes, $post_id ) {
		$css->set_style_id( 'kadence-ab-test-' . esc_attr( $post_id ) );
		$css->set_selector( '.kadence-insights-ab-test' . $post_id . ':not(.kab-test-loaded) > *:not(:first-child)' );
		$css->add_property( 'display', 'none' );
		$css->set_selector( '.kadence-insights-ab-test' . $post_id . '.kab-test-loaded > *:not(.kad-ab-visible)' );
		$css->add_property( 'display', 'none' );

		return $css->css_output();
	}

	/**
	 * Render A/B Test Item Block
	 *
	 * @param array    $attributes Blocks attribtues.
	 * @param string   $content    Block content.
	 * @param WP_Block $block      Block instance.
	 */
	public function render_ab_test_item( $attributes, $content, $block ) {
		$variation_id       = ! empty( $attributes['variantID'] ) ? $attributes['variantID'] : '';
		$outer_classes      = [ 'kadence-ab-testing-item', 'kadence-ab-variation-' . $variation_id ];
		$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $outer_classes ) ] );
		return sprintf( '<div %1$s>%2$s</div>', $wrapper_attributes, $content );
	}
	/**
	 * Enqueue scripts and styles.
	 */
	public function register_scripts() {
		wp_register_script( 'kadence-ab-test', KADENCE_INSIGHTS_URL . 'assets/js/kadence-ab-test.min.js', [], KADENCE_INSIGHTS_VERSION, true );
		wp_register_style( 'kadence-ab-test', KADENCE_INSIGHTS_URL . 'build/style-blocks-ab-test.css', [], KADENCE_INSIGHTS_VERSION );
	}
	/**
	 * Enqueue script data.
	 */
	public function ab_test_data_enqueue() {
		$settings = get_option( 'kadence_insights' );
		if ( ! is_array( $settings ) ) {
			$settings = json_decode( $settings, true );
		}
		$enable_analytics = ( isset( $settings['enable_analytics'] ) && false == $settings['enable_analytics'] ) ? false : true;
		$google_analytics = ( isset( $settings['google_analytics'] ) && true == $settings['google_analytics'] ) ? true : false;
		wp_localize_script(
			'kadence-ab-test',
			'kadenceABConfig',
			[
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'          => wp_create_nonce( 'kadence_insights' ),
				'site_slug'           => apply_filters( 'kadence_insights_site_slug', sanitize_title( get_bloginfo( 'name' ) ) ),
				'items'               => wp_json_encode( self::$ab_blocks ),
				'gtag'                => apply_filters( 'kadence_insights_google_tracking_gtag', $google_analytics ),
				'analytics'           => apply_filters( 'kadence_insights_enable_local_tracking', $enable_analytics ),
				'do_not_track'        => apply_filters( 'kadence_insights_do_not_track_user', current_user_can( 'manage_options' ) ),
			]
		);
	}
	/**
	 * Make sure to pre-render the ab test blocks.
	 */
	public function frontend_inline_css() {
		if ( function_exists( 'has_blocks' ) && has_blocks( get_the_ID() ) ) {
			global $post;
			if ( ! is_object( $post ) ) {
				return;
			}
			$this->frontend_build_css( $post );
		}
	}
	/**
	 * Outputs extra css for blocks.
	 *
	 * @param object $post_object object of WP_Post.
	 */
	public function frontend_build_css( $post_object ) {
		if ( ! is_object( $post_object ) ) {
			return;
		}
		if ( ! method_exists( $post_object, 'post_content' ) ) {
			$blocks = parse_blocks( $post_object->post_content );
			if ( ! is_array( $blocks ) || empty( $blocks ) ) {
				return;
			}
			foreach ( $blocks as $indexkey => $block ) {
				if ( is_array( $block ) && isset( $block['blockName'] ) ) {
					if ( 'kadence-insights/ab-test' === $block['blockName'] ) {
						if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
							$blockattr = $block['attrs'];
							if ( isset( $blockattr['id'] ) ) {
								$ab_block = get_post( $blockattr['id'] );
								if ( $ab_block && 'kadence_ab_test' === $ab_block->post_type ) {
									$this->output_head_data( $block );
									$this->enqueue_inner_block_styles( $ab_block );
								}
							}
						}
					}
					if ( 'core/block' === $block['blockName'] ) {
						if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
							$blockattr = $block['attrs'];
							if ( isset( $blockattr['ref'] ) ) {
								$reusable_block = get_post( $blockattr['ref'] );
								if ( $reusable_block && 'wp_block' === $reusable_block->post_type ) {
									$reuse_data_block = parse_blocks( $reusable_block->post_content );
									$this->blocks_cycle_through( $reuse_data_block );
								}
							}
						}
					}
					if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
						$this->blocks_cycle_through( $block['innerBlocks'] );
					}
				}
			}
		}
	}
	/**
	 * Builds css for inner blocks
	 *
	 * @param array $inner_blocks array of inner blocks.
	 */
	public function blocks_cycle_through( $inner_blocks ) {
		foreach ( $inner_blocks as $in_indexkey => $inner_block ) {
			if ( is_array( $inner_block ) && isset( $inner_block['blockName'] ) ) {
				if ( 'kadence-insights/ab-test' === $inner_block['blockName'] ) {
					if ( isset( $inner_block['attrs'] ) && is_array( $inner_block['attrs'] ) ) {
						$blockattr = $inner_block['attrs'];
						if ( isset( $blockattr['id'] ) ) {
							$ab_block = get_post( $blockattr['id'] );
							if ( $ab_block && 'kadence_ab_test' === $ab_block->post_type ) {
								$this->output_head_data( $inner_block );
								$this->enqueue_inner_block_styles( $ab_block );
							}
						}
					}
				}
				if ( 'core/block' === $inner_block['blockName'] ) {
					if ( isset( $inner_block['attrs'] ) && is_array( $inner_block['attrs'] ) ) {
						$blockattr = $inner_block['attrs'];
						if ( isset( $blockattr['ref'] ) ) {
							$reusable_block = get_post( $blockattr['ref'] );
							if ( $reusable_block && 'wp_block' === $reusable_block->post_type ) {
								$reuse_data_block = parse_blocks( $reusable_block->post_content );
								// This is a block inside itself.
								if ( isset( $reuse_data_block[0] ) && isset( $reuse_data_block[0]['blockName'] ) && 'core/block' === $reuse_data_block[0]['blockName'] && isset( $reuse_data_block[0]['attrs'] ) && isset( $reuse_data_block[0]['attrs']['ref'] ) && $reuse_data_block[0]['attrs']['ref'] === $blockattr['ref'] ) {
									return;
								}
								$this->blocks_cycle_through( $reuse_data_block );
							}
						}
					}
				}
				if ( ! empty( $inner_block['innerBlocks'] ) && is_array( $inner_block['innerBlocks'] ) ) {
					$this->blocks_cycle_through( $inner_block['innerBlocks'] );
				}
			}
		}
	}
	/**
	 * Render Block CSS in Page Head.
	 *
	 * @param array $block the block data.
	 */
	public function output_head_data( $block ) {
		wp_enqueue_style( 'kadence-ab-test' );
		// if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
		// 	$attributes = $block['attrs'];
		// 	$post_id   = ! empty( $attributes['id'] ) ? $attributes['id'] : '';
		// 	if ( ! empty( $post_id ) ) {
		// 		$attributes = apply_filters( 'kadence_ab_test_render_block_attributes', $attributes );
		// 		$css_class = Minified_CSS::get_instance();
		// 		if ( ! $css_class->has_styles( 'kadence-ab-test-' . esc_attr( $post_id ) ) ) {
		// 			$this->output_css( $css_class, $attributes, $post_id );
		// 		}
		// 	}
		// }
	}
	/**
	 * Outputs the css content of the ab test.
	 *
	 * @param object $post the post object.
	 *
	 * @return void
	 */
	public function enqueue_inner_block_styles( $post ) {

		$content = $post->post_content;
		if ( ! $content ) {
			return;
		}
		if ( has_blocks( $content ) ) {
			if ( class_exists( 'Kadence_Blocks_Frontend' ) ) {
				$kadence_blocks = \Kadence_Blocks_Frontend::get_instance();
				if ( method_exists( $kadence_blocks, 'frontend_build_css' ) ) {
					$kadence_blocks->frontend_build_css( $post );
				}
				if ( class_exists( 'Kadence_Blocks_Pro_Frontend' ) ) {
					$kadence_blocks_pro = \Kadence_Blocks_Pro_Frontend::get_instance();
					if ( method_exists( $kadence_blocks_pro, 'frontend_build_css' ) ) {
						$kadence_blocks_pro->frontend_build_css( $post );
					}
				}
			}
			return;
		}
	}
}
