<?php
/**
 * An exception used to signal no binding was found for container ID.
 *
 * @package lucatume\DI52
 *
 * @license GPL-3.0
 * Modified by kadencewp on 13-March-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace KadenceWP\KadenceConversions\lucatume\DI52;

use KadenceWP\KadenceConversions\Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * @package lucatume\DI52
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
