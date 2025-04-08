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

use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\Utilities\OrderUtil;
use WC_Order;
use WP_CONSENT_API;
use WC_Order_Query;
use DatePeriod;
use DateTime;
use DateInterval;
use function wp_has_consent;


/**
 * Handles all functionality related to the database events page.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */
class Woocommerce {

	/**
	 * ExtendSchema instance.
	 *
	 * @var ExtendSchema
	 */
	private $extend_schema;
	/**
	 * Identifier of the consent category used for order attribution.
	 *
	 * @var string
	 */
	public static $consent_category = 'marketing';

	/**
	 * Field Names.
	 *
	 * @var array
	 * */
	private $field_names = [ 'kad_ab_variants', 'kad_ab_device' ];
	/**
	 *  Whether the `stamp_checkout_html_element` method has been called.
	 *
	 * @var bool
	 */
	private static $is_stamp_checkout_html_called = false;
	/**
	 * Check if WP Cookie Consent API is active
	 *
	 * @return bool
	 */
	protected function is_wp_consent_api_active() {
		return class_exists( WP_CONSENT_API::class );
	}
	/**
	 * Register our hooks on init.
	 *
	 * @return void
	 */
	public function on_init() {
		$this->extend_schema = StoreApi::container()->get( ExtendSchema::class );
		$this->extend_api();
		$this->register_consent();
		add_action(
			'wp_enqueue_scripts',
			function() {
				$this->enqueue_scripts_and_styles();
			}
		);
		/**
		 * Filter set of actions used to stamp the unique checkout order attribution HTML container element.
		 *
		 * @since 9.0.0
		 *
		 * @param array $stamp_checkout_html_actions The set of actions used to stamp the unique checkout order attribution HTML container element.
		 */
		$stamp_checkout_html_actions = array(
			'woocommerce_checkout_billing',
			'woocommerce_after_checkout_billing_form',
			'woocommerce_checkout_shipping',
			'woocommerce_after_order_notes',
			'woocommerce_checkout_after_customer_details',
		);
		foreach ( $stamp_checkout_html_actions as $action ) {
			add_action( $action, [ $this, 'stamp_checkout_html_element_once' ] );
		}

		add_action( 'woocommerce_register_form', [ $this, 'stamp_html_element' ] );

	}
	/**
	 * Register the consent category for order attribution.
	 *
	 * @return void
	 */
	private function register_consent() {
		// Include integration to WP Consent Level API if available.
		if ( ! $this->is_wp_consent_api_active() ) {
			return;
		}

		$plugin = plugin_basename( KADENCE_INSIGHTS_FILE );
		add_filter( "wp_consent_api_registered_{$plugin}", '__return_true' );

		/**
		 * Modify the "allowTracking" flag consent if the user has consented to marketing.
		 *
		 * Wp-consent-api will initialize the modules on "init" with priority 9,
		 * So this code needs to be run after that.
		 */
		add_filter(
			'kadence_insights_do_not_track_user',
			function() {
				return function_exists( 'wp_has_consent' ) && wp_has_consent( self::$consent_category );
			}
		);
	}
	/**
	 * Handles the `<wc-order-insight-inputs>` element for checkout forms, ensuring that the field is only output once.
	 *
	 * @since 9.0.0
	 *
	 * @return void
	 */
	public function stamp_checkout_html_element_once() {
		if ( self::$is_stamp_checkout_html_called ) {
			return;
		}
		$this->stamp_html_element();
		self::$is_stamp_checkout_html_called = true;
	}

	/**
	 * Output `<wc-order-insight-inputs>` element that contributes the order attribution values to the enclosing form.
	 * Used customer register forms, and for checkout forms through `stamp_checkout_html_element()`.
	 *
	 * @return void
	 */
	public function stamp_html_element() {
		printf( '<wc-order-insight-inputs></wc-order-insight-inputs>' );
	}
	/**
	 * Scripts & styles for custom source tracking and cart tracking.
	 */
	private function enqueue_scripts_and_styles() {
		if ( ! is_checkout() ) {
			return;
		}
		wp_enqueue_script( 'kadence-checkout-insights', KADENCE_INSIGHTS_URL . 'assets/js/kadence-checkout-insights.min.js', [], KADENCE_INSIGHTS_VERSION, true );
	}

