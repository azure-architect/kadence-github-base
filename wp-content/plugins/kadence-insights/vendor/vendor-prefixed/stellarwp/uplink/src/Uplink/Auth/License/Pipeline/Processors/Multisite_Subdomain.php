<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare( strict_types=1 );

namespace KadenceWP\Insights\StellarWP\Uplink\Auth\License\Pipeline\Processors;

use Closure;
use KadenceWP\Insights\StellarWP\Uplink\Auth\License\Pipeline\Traits\Multisite_Trait;
use KadenceWP\Insights\StellarWP\Uplink\Config;
use Throwable;

final class Multisite_Subdomain {

	use Multisite_Trait;

	/**
	 * Checks if a sub-site already has a network token.
	 *
	 * @param  bool $is_multisite_license
	 * @param  Closure  $next
	 *
	 * @return bool
	 */
	public function __invoke( bool $is_multisite_license, Closure $next ): bool {
		if ( is_main_site() ) {
			return $next( $is_multisite_license );
		}

		try {
			if ( $this->is_subdomain() ) {
				return Config::allows_network_subdomain_license();
			}
		} catch ( Throwable $e ) {
			return false;
		}

		return $next( $is_multisite_license );
	}

}
