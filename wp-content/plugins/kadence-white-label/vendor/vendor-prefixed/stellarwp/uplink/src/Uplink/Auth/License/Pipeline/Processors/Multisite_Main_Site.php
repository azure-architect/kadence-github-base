<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by kadencewp on 22-January-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare( strict_types=1 );

namespace KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Auth\License\Pipeline\Processors;

use Closure;
use KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Config;

final class Multisite_Main_Site {

	/**
	 * If we're on the main site any of the multisite licensing options are enabled,
	 * licensing is enabled.
	 *
	 * @param  bool  $is_multisite_license
	 * @param  Closure  $next
	 *
	 * @throws \RuntimeException
	 *
	 * @return bool
	 */
	public function __invoke( bool $is_multisite_license, Closure $next ): bool {
		if ( is_main_site() && Config::allows_network_licenses() ) {
			$is_multisite_license = true;
		}

		return $next( $is_multisite_license );
	}

}
