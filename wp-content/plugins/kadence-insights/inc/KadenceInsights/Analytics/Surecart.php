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
class Surecart {
	/**
	 * Register our hooks on init.
	 *
	 * @return void
	 */
	public function on_init() {
		add_action(
			'wp_enqueue_scripts',
			function() {
				$this->enqueue_scripts_and_styles();
			}
		);
		if ( ! is_admin() ) {
			add_filter( 'render_block', array( $this, 'conditionally_load_surecart_js' ), 5, 3 );
		}
	}
	/**
	 * Conditionally load the Surecart JS on the checkout form block.
	 */
	public function conditionally_load_surecart_js( $block_content, $block, $wp_block ) {
		if ( ! empty( $block['blockName'] ) && 'surecart/checkout-form' === $block['blockName'] ) {
			wp_enqueue_script( 'kadence-surecart-insights' );
		}
		return $block_content;
	}
	/**
	 * Scripts & styles for custom source tracking and cart tracking.
	 */
	private function enqueue_scripts_and_styles() {
		wp_register_script( 'kadence-surecart-insights', KADENCE_INSIGHTS_URL . 'assets/js/kadence-surecart-insights.min.js', [], KADENCE_INSIGHTS_VERSION, true );
		wp_localize_script(
			'kadence-surecart-insights',
			'kadenceABSaleConfig',
			[
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'          => wp_create_nonce( 'kadence_insights' ),
			]
		);
	}
	/**
	 * Build the query string for the API request.
	 *
	 * @param array $args Query arguments.
	 * @return string Query string.
	 */
	public static function build_query_params( $args ) {
		$param = '?';
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $val ) {
					if ( $param === '?' ) {
						$param .= $key . '[]=' . urlencode( $val );
					} else {
						$param .= '&' . $key . '[]=' . urlencode( $val );
					}
				}
			} else {
				if ( $param === '?' ) {
					$param .= $key . '=' . urlencode( $value );
				} else {
					$param .= '&' . $key . '=' . urlencode( $value );
				}
			}
		}
		return $param;
	}
	/**
	 * Get remote order data with pagination handling.
	 *
	 * @param array  $args Query arguments.
	 * @param string $private_token Private API token.
	 * @return array|WP_Error All order data or error.
	 */
	public static function get_remote_order_data( $args, $private_token ) {
		$query_params = self::build_query_params( $args );
		$all_data     = [];
    	$current_page = 1;
		$base_url	  = 'https://api.surecart.com/v1/orders';
		do {
			$url = $base_url . $query_params . '&page=' . $current_page;
			$response = wp_safe_remote_get(
				$url,
				array(
					'headers' => array(
						'accept' => 'application/json',
						'authorization' => 'Bearer ' . $private_token,
					),
					'timeout' => 20,
				)
			);
	
			if ( is_wp_error( $response ) ) {
				error_log( $response->get_error_message() );
				return $response;
			}
	
			$contents = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $contents ) ) {
				error_log( $contents->get_error_message() );
				return $contents;
			}
	
			$data = json_decode( $contents, true );
	
			if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
				$all_data = array_merge( $all_data, $data['data'] );
			}
	
			// Check for pagination details
			$pagination = isset( $data['pagination'] ) ? $data['pagination'] : null;
			if ( $pagination && isset( $pagination['page'], $pagination['count'], $pagination['limit'] ) ) {
				$current_page++;
				$total_pages = ceil( $pagination['count'] / $pagination['limit'] );
			} else {
				break; // Exit loop if no pagination is available
			}
		} while ( $current_page <= $total_pages );
	
		return $all_data;
	}
	/**
	 * Convert an integer to a float with two decimal places.
	 *
	 * @param int $number The input number to convert.
	 * @return string The formatted number as a string with two decimal places.
	 */
	public static function convert_to_decimal( $number ) {
		// Divide by 100 to convert to decimal
		$decimal = $number / 100;

		// Format the number to two decimal places
		return number_format( $decimal, 2, '.', '' );
	}
	/**
	 * Get the orders that container the given meta key and search ID.
	 *
	 * @param string $test_id The test ID.
	 * @param string $variant The variant.
	 * @param string $time_period The time period.
	 * @param string $device_type The device type.
	 *
	 * @return array
	 */
	public static function get_orders_by_variant( $test_id, $variant, $time_period, $device_type = 'any' ) {
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
		$order_ids = Events_Util::get_sale_events( 'surecart', $test_id, $variant, $time_period, $device_type );
		$private_token = ( class_exists( '\SureCart\Models\ApiToken' ) ? \SureCart\Models\ApiToken::get() : '' );
		if ( ! empty( $order_ids ) && ! empty( $private_token ) ) {
			$args = [
				'ids' => $order_ids,
				'return_status' => [ 'not_returned' ],
				'status' => [ 'paid', 'processing' ],
				'expand' => [ 'checkout' ],
				'limit' => '100',
			];
			$orders = self::get_remote_order_data( $args, $private_token );
			if ( is_wp_error( $orders ) ) {
				return $orders;
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
		//error_log( print_r( $order_data, true ) );
		if ( ! empty( $orders ) && is_array( $orders ) ) {
			foreach ( $orders as $order ) {
				$date = wp_date( 'Y-m-d', $order['created_at'] );
				// Update daily counts and sales for the order date
				$daily_counts[ $date ]++;
				$daily_sales[ $date ] += ( !empty( $order['checkout']['total_amount'] ) ? self::convert_to_decimal( $order['checkout']['total_amount'] ) : 0 );
		
				// Overall totals.
				$total_count++;
				$total_sales += ( ! empty( $order['checkout']['total_amount'] ) ? self::convert_to_decimal( $order['checkout']['total_amount'] ) : 0 );
			}
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
}
