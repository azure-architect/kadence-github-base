<?php
/**
 * Provides an API for all classes that are runnable.
 *
 * @since 1.0.0
 *
 * @package KadenceWP\KadenceWhiteLabel\StellarWP\Telemetry\Contracts
 *
 * @license GPL-2.0-or-later
 * Modified by kadencewp on 05-April-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace KadenceWP\KadenceWhiteLabel\StellarWP\Telemetry\Contracts;

/**
 * Provides an API for all classes that are runnable.
 *
 * @since 1.0.0
 *
 * @package KadenceWP\KadenceWhiteLabel\StellarWP\Telemetry\Contracts
 */
interface Runnable {
	/**
	 * Run the intended action.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run();
}
