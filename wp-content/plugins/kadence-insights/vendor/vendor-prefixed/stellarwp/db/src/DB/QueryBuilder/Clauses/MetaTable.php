<?php
/**
 * @license GPL-2.0
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace KadenceWP\Insights\StellarWP\DB\QueryBuilder\Clauses;

use KadenceWP\Insights\StellarWP\DB\QueryBuilder\QueryBuilder;

/**
 * @since 1.0.0
 */
class MetaTable {
	/**
	 * @var string
	 */
	public $tableName;

	/**
	 * @var string
	 */
	public $keyColumnName;

	/**
	 * @var string
	 */
	public $valueColumnName;

	/**
	 * @param  string  $table
	 * @param  string  $metaKeyColumnName
	 * @param  string  $metaValueColumnName
	 */
	public function __construct( $table, $metaKeyColumnName, $metaValueColumnName ) {
		$this->tableName       = QueryBuilder::prefixTable( $table );
		$this->keyColumnName   = trim( $metaKeyColumnName );
		$this->valueColumnName = trim( $metaValueColumnName );
	}
}
