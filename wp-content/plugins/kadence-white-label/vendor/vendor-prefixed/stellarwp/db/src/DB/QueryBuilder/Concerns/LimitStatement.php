<?php
/**
 * @license GPL-2.0
 *
 * Modified by kadencewp on 05-April-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace KadenceWP\KadenceWhiteLabel\StellarWP\DB\QueryBuilder\Concerns;

/**
 * @since 1.0.0
 */
trait LimitStatement {
	/**
	 * @var int
	 */
	protected $limit;

	/**
	 * @param  int  $limit
	 *
	 * @return $this
	 */
	public function limit( $limit ) {
		$this->limit = (int) $limit;

		return $this;
	}

	protected function getLimitSQL() {
		return $this->limit
			? [ "LIMIT {$this->limit}" ]
			: [];
	}
}
