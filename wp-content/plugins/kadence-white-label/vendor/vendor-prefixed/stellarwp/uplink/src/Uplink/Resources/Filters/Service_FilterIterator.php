<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by kadencewp on 22-January-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare( strict_types=1 );

namespace KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Resources\Filters;

use Countable;
use FilterIterator;
use KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Resources\Service;

/**
 * @method Service current()
 */
class Service_FilterIterator extends FilterIterator implements Countable {

	/**
	 * @inheritDoc
	 */
	public function accept(): bool {
		$resource = $this->getInnerIterator()->current();

		return 'service' === $resource->get_type();
	}

	/**
	 * @inheritDoc
	 */
	public function count() : int {
		return iterator_count( $this );
	}

}
