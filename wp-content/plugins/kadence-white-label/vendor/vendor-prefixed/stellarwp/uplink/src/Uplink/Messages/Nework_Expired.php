<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by kadencewp on 22-January-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Messages;

class Network_Expired extends Message_Abstract {
	/**
	 * @inheritDoc
	 */
	public function get(): string {
		return esc_html__( 'Expired license. Consult your network administrator.', '%TEXTDOMAIN%' );
	}
}
