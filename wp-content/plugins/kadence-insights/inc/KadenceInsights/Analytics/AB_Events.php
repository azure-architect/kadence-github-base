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
class AB_Events extends Table {
	/**
	 * {@inheritdoc}
	 */
	const SCHEMA_VERSION = '1.0.1';

	/**
	 * {@inheritdoc}
	 */
	protected static $base_table_name = 'kip_ab_events';

	/**
	 * {@inheritdoc}
	 */
	protected static $group = 'kip';

	/**
	 * {@inheritdoc}
	 */
	protected static $schema_slug = 'kip-ab-events';

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
				`variant` varchar(128) NOT NULL DEFAULT '',
				`goal` varchar(128) NOT NULL DEFAULT '',
				`post` int(11) NOT NULL DEFAULT '0',
				`time` datetime NOT NULL,
				`count` int(11) unsigned NOT NULL DEFAULT '1',
				`desktop` int(11) unsigned NOT NULL DEFAULT '0',
				`tablet` int(11) unsigned NOT NULL DEFAULT '0',
				`mobile` int(11) unsigned NOT NULL DEFAULT '0',
				`consolidated` tinyint(1) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`),
				UNIQUE KEY `type__variant__goal__post__time__consolidated` (type,variant,goal,post,time,consolidated)
			) {$charset_collate};
		";
	}
}
