<?php
/**
 * @license GPL-2.0
 *
 * Modified by kadencewp on 05-April-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace KadenceWP\KadenceWhiteLabel\StellarWP\DB\QueryBuilder\Types;

/**
 * @since 1.0.0
 */
class Math extends Type {
	const SUM = 'SUM';
	const MIN = 'MIN';
	const MAX = 'MAX';
	const COUNT = 'COUNT';
	const AVG = 'AVG';
}
