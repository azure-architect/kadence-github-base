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

use KadenceWP\Insights\Container;
use KadenceWP\Insights\StellarWP\DB\DB;
use KadenceWP\Insights\StellarWP\Schema\Config as SchemaConfig;
use KadenceWP\Insights\StellarWP\Schema\Register;
use KadenceWP\Insights\StellarWP\DB\Database\Exceptions\DatabaseQueryException;

/**
 * Handles all functionality related to the database events page.
 *
 * @since 0.1.1
 *
 * @package KadenceWP\Insights
 */
class Database_Events {

	/**
	 * Initializes DB for AB events.
	 *
	 * @since 0.1.1
	 *
	 * @action plugins_loaded
	 *
	 * @return void
	 */
	public function custom_tables_init(): void {
		DB::init();

		$container = new Container();
		SchemaConfig::set_container( $container );
		SchemaConfig::set_db( DB::class );
		try {
			Register::table( AB_Events::class );
			Register::table( Sales_Events::class );
		} catch ( DatabaseQueryException $e ) {
			// Do nothing.
		}
	}
}
