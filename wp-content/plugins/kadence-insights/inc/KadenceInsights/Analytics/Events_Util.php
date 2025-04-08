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

use DateTime;
use WP_Error;
use DatePeriod;
use DateInterval;

/**
 * Handles all functionality related to the database events page.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */
class Events_Util {
	const TABLE_NAME = 'kip_ab_events';
	const SALES_TABLE_NAME = 'kip_ab_sales';
	const P_WEEK = 'week';
	const P_30_DAYS = 'month';
	const P_90_DAYS = 'quarter';
	/**
	 * Holds the query cache.
	 */
	private static $_query_cache = [];

	/**
	 * Log form events
	 */
	public function log_ab_event() {
		if ( apply_filters( 'kadence_insights_ab_test_verify_nonce', false ) && ! check_ajax_referer( 'kadence_insights', '_kadence_verify', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
			wp_die();
		}
		if ( ! isset( $_POST['id'] ) || ! isset( $_POST['types'] ) || ! isset( $_POST['variant'] ) || ! isset( $_POST['device'] ) ) {
			wp_send_json_error( 'missing_data' );
			wp_die();
		}
		$types  = json_decode( sanitize_text_field( wp_unslash( $_POST['types'] ) ), true );
		$device = sanitize_text_field( wp_unslash( $_POST['device'] ) );
		if ( empty( $types ) || empty( $device ) ) {
			wp_send_json_error( 'missing_data_types_or_device' );
			wp_die();
		}
		$results = [];
		foreach ( $types as $type ) {
			if ( ! in_array( $type, [ 'converted', 'trigger', 'viewed', 'placed' ], true ) ) {
				wp_send_json_error( 'invalid_type' );
				wp_die();
			}
			if ( ! in_array( $device, [ 'desktop', 'tablet', 'mobile' ], true ) ) {
				wp_send_json_error( 'invalid_device' );
				wp_die();
			}
			if ( ! empty( $_POST['goal'] ) ) {
				$goal = sanitize_text_field( wp_unslash( $_POST['goal'] ) );
			} else {
				$goal = '';
			}
			if ( 'converted' === $type && empty( $goal ) ) {
				wp_send_json_error( 'missing_goal' );
			}
			$data = [
				'type'    => $type,
				'variant' => sanitize_text_field( wp_unslash( $_POST['variant'] ) ),
				'goal'    => $goal,
				'id'      => absint( wp_unslash( $_POST['id'] ) ),
				'device'  => $device,
			];
			do_action( 'kadence_optimize_event', $data );
			$record = $this->record_event( $data );
			if ( $record ) {
				$results[] = $data;
			} else {
				$results[] = 'error saving event';
			}
		}
		wp_send_json( $results );
		wp_die();
	}
	/**
	 * Log form events
	 */
	public function log_sales_event() {
		if ( apply_filters( 'kadence_insights_ab_sales_test_verify_nonce', false ) && ! check_ajax_referer( 'kadence_insights', '_kadence_verify', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
			wp_die();
		}
		if ( ! isset( $_POST['sale'] ) || ! isset( $_POST['type'] ) || ! isset( $_POST['data'] ) || ! isset( $_POST['device'] ) ) {
			wp_send_json_error( 'missing_data' );
			wp_die();
		}
		$type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
		$data = sanitize_text_field( wp_unslash( $_POST['data'] ) );
		$device = sanitize_text_field( wp_unslash( $_POST['device'] ) );
		$sale = sanitize_text_field( wp_unslash( $_POST['sale'] ) );
		if ( empty( $type ) || empty( $device ) || empty( $data ) || empty( $sale ) ) {
			wp_send_json_error( 'missing_data_types_or_device' );
			wp_die();
		}
		if ( ! in_array( $type, [ 'surecart', 'givewp', 'learndash', 'ticket' ], true ) ) {
			wp_send_json_error( 'invalid_type' );
			wp_die();
		}
		if ( ! in_array( $device, [ 'desktop', 'tablet', 'mobile' ], true ) ) {
			wp_send_json_error( 'invalid_device' );
			wp_die();
		}
		$sale_data = [
			'type'    => $type,
			'data'    => $data,
			'sale'    => $sale,
			'device'  => $device,
		];
		do_action( 'kadence_optimize_sales_event', $sale_data );
		$record = $this->record_sales_event( $sale_data );
		if ( ! $record ) {
			wp_send_json_error( 'error_saving_sale_event' );
		}
		wp_send_json( $sale_data );
		wp_die();
	}
	/**
	 * Record an occurrence of an event.
	 *
	 * @param array $data for the event.
	 *
	 * @return bool
	 */
	public static function record_event( $data, $count = 1, $date = false ) {
		global $wpdb;
		$variant  = $data['variant'];
		$post_id  = $data['id'];
		$type     = $data['type'];
		$goal     = $data['goal'];
		$device   = $data['device'];
		$day_time = $date ? $date : wp_date( 'Y-m-d', current_time('timestamp') );
		$r = $wpdb->query( $wpdb->prepare(
			"INSERT INTO {$wpdb->base_prefix}kip_ab_events (`type`,`variant`,`goal`,`post`,`time`) VALUES (%s, %s, %s, %d, %s) ON DUPLICATE KEY UPDATE `count` = `count` + %d,
			`desktop` = `desktop` + IF(%s = 'desktop', %d, 0),
			`tablet` = `tablet` + IF(%s = 'tablet', %d, 0),
			`mobile` = `mobile` + IF(%s = 'mobile', %d, 0)",
			$type, $variant, $goal, $post_id, $day_time, $count, $device, $count, $device, $count, $device, $count
		) );
		return false !== $r;
	}
	/**
	 * Record an occurrence of an event.
	 *
	 * @param array $data for the event.
	 *
	 * @return bool
	 */
	public static function record_sales_event( $sale_data ) {
		global $wpdb;
		$sale     = $sale_data['sale'];
		$type     = $sale_data['type'];
		$data     = $sale_data['data'];
		$device   = $sale_data['device'];
		$day_time = wp_date( 'Y-m-d', current_time('timestamp') );
		$r = $wpdb->query( $wpdb->prepare(
			"INSERT INTO {$wpdb->base_prefix}kip_ab_sales (`type`,`data`,`sale`,`time`,`device`) VALUES (%s, %s, %s, %s, %s)",
			$type, $data, $sale, $day_time, $device
		) );
		return false !== $r;
	}
	/**
	 * Count events.
	 *
	 * @param array|string       $slug_or_slugs
	 * @param array|string|false $period
	 *
	 * @return array|int[]|WP_Error
	 */
	public static function count_events( $slug_or_slugs, $form = false, $period = false ) {

		if ( false === $period ) {
			$period = array(
				'start' => wp_date( 'Y-m-d', current_time('timestamp') - 2 * MONTH_IN_SECONDS ),
				'end'   => wp_date( 'Y-m-d H:i:s', current_time('timestamp') ),
			);
		}
		$slugs = (array) $slug_or_slugs;

		if ( is_wp_error( $range = self::_get_range( $period ) ) ) {
			return $range;
		}

		list( $start, $end ) = $range;

		$prepare = array(
			wp_date( 'Y-m-d H:i:s', $start ),
			wp_date( 'Y-m-d H:i:s', $end ),
		);
		$slug_where = implode( ', ', array_fill( 0, count( $slugs ), '%s' ) );
		$prepare    = array_merge( $prepare, $slugs );
		global $wpdb;
		if ( $form ) {
			$r = $wpdb->get_results( $wpdb->prepare(
				"SELECT sum(`count`) as `c`, `type` as `s` FROM {$wpdb->base_prefix}kbp_form_events WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) AND `post` IN ({$form}) GROUP BY `type` ORDER BY `time` DESC",
				$prepare
			) );
		} else {
			$r = $wpdb->get_results( $wpdb->prepare(
				"SELECT sum(`count`) as `c`, `type` as `s` FROM {$wpdb->base_prefix}kbp_form_events WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) GROUP BY `type` ORDER BY `time` DESC",
				$prepare
			) );
		}
		if ( false === $r ) {
			return new WP_Error( 'kadence-dashboard-query-count-events-db-error', __( 'Error when querying the database for counting events.', 'kadence-insights' ) );
		}

		$events = array();

		foreach ( $r as $row ) {
			$events[ $row->s ] = (int) $row->c;
		}

		foreach ( $slugs as $slug ) {
			if ( ! isset( $events[ $slug ] ) ) {
				$events[ $slug ] = 0;
			}
		}

		return $events;
	}
	/**
	 * Retrieve events.
	 *
	 * @param array|string       $slug_or_slugs
	 * @param string|integer $form the form id.
	 * @param array|string|false $period
	 *
	 * @return array|int[]|WP_Error
	 */
	public static function query_events( $slug_or_slugs, $item = false, $variant_or_variants = false, $period = false, $goals_or_goal = false, $device_type = 'any' ) {
		global $wpdb;
		$table_name = "{$wpdb->prefix}" . self::TABLE_NAME;
		if ( false === $period ) {
			$period = array(
				'start' => wp_date( 'Y-m-d 00:00:00', current_time('timestamp') - 2 * MONTH_IN_SECONDS ),
				'end'   => wp_date( 'Y-m-d H:i:s', current_time('timestamp') ),
			);
		}

		$slugs = (array) $slug_or_slugs;
		$variants      = $variant_or_variants ? (array) $variant_or_variants : false;
		$goals         = $goals_or_goal ? (array) $goals_or_goal : false;
		$slug_where    = implode( ', ', array_fill( 0, count( $slugs ), '%s' ) );
		$variant_where = $variants ? implode( ', ', array_fill( 0, count( $variants ), '%s' ) ) : '';
		$goals_where = $goals ? implode( ', ', array_fill( 0, count( $goals ), '%s' ) ) : '';
		if ( ! empty( $device_type ) && in_array( $device_type, [ 'desktop', 'tablet', 'mobile' ], true ) ) {
			$device = "`{$device_type}` as `c`";
		} else {
			$device = '`count` as `c`';
		}
		if ( is_wp_error( $range = self::_get_range( $period ) ) ) {
			return $range;
		}
		list( $start, $end ) = $range;
		$prepare = array(
			wp_date( 'Y-m-d 00:00:00', $start ),
			wp_date( 'Y-m-d H:i:s', $end ),
		);

		$prepare    = array_merge( $prepare, $slugs );
		if ( $variants ) {
			$prepare = array_merge( $prepare, $variants );
		}
		if ( $goals ) {
			$prepare = array_merge( $prepare, $goals );
		}

		if ( $item && $variants && $goals ) {
			$r = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT `time` as `t`, {$device}, `type` as `s` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) AND `post` IN ({$item}) AND `variant` IN ($variant_where) AND `goal` IN ($goals_where) ORDER BY `time` DESC",
					$prepare
				) 
			);
		} elseif ( $item && $variants) {
			$r = $wpdb->get_results( $wpdb->prepare(
				"SELECT `time` as `t`, {$device}, `type` as `s` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) AND `post` IN ({$item}) AND `variant` IN ($variant_where) ORDER BY `time` DESC",
				$prepare
			) );
		} else if ( $item ) {
			$r = $wpdb->get_results( $wpdb->prepare(
				"SELECT `time` as `t`, {$device}, `type` as `s` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) AND `post` IN ({$item}) ORDER BY `time` DESC",
				$prepare
			) );
		} else {
			$r = $wpdb->get_results( $wpdb->prepare(
				"SELECT `time` as `t`, {$device}, `type` as `s` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) ORDER BY `time` DESC",
				$prepare
			) );
		}
		if ( false === $r ) {
			return new WP_Error( 'kadence-dashboard-query-events-db-error', __( 'Error when querying the database for events.', 'kadence-insights' ) );
		}

