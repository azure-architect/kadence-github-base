<?php
/**
 * Handles all functionality related to the Database Events.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */

declare( strict_types=1 );

namespace KadenceWP\Insights\Analytics;

use WP_Error;
use function get_woocommerce_currency;
use function get_woocommerce_currency_symbol;

/**
 * Handles all functionality related to the database events page.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */
class Dashboard {
	/**
	 * Log form events
	 */
	public function add_single_analytics_view() {
		$page = add_submenu_page( 'kadence-insights', __( 'AB Test Analytics', 'kadence-insights' ), '', 'edit_pages', 'kadence-insights-analytics', array( $this, 'analytics_output' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'scripts' ) );
	}
	/**
	 * Loads config page
	 */
	public function analytics_output() {
		?>
		<div class="wrap kadence_insights_dash">
			<div class="kadence_insights_dash_head_container">
				<h2 class="notices" style="display:none;"></h2>
				<div class="kadence_insights_dash_wrap">
					<div class="kadence_insights_welcome_title_head">
						<div class="kadence_insights_welcome_head_container">
							<div class="kadence_insights_welcome_logo">
								<img src="<?php echo KADENCE_INSIGHTS_URL . 'assets/kadence-logo.png'; ?>">
							</div>
							<div class="kadence_insights_dash_title">
								<h1>
									Kadence Insights - A/B Testing
								</h1>
							</div>
						</div>
					</div>
					<div class="kadence_insights_individual_analytics">
					</div>
				</div>
			</div>
		</div>
		<?php
	}
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
				'dependencies' => array( 'lodash', 'react', 'react-dom', 'wp-block-editor', 'wp-blocks', 'wp-data', 'wp-element', 'wp-i18n', 'wp-polyfill', 'wp-primitives', 'wp-api' ),
				'version'      => KADENCE_INSIGHTS_VERSION,
			);
	}
	/**
	 * Get data from the database.
	 *
	 * @param int    $test_id the test id.
	 * @param array  $variants the variants.
	 * @param array  $goals the goals.
	 * @param string $period_name the period name.
	 * @param int    $period_length the period length.
	 */
	public function get_database_data( $test_id, $variants, $goals, $period_name, $period_length, $device = 'any' ) {
		$is_error = false;
		$error_message = '';
		$data     = [
			'dates'        => [],
			'totalViews'   => [],
			'totalConvert' => [],
			'graphViews'   => [],
			'graphConvert' => [],
		];
		// Get Data.
		$data['dates'] = Events_Util::get_dates( $period_length );
		if ( is_wp_error( $data['dates'] ) ) {
			$is_error      = true;
			$error_message = $data['dates'];
		}
		$unique_total_views = [];
		$unique_graph_views = [];
		$unique_total_goals = [];
		$unique_graph_goals = [];
		$unique_total_sales = [];
		$unique_graph_sales = [];
		if ( $variants && is_array( $variants ) ) {
			foreach ( $variants as $variant ) {
				$unique_graph_views[ $variant['variantID'] ] = Events_Util::query_events( 'placed', $test_id, $variant['variantID'], $period_name, false, $device );
				if ( is_wp_error( $unique_graph_views[ $variant['variantID'] ] ) ) {
					$is_error      = true;
					$error_message = $unique_graph_views[ $variant['variantID'] ];
				}
				$unique_total_views[ $variant['variantID'] ] = Events_Util::total_events( 'placed', $test_id, $variant['variantID'], $period_name, false, $device );
				if ( is_wp_error( $unique_total_views[ $variant['variantID'] ] ) ) {
					$is_error      = true;
					$error_message = $unique_total_views[ $variant['variantID'] ];
				}
				$unique_total_goals[ $variant['variantID'] ] = [];
				$unique_graph_goals[ $variant['variantID'] ] = [];
				$unique_total_sales[ $variant['variantID'] ] = [];
				$unique_graph_sales[ $variant['variantID'] ] = [];
				if ( $goals && is_array( $goals ) ) {
					foreach ( $goals as $goal ) {
						if ( ! empty( $goal['type'] ) && 'sales' === $goal['type'] ) {
							$order_data = Woocommerce::get_orders_by_meta_id( '_kad_ab_variants', $test_id, $variant['variantID'], $period_name, $device );
							if ( is_wp_error( $order_data ) ) {
								$is_error      = true;
								$error_message = $order_data;
							} else {
								$unique_total_goals[ $variant['variantID'] ][ $goal['slug'] ] = $order_data['total_count'];
								$unique_graph_goals[ $variant['variantID'] ][ $goal['slug'] ] = $order_data['daily_counts'];
								$unique_total_sales[ $variant['variantID'] ][ $goal['slug'] ] = $order_data['total_sales'];
								$unique_graph_sales[ $variant['variantID'] ][ $goal['slug'] ] = $order_data['daily_sales'];
							}
						} else if ( ! empty( $goal['type'] ) && 'surecart' === $goal['type'] ) {
							$order_data = Surecart::get_orders_by_variant( $test_id, $variant['variantID'], $period_name, $device );
							if ( is_wp_error( $order_data ) ) {
								$is_error      = true;
								$error_message = $order_data;
							} else {
								$unique_total_goals[ $variant['variantID'] ][ $goal['slug'] ] = $order_data['total_count'];
								$unique_graph_goals[ $variant['variantID'] ][ $goal['slug'] ] = $order_data['daily_counts'];
								$unique_total_sales[ $variant['variantID'] ][ $goal['slug'] ] = $order_data['total_sales'];
								$unique_graph_sales[ $variant['variantID'] ][ $goal['slug'] ] = $order_data['daily_sales'];
							}
						} else {
							$unique_total_goals[ $variant['variantID'] ][ $goal['slug'] ] = Events_Util::total_events( 'converted', $test_id, $variant['variantID'], $period_name, $goal['slug'], $device );
							if ( is_wp_error( $unique_total_goals[ $variant['variantID'] ][ $goal['slug'] ] ) ) {
								$is_error      = true;
								$error_message = $unique_total_goals[ $variant['variantID'] ][ $goal['slug'] ];
							}
							$unique_graph_goals[ $variant['variantID'] ][ $goal['slug'] ] = Events_Util::query_events( 'converted', $test_id, $variant['variantID'], $period_name, $goal['slug'], $device );
							if ( is_wp_error( $unique_graph_goals[ $variant['variantID'] ][ $goal['slug'] ] ) ) {
								$is_error      = true;
								$error_message = $unique_graph_goals[ $variant['variantID'] ][ $goal['slug'] ];
							}
						}
					}
				}
			}
			$data['totalViews']   = $unique_total_views;
			$data['totalConvert'] = $unique_total_goals;
			$data['graphViews']   = $unique_graph_views;
			$data['graphConvert'] = $unique_graph_goals;
			$data['totalSales']   = $unique_total_sales;
			$data['graphSales']   = $unique_graph_sales;
		}
		if ( $is_error ) {
			return $error_message;
		}
		return $data;
	}
	/**
	 * Get analytics data for period update.
	 */
	public function get_analytics_data() {
		check_ajax_referer( 'kadence-test-analytics-ajax-verification', 'security' );

		if ( ! current_user_can( 'edit_pages' ) || ! isset( $_POST['post_id'] ) ) {
			wp_send_json_error( __( 'Permissions issue, please reload the page.', 'kadence-insights' ) );
		}
		$test_id       = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
		$period        = ! empty( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : '';
		$period_length = ! empty( $_POST['length'] ) ? absint( wp_unslash( $_POST['length'] ) ) : '';
		$device 	   = ! empty( $_POST['device'] ) ? sanitize_text_field( wp_unslash( $_POST['device'] ) ) : 'any';

		if ( empty( $period_length ) ) {
			$period_length = 7;
		}
		if ( empty( $period ) ) {
			switch ( $period_length ) {
				case 30:
					$period = 'month';
					break;
				case 90:
					$period = 'quarter';
					break;
				default:
					$period = 'week';
					break;
			}
		}
		$variants = false;
		$goals    = false;
		if ( $test_id ) {
			$variants = get_post_meta( $test_id, '_kad_ab_variants', true );
			$goals    = get_post_meta( $test_id, '_kad_ab_goals', true );
		}
		if ( $variants && is_array( $variants ) && $goals && is_array( $goals ) ) {
			$data = $this->get_database_data( $test_id, $variants, $goals, $period, $period_length, $device );
			if ( is_wp_error( $data ) ) {
				wp_send_json_error( $data->get_error_message() );
			} else {
				$data['variants'] = $variants;
				$data['goals']    = $goals;
				wp_send_json( $data );
			}
		}
		wp_send_json_error( __( 'No data available.', 'kadence-insights' ) );
	}
	/**
	 * Add analytics scripts.
	 */
	public function scripts() {
		$test_id = isset( $_GET['view-item'] ) ? absint( $_GET['view-item'] ) : false;
		$plugin_asset_meta = $this->get_asset_file( 'build/analytics' );

		$variants = false;
		$goals    = false;
		if ( $test_id ) {
			$variants = get_post_meta( $test_id, '_kad_ab_variants', true );
			$goals    = get_post_meta( $test_id, '_kad_ab_goals', true );
		}
		// Register the script.
		wp_enqueue_script(
			'kadence-test-analytics',
			KADENCE_INSIGHTS_URL . 'build/analytics.js',
			$plugin_asset_meta['dependencies'],
			$plugin_asset_meta['version'],
			true
		);
		wp_enqueue_style(
			'kadence-test-analytics',
			KADENCE_INSIGHTS_URL . 'build/analytics.css',
			['wp-components'],
			$plugin_asset_meta['version']
		);
		$period_length = 30;
		$period_name = 'month';
		// $data = array(
		// 	'type'    => 'placed',
		// 	'variant' => '2127-4c3fa0',
		// 	'goal'    => '',
		// 	'id'      => 2127,
		// 	'device'  => 'desktop',
		// );
		// $record = Events_Util::record_event( $data, 93, '2024-11-8 00:00:00');
		// $args = Surecart::get_orders_by_variant( $test_id, '2127-4c3fa0', 'month', 'desktop' );
		
		$unique_total_views = [];
		$unique_graph_views = [];
		$unique_total_goals = [];
		$unique_total_sales = [];
		$unique_graph_goals = [];
		$unique_graph_sales = [];
		$combined_views     = 0;
		$combined_graph     = [];
		$data = [];
		if ( $variants && is_array( $variants ) && $goals && is_array( $goals ) ) {
			$data = $this->get_database_data( $test_id, $variants, $goals, $period_name, $period_length, 'any' );
			if ( is_wp_error( $data ) ) {
				error_log( $data->get_error_message() );
				$data = [];
			}
		}
		
		wp_localize_script(
			'kadence-test-analytics',
			'kadenceTestAnalyticsParams',
			[
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'   => wp_create_nonce( 'kadence-test-analytics-ajax-verification' ),
				'exportLink'   => admin_url( 'admin.php?page=kadence-insights-analytics&export_orders_csv=true' ),
				'period'       => $period_name,
				'periodLength' => $period_length,
				'dates'        => Events_Util::get_dates( $period_length ),
				'testId'       => $test_id ? $test_id : 'all',
				'testTitle'    => $test_id ? get_the_title( $test_id ) : __( 'All Tests', 'kadence-insights' ),
				'variants'     => $variants,
				'goals'        => $goals,
				'totalViews'     => ! empty( $data['totalViews'] ) ? $data['totalViews'] : [],
				'totalConvert'   => ! empty( $data['totalConvert'] ) ? $data['totalConvert'] : [],
				'totalSales'     => ! empty( $data['totalSales'] ) ? $data['totalSales'] : [],
				'graphViews'     => ! empty( $data['graphViews'] ) ? $data['graphViews'] : [],
				'graphConvert'   => ! empty( $data['graphConvert'] ) ? $data['graphConvert'] : [],
				'graphSales'     => ! empty( $data['graphSales'] ) ? $data['graphSales'] : [],
				'currency'       => $this->get_currency(),
				'currencySymbol' => $this->get_currency_symbol(),
			]
		);
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'kadence-test-analytics', 'kadence-insights' );
		}
	}
	/**
	 * Get the store currency.
	 */
	public function get_currency_symbol() {
		$store_currency_symbol = '$';
		if ( class_exists( 'WooCommerce' ) && function_exists( 'get_woocommerce_currency_symbol' ) ) {
			$store_currency_symbol = get_woocommerce_currency_symbol();
		} elseif ( class_exists( 'SureCart' ) ) {
			$store_currency = \SureCart::account()->currency ?? 'USD';
			$store_currency_symbol = html_entity_decode( \SureCart\Support\Currency::getCurrencySymbol( $store_currency ) );
		}
		return $store_currency_symbol;
	}
	/**
	 * Get the store currency.
	 */
	public function get_currency() {
		$store_currency = 'USD';
		if ( class_exists( 'WooCommerce' ) && function_exists( 'get_woocommerce_currency' ) ) {
			$store_currency = get_woocommerce_currency();
		} elseif ( class_exists( 'SureCart' ) ) {
			$store_currency = \SureCart::account()->currency ?? 'USD';
		}
		return $store_currency;
	}
	/**
	 * Hide the analytics submenu.
	 *
	 * @param string $submenu_file the submenu file.
	 */
	public function hide_analytics_submenu( $submenu_file ) {

		global $plugin_page;
		// print_r( $plugin_page );
		// Select another submenu item to highlight (optional).
		if ( ! empty( $plugin_page ) && 'kadence-insights-analytics' === $plugin_page ) {
			$submenu_file = 'edit.php?post_type=kadence_ab_test';
		}
		remove_submenu_page( 'kadence-insights', 'kadence-insights-analytics' );
		return $submenu_file;
	}
	/**
	 * Filters the block area post type columns in the admin list table.
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns Columns to display.
	 * @return array Filtered $columns.
	 */
	public function filter_post_type_columns( array $columns ) : array {

		$add = array(
			'analytics' => esc_html__( 'Analytics', 'kadence-insights' ),
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
	 * Renders column content for the block area post type list table.
	 *
	 * @param string $column_name Column name to render.
	 * @param int    $post_id     Post ID.
	 */
	public function render_post_type_column( string $column_name, int $post_id ) {
		if ( 'analytics' !== $column_name ) {
			return;
		}
		if ( 'analytics' === $column_name ) {
			echo '<div class="kadence-insight-analytics"><a style="padding: 7px 12px;display: inline-flex;background: #0073e6;color: white;font-size:13px;gap:10px;border-radius:4px;align-items: center;" href="' . esc_url( admin_url( 'admin.php?page=kadence-insights-analytics&view-item=' . $post_id  ) ) . '"><img style="max-width:14px;height: auto;display:flex;" src="'. esc_url( KADENCE_INSIGHTS_URL . 'assets/chart-icon.png' ) . '">' . esc_html__( 'View Analytics', 'kadence-insights' ) . '</a></div>';
		}
	}
	
}
