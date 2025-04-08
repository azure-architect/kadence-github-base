<?php

namespace KadenceWP\Insights\Analytics;

use KadenceWP\Insights\StellarWP\Schema\Tables\Contracts\Table;

/**
 * The Optimize_Events table.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */
class Sales_Events extends Table {
	/**
	 * {@inheritdoc}
	 */
	const SCHEMA_VERSION = '1.0.1';

	/**
	 * {@inheritdoc}
	 */
	protected static $base_table_name = 'kip_ab_sales';

	/**
	 * {@inheritdoc}
	 */
	protected static $group = 'kip';

	/**
	 * {@inheritdoc}
	 */
	protected static $schema_slug = 'kip-ab-sales';

	/**
	 * {@inheritdoc}
	 */
	protected static $uid_column = 'id';

	/**
	 * {@inheritdoc}
	 */
	protected function get_definition() {
		global $wpdb;
		$table_name      = self::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		return "
			CREATE TABLE `{$table_name}` (
				`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`type` varchar(128) NOT NULL DEFAULT '',
				`data` varchar(512) NOT NULL DEFAULT '',
				`time` datetime NOT NULL,
				`sale` varchar(128) NOT NULL DEFAULT '',
				`device` varchar(128) NOT NULL DEFAULT '',
				PRIMARY KEY (`id`),
				UNIQUE KEY `type__time__sale__device` (type,time,sale,device)
			) {$charset_collate};
		";
	}
}