		$format    = 'Y-m-d';
		$increment = '+1 day';

		$events = array_combine( $slugs, array_pad( array(), count( $slugs ), array() ) );

		foreach ( $r as $row ) {
			$key = mysql2date( $format, $row->t );
			if ( isset( $events[ $row->s ][ $key ] ) ) {
				$events[ $row->s ][ $key ] += $row->c; // Handle unconsolidated rows.
			} else {
				$events[ $row->s ][ $key ] = (int) $row->c;
			}
		}
		$retval = array();
		foreach ( $events as $slug => $slug_events ) {
			$slug_events = self::fill_gaps( $slug_events, $start, $end, $format, $increment );
			foreach ( $slug_events as $time => $count ) {
				$retval[ $slug ][] = array(
					'time'  => $time,
					'count' => $count,
				);
			}
		}
		return $retval;
	}

	/**
	 * Retrieve events.
	 *
	 * @param array|string       $slug_or_slugs
	 * @param string|integer $form the form id.
	 * @param array|string|false $period
	 *
	 * @return array|int[]|WP_Error
	 */
	public static function get_sale_events( $type, $item = false, $variant = false, $period = false, $device_type = 'any' ) {
		global $wpdb;
		$table_name = "{$wpdb->prefix}" . self::SALES_TABLE_NAME;
		if ( false === $period ) {
			$period = array(
				'start' => wp_date( 'Y-m-d 00:00:00', current_time('timestamp') - 2 * MONTH_IN_SECONDS ),
				'end'   => wp_date( 'Y-m-d H:i:s', current_time('timestamp') ),
			);
		}
		$data_search   = $item && $variant ? '"' . $item . '":"' . $variant . '"' : false;
		if ( $data_search ) {
			$data_search = array( '%' . $wpdb->esc_like($data_search) . '%' );
		}
		$device        = ! empty( $device_type ) && in_array( $device_type, [ 'desktop', 'tablet', 'mobile' ], true ) ? $device_type : false;
		if ( is_wp_error( $range = self::_get_range( $period ) ) ) {
			return $range;
		}

		list( $start, $end ) = $range;
		$prepare = array(
			wp_date( 'Y-m-d 00:00:00', $start ),
			wp_date( 'Y-m-d H:i:s', $end ),
		);
		if ( $type ) {
			$prepare = array_merge( $prepare, [$type] );
		}
		if ( $device ) {
			$prepare = array_merge( $prepare, [$device] );
		}
		if ( $data_search ) {
			$prepare = array_merge( $prepare, $data_search );
		}
		$r = false;
		if ( $type && $device && $data_search ) {
			$r = $wpdb->get_results( $wpdb->prepare(
				"SELECT `sale` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` = %s AND `device` = %s AND `data` LIKE %s ORDER BY `time` DESC",
				$prepare
			) );
		} elseif ( $type && $data_search ) {
			$r = $wpdb->get_results( $wpdb->prepare(
				"SELECT `sale` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` = %s AND `data` LIKE %s ORDER BY `time` DESC",
				$prepare
			) );
		} elseif ( $type ) {
			$r = $wpdb->get_results( $wpdb->prepare(
				"SELECT `sale` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` = %s ORDER BY `time` DESC",
				$prepare
			) );
		}
		if ( false === $r ) {
			return new WP_Error( 'kadence-dashboard-query-events-db-error', __( 'Error when querying the database for events.', 'kadence-insights' ) );
		}
		$response = [];
		foreach ( $r as $row ) {
			$response[] = $row->sale;
		}

		return $response;
	}
	/**
	 * Retrieve dates.
	 *
	 * @param integer $days the amount in that period.
	 *
	 * @return array
	 */
	public static function get_dates( $days ) {
		$dates        = [];
		$current_date = new DateTime(); // Start from today.

		for ( $i = 0; $i < $days; $i++ ) {
			// Format the date using wp_date with full month name and day number
			$dates[] = wp_date( 'F j', $current_date->getTimestamp() );

			// Move back 1 day
			$current_date->modify( '-1 day' );
		}

		return $dates;
	}
	/**
	 * Retrieve the total number of events.
	 *
	 * @param array|string         $slug_or_slugs
	 * @param string|integer|false $item the individual id.
	 * @param array|string|false   $period
	 *
	 * @return int|WP_Error
	 */
	public static function total_events( $slug_or_slugs, $item = false, $variant_or_variants = false, $period = false, $goals_or_goal = false, $device_type = 'any' ) {
		global $wpdb;
		$table_name = "{$wpdb->prefix}" . self::TABLE_NAME;
		if ( false === $period ) {
			$period = array(
				'start' => wp_date( 'Y-m-d', current_time('timestamp') - 2 * MONTH_IN_SECONDS ),
				'end'   => wp_date( 'Y-m-d', current_time('timestamp') ),
			);
		}
		$slugs         = (array) $slug_or_slugs;
		$variants      = $variant_or_variants ? (array) $variant_or_variants : false;
		$goals         = $goals_or_goal ? (array) $goals_or_goal : false;
		$slug_where    = implode( ', ', array_fill( 0, count( $slugs ), '%s' ) );
		$variant_where = $variants ? implode( ', ', array_fill( 0, count( $variants ), '%s' ) ) : '';
		$goals_where   = $goals ? implode( ', ', array_fill( 0, count( $goals ), '%s' ) ) : '';
		if ( ! empty( $device_type ) && in_array( $device_type, [ 'desktop', 'tablet', 'mobile' ], true ) ) {
			$count_column = $device_type;
		} else {
			$count_column = 'count';
		}
		$range = self::_get_range( $period );
		if ( is_wp_error( $range ) ) {
			return $range;
		}

		list( $start, $end ) = $range;

		$prepare = array(
			wp_date( 'Y-m-d H:i:s', $start ),
			wp_date( 'Y-m-d H:i:s', $end ),
		);
		$prepare      = array_merge( $prepare, $slugs );
		if ( $item ) {
			$prepare = array_merge( $prepare, array( $item ) );
		}
		if ( $variants ) {
			$prepare = array_merge( $prepare, $variants );
		}
		if ( $goals ) {
			$prepare = array_merge( $prepare, $goals );
		}
		if ( $item && $variants && $goals ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT sum(`$count_column`) as `c` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) AND `post` IN (%s) AND `variant` IN ($variant_where) AND `goal` IN ($goals_where)",
				$prepare
			) );
		} elseif ( $item && $variants ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT sum(`$count_column`) as `c` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) AND `post` IN (%s) AND `variant` IN ($variant_where)",
				$prepare
			) );
		} elseif ( $item ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT sum(`$count_column`) as `c` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where}) AND `post` IN (%s)",
				$prepare
			) );
		} else {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT sum(`count`) as `c` FROM `$table_name` WHERE `time` BETWEEN %s AND %s AND `type` IN ({$slug_where})",
				$prepare
			) );
		}
		if ( false === $count ) {
			return new WP_Error( 'kadence-dashboard-total-events-db-error', __( 'Error when querying the database for total events.', 'kadence-insights' ) );
		}

		return (int) $count;
	}
	/**
	 * Fill the gaps in a range of days
	 *
	 * @param array  $events
	 * @param int    $start
	 * @param int    $end
	 * @param string $format
	 * @param string $increment
	 *
	 * @return array
	 */
	private static function fill_gaps( $events, $start, $end, $format = 'Y-m-d', $increment = '+1 day' ) {
		$now   = wp_date( $format, $start );
		$end_d = wp_date( $format, $end );
		$period = new DatePeriod(
			new DateTime( $now ),
			new DateInterval( 'P1D' ),
			new DateTime( wp_date( $format, strtotime( $increment, strtotime( $end_d ) ) ) )
		);
		foreach ( $period as $date ) {
			$formatted_date = $date->format( $format );
			if ( ! isset( $events[ $formatted_date ] ) ) {
				$events[ $formatted_date ] = 0;
			}
		}
		ksort( $events );
		// Add the end date;
		if ( ! isset( $events[ $end_d ] ) ) {
			$events[ $end_d ] = 0;
		}
		return $events;
	}

	/**
	 * Get the date range for the report query.
	 *
	 * @param string|array $period
	 *
	 * @return int[]|WP_Error
	 */
	public static function _get_range( $period ) {
		if ( is_array( $period ) ) {
			if ( ! isset( $period['start'], $period['end'] ) ) {
				return new WP_Error( 'kadence-insights-analytics-invalid-period', __( 'Invalid Period', 'kadence-insights' ) );
			}

			if ( false === ( $s = strtotime( $period['start'] ) ) || false === ( $e = strtotime( $period['end'] ) ) ) {
				return new WP_Error( 'kadence-insights-analytics-invalid-period', __( 'Invalid Period', 'kadence-insights' ) );
			}

			return array( $s, $e );
		}

		switch ( $period ) {
			case self::P_WEEK:
				return array(
					strtotime( '-6 days', current_time('timestamp') ),
					current_time('timestamp'),
				);
			case self::P_30_DAYS: 
				return array(
					strtotime( '-29 days', current_time('timestamp') ),
					current_time('timestamp'),
				);
			case self::P_90_DAYS:
				return array(
					strtotime( '-89 days', current_time('timestamp') ),
					current_time('timestamp'),
				);
		}

		return new WP_Error( 'kadence-insights-analytics-invalid-period', __( 'Invalid Period', 'kadence-insights' ) );
	}

	/**
	 * Flushes the internal query cache.
	 */
	public static function flush_cache() {
		self::$_query_cache = [];
	}
}