	/**
	 * Store the cookie info if present.
	 *
	 * @param int $order_id The order ID.
	 * @param array $posted The posted data.
	 */
	public function save_classic_checkout( $order ) {
		// Nonce check is handled by WooCommerce before woocommerce_checkout_order_created hook.
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $_POST[ 'kad_ab_variants' ] ) ) {
			return;
		}
		$params = $this->get_insight_field_values( $_POST );
		$params['kad_ab_variants'] = sanitize_text_field( wp_unslash( $_POST[ 'kad_ab_variants' ] ) );
		$params['kad_ab_device']   = sanitize_text_field( wp_unslash( $_POST[ 'kad_ab_device' ] ) );
		/**
		 * Run an action to save order attribution data.
		 *
		 * @since 8.5.0
		 *
		 * @param WC_Order $order The order object.
		 * @param array    $params Unprefixed order attribution data.
		 */
		do_action( 'kadence_order_save_insights_data', $order, $params );

	}
	/**
	 * Map posted, prefixed values to field values.
	 * Used for the classic forms.
	 *
	 * @param array $raw_values The raw values from the POST form.
	 *
	 * @return array
	 */
	private function get_insight_field_values( array $raw_values = array() ): array {
		$values = array();

		// Look through each field in POST data.
		foreach ( $this->field_names as $field_name ) {
			$values[ $field_name ] = $raw_values[ $field_name ] ?? '';
		}

		return $values;
	}
	/**
	 * Map submitted values to meta values.
	 *
	 * @param array $raw_values The raw (unprefixed) values from the submitted data.
	 *
	 * @return array
	 */
	private function get_insight_values( array $raw_values = array() ): array {
		$values = [];

		// Look through each field in given data.
		foreach ( $this->field_names as $field_name ) {
			$value = sanitize_text_field( wp_unslash( $raw_values[ $field_name ] ) );
			if ( empty( $value ) ) {
				continue;
			}
			if ( strlen( $value ) >= 500 ) {
				// If it's longer then 500 characters something is wrong and we shouldn't save it.
				continue;
			}

			$values[ $field_name ] = $value;
		}

		return $values;
	}
	/**
	 * Store the cookie info if present.
	 *
	 * @param int $order_id The order ID.
	 * @param array $posted The posted data.
	 */
	public function save_meta_data( $order, $data ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}
		$insight_data = $this->get_insight_values( $data );
		if ( empty( $insight_data ) ) {
			return;
		}
		$this->set_order_insight_data( $insight_data, $order );
	}
	/**
	 * Save source data for an Order object.
	 *
	 * @param array    $insight_data The source data.
	 * @param WC_Order $order       The order object.
	 *
	 * @return void
	 */
	private function set_order_insight_data( array $insight_data, WC_Order $order ) {
		// If all the values are empty, bail.
		if ( empty( array_filter( $insight_data ) ) ) {
			return;
		}
		foreach ( $insight_data as $key => $value ) {
			$order->update_meta_data( '_' . $key, $value );
		}

		$order->save_meta_data();
	}

	/**
	 * Extend the Store API.
	 *
	 * @return void
	 */
	private function extend_api() {
		$this->extend_schema->register_endpoint_data(
			[
				'endpoint'        => CheckoutSchema::IDENTIFIER,
				'namespace'       => 'kadence/insights',
				'schema_callback' => $this->get_schema_callback(),
			]
		);
		// Update order based on extended data.
		add_action(
			'woocommerce_store_api_checkout_update_order_from_request',
			function ( $order, $request ) {
				$extensions = $request->get_param( 'extensions' );
				$params     = $extensions['kadence/insights'] ?? [];

				if ( empty( $params ) ) {
					return;
				}

				/**
				 * Run an action to save order attribution data.
				 *
				 * @since 8.5.0
				 *
				 * @param WC_Order $order  The order object.
				 * @param array    $params Unprefixed order attribution data.
				 */
				$this->save_meta_data( $order, $params );
			},
			10,
			2
		);
	}
	/**
	 * Get the schema callback.
	 *
	 * @return callable
	 */
	private function get_schema_callback() {
		return function() {
			$schema      = array();

			$validate_callback = function( $value ) {
				if ( ! is_string( $value ) && null !== $value ) {
					return new WP_Error(
						'api-error',
						sprintf(
							/* translators: %s is the property type */
							esc_html__( 'Value of type %s was posted to the kadence insights callback', 'kadence-insights' ),
							gettype( $value )
						)
					);
				}

				return true;
			};

			$sanitize_callback = function( $value ) {
				return sanitize_text_field( $value );
			};

			$schema['kad_ab_variants'] = [
				'description' => __( 'Field for kadence ab variants', 'kadence-insights' ),
				'type'        => [ 'string', 'null' ],
				'context'     => [],
				'arg_options' => [
					'validate_callback' => $validate_callback,
					'sanitize_callback' => $sanitize_callback,
				],
			];
			$schema['kad_ab_device'] = [
				'description' => __( 'Field for kadence ab device', 'kadence-insights' ),
				'type'        => [ 'string', 'null' ],
				'context'     => [],
				'arg_options' => [
					'validate_callback' => $validate_callback,
					'sanitize_callback' => $sanitize_callback,
				],
			];

			return $schema;
		};
	}

	/**
	 * Get the orders that container the given meta key and search ID.
	 *
	 * @param string $meta_key The meta key to search for.
	 * @param string $search_id The search ID.
	 *
	 * @return array
	 */
	public static function get_orders_by_meta_id( $meta_key, $test_id, $variant, $time_period, $device_type = 'any' ) {
		// Set the date range based on the specified time period
		$start_date = '';
		$end_date = wp_date( 'Y-m-d' ); // End date is today.
	
		switch ( $time_period ) {
			case 'week':
				$start_date = wp_date( 'Y-m-d', strtotime( '-1 week' ) );
				break;
			case 'month':
				$start_date = wp_date( 'Y-m-d', strtotime( '-1 month' ) );
				break;
			case 'quarter':
				$start_date = wp_date( 'Y-m-d', strtotime( '-3 months' ) );
				break;
			default:
				$start_date = wp_date( 'Y-m-d', strtotime( '-1 week' ) );
				break;
		}
	
		// Generate an array of all dates in the period, initializing counts and sales to 0.
		$period = new DatePeriod(
			new DateTime( $start_date ),
			new DateInterval( 'P1D' ),
			new DateTime( wp_date( 'Y-m-d', strtotime( '+1 day', strtotime( $end_date ) ) ) )
		);
		$daily_counts = [];
		$daily_sales  = [];
	
		foreach ( $period as $date ) {
			$formatted_date                  = $date->format( 'Y-m-d' );
			$daily_counts[ $formatted_date ] = 0;
			$daily_sales[ $formatted_date ]  = 0;
		}
		$orders = [];
		if ( class_exists( 'WooCommerce' ) ) {
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				// Create a new WC_Order_Query.
				$query = new WC_Order_Query(
					[
						'limit'      => -1,
						'meta_query' => [
							[
								'key'     => $meta_key,
								'value'   => '"' . $test_id . '":"' . $variant . '"',
								'compare' => 'LIKE',
							],
						],
						'date_query' => [
							'after'  => $start_date,
							'before' => wp_date( 'Y-m-d', strtotime( '1 day' ) ),
						],
						'status' => [ 'wc-processing', 'wc-completed' ],
					]
				);
			
				// Get the orders.
				$orders = $query->get_orders();
			} else {
				$orders = wc_get_orders(
					[
						'limit'      => -1,
						'meta_key'   => $meta_key,
						'meta_value' => '"' . $test_id . '":"' . $variant . '"',
						'meta_compare'  => 'LIKE',
						'date_query' => [
							'after'  => $start_date,
							'before' => wp_date( 'Y-m-d', strtotime( '1 day' ) ),
						],
						'status' => [ 'wc-processing', 'wc-completed' ],
					]
				);
			}
		}
		if ( empty( $orders ) ) {
			$daily_counts_structured = [];
			foreach ( $daily_counts as $date => $count ) {
				$daily_counts_structured[] = [
					'time'  => $date,
					'count' => $count,
				];
			}
			$daily_counts_structured = [ 'converted' => $daily_counts_structured ];
			$daily_sales_structured  = [];
			foreach ( $daily_sales as $date => $count ) {
				$daily_sales_structured[] = [
					'time'  => $date,
					'count' => $count,
				];
			}
			return [
				'total_count'  => 0,
				'total_sales'  => 0,
				'daily_counts' => $daily_counts_structured,
				'daily_sales'  => $daily_sales_structured,
			];
		}
		// Initialize counters for overall totals.
		$total_count = 0;
		$total_sales = 0;
		foreach ( $orders as $order ) {
			if ( ! $order instanceof WC_Order ) {
				continue;
			}
			if ( ! $order->get_date_paid() ) {
				continue;
			}
			if ( ! empty( $device_type ) && in_array( $device_type, [ 'desktop', 'tablet', 'mobile' ], true ) ) {
				$device = $order->get_meta( '_kad_ab_device' );
				if ( ! empty ( $device ) ) {
					if ( $device !== $device_type ) {
						continue;
					}
				} else {
					// Fallback to check wc order attribution device type.
					$device = $order->get_meta( '_wc_order_attribution_device_type' );
					$device = ! empty( $device ) ? strtolower( $device ) : '';
					if ( ! empty ( $device ) ) {
						if ( $device !== $device_type ) {
							continue;
						}
					}
				}
			}
			$date = $order->get_date_paid()->date( 'Y-m-d' );
	
			// Update daily counts and sales for the order date
			$daily_counts[ $date ]++;
			$daily_sales[ $date ] += $order->get_total();
	
			// Overall totals.
			$total_count++;
			$total_sales += $order->get_total();
		}
		$daily_counts_structured = [];
		foreach ( $daily_counts as $date => $count ) {
			$daily_counts_structured[] = [
				'time'  => $date,
				'count' => $count,
			];
		}
		$daily_counts_structured = [ 'converted' => $daily_counts_structured ];
		$daily_sales_structured  = [];
		foreach ( $daily_sales as $date => $count ) {
			$daily_sales_structured[] = [
				'time'  => $date,
				'count' => $count,
			];
		}
		$daily_sales_structured  = [ 'converted' => $daily_sales_structured ];
	
		// Return the summary data.
		return [
			'total_count'  => $total_count,
			'total_sales'  => $total_sales,
			'daily_counts' => $daily_counts_structured,
			'daily_sales'  => $daily_sales_structured,
		];
	}
	/**
	 * Export orders to CSV with items by URL IDs.
	 *
	 * @return void
	 */
	public function export_orders_to_csv() {
		// Only run this code if 'export_orders_csv' is set in the URL
		if (isset($_GET['export_orders_csv']) && current_user_can('manage_woocommerce')) {
	
			// Check if 'order_ids' parameter is provided in the URL
			if (empty($_GET['view-item']) || empty($_GET['variant']) || empty($_GET['time_period'])) {
				wp_die(__('Missing Report Indication Data.', 'kadence-insights'));
			}
			$test_id     = sanitize_text_field($_GET['view-item']);
			$variant     = sanitize_text_field($_GET['variant']);
			$time_period = sanitize_text_field($_GET['time_period']);

			$device_type = ! empty( $_GET['device'] ) ? sanitize_text_field($_GET['device']) : 'any';
			// Set the date range based on the specified time period
			$start_date = '';
			$end_date = wp_date( 'Y-m-d' ); // End date is today.
			
			switch ( $time_period ) {
				case 'week':
					$start_date = wp_date( 'Y-m-d', strtotime( '-1 week' ) );
					break;
				case 'month':
					$start_date = wp_date( 'Y-m-d', strtotime( '-1 month' ) );
					break;
				case 'quarter':
					$start_date = wp_date( 'Y-m-d', strtotime( '-3 months' ) );
					break;
				default:
					$start_date = wp_date( 'Y-m-d', strtotime( '-1 week' ) );
					break;
			}

			switch ( $device_type ) {
				case 'desktop':
					$device_type = 'desktop';
					break;
				case 'tablet':
					$device_type = 'tablet';
					break;
				case 'mobile':
					$device_type = 'mobile';
					break;
				default:
					$device_type = 'any';
					break;
			}
		
			// Generate an array of all dates in the period, initializing counts and sales to 0.
			$period = new DatePeriod(
				new DateTime( $start_date ),
				new DateInterval( 'P1D' ),
				new DateTime( wp_date( 'Y-m-d', strtotime( '+1 day', strtotime( $end_date ) ) ) )
			);
		
			$orders = [];
			if ( class_exists( 'WooCommerce' ) ) {
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					// Create a new WC_Order_Query.
					$query = new WC_Order_Query(
						[
							'limit'      => -1,
							'meta_query' => [
								[
									'key'     => $meta_key,
									'value'   => '"' . $test_id . '":"' . $variant . '"',
									'compare' => 'LIKE',
								],
							],
							'date_query' => [
								'after'  => $start_date,
								'before' => wp_date( 'Y-m-d', strtotime( '1 day' ) ),
							],
							'status' => [ 'wc-processing', 'wc-completed' ],
						]
					);
				
					// Get the orders.
					$orders = $query->get_orders();
				} else {
					$orders = wc_get_orders(
						[
							'limit'      => -1,
							'meta_key'   => $meta_key,
							'meta_value' => '"' . $test_id . '":"' . $variant . '"',
							'meta_compare'  => 'LIKE',
							'date_query' => [
								'after'  => $start_date,
								'before' => wp_date( 'Y-m-d', strtotime( '1 day' ) ),
							],
							'status' => [ 'wc-processing', 'wc-completed' ],
						]
					);
				}
			}
	
			// Define the CSV file headers
			$headers = array('Order ID', 'Date', 'Total', 'Customer Name', 'Customer Email', 'Status', 'Product Name', 'Quantity', 'Product Total');
	
			// Set the filename
			$filename = 'orders_from_ab_test_variation_' . date('Y-m-d') . '.csv';
	
			// Open output stream as PHP output
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment;filename=' . $filename);
			$output = fopen('php://output', 'w');
	
			// Write the CSV headers
			fputcsv($output, $headers);
	
			// Loop through each order ID and get the order data
			foreach ( $orders as $order ) {
				if ( ! $order instanceof WC_Order ) {
					continue;
				}
				if ( ! $order->get_date_paid() ) {
					continue;
				}
				if ( ! empty( $device_type ) && in_array( $device_type, [ 'desktop', 'tablet', 'mobile' ], true ) ) {
					$device = $order->get_meta( '_kad_ab_device' );
					if ( ! empty ( $device ) ) {
						if ( $device !== $device_type ) {
							continue;
						}
					} else {
						// Fallback to check wc order attribution device type.
						$device = $order->get_meta( '_wc_order_attribution_device_type' );
						$device = ! empty( $device ) ? strtolower( $device ) : '';
						if ( ! empty ( $device ) ) {
							if ( $device !== $device_type ) {
								continue;
							}
						}
					}
				}
				if ($order) {
					// Common order data for each row
					$order_data = array(
						$order->get_id(),
						$order->get_date_created()->date('Y-m-d H:i:s'),
						$order->get_total(),
						$order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
						$order->get_billing_email(),
						$order->get_status()
					);
	
					// Loop through each item in the order
					foreach ($order->get_items() as $item) {
						$product_name = $item->get_name();
						$quantity = $item->get_quantity();
						$line_total = $item->get_total();
	
						// Combine order data with item-specific data
						$row = array_merge($order_data, array($product_name, $quantity, $line_total));
	
						// Write the order data with item details as a row in the CSV
						fputcsv($output, $row);
					}
				}
			}
	
			// Close the output stream
			fclose($output);
			exit();
		}
	}
}
