<?php
/**
 * An interface that provides the API for all data providers.
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
 * An interface that provides the API for all data providers.
 *
 * @since 1.0.0
 *
 * @package KadenceWP\KadenceWhiteLabel\StellarWP\Telemetry\Contracts
 */
interface Data_Provider {

	/**
	 * Gets the data that should be sent to the telemetry server.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_data(): array;
}
