<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by kadencewp on 22-January-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Contracts;

interface Provider_Interface {
	/**
	 * Register action/filter listeners to hook into WordPress
	 *
	 * @return void
	 */
	public function register();
}
