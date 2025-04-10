<?php

namespace KadenceWP\KadenceWhiteLabel;

use KadenceWP\KadenceWhiteLabel\lucatume\DI52\Container as DI52Container;
use KadenceWP\KadenceWhiteLabel\StellarWP\ContainerContract\ContainerInterface;

class Container implements ContainerInterface {
	protected $container;

	/**
	 * Container constructor.
	 */
	public function __construct() {
		$this->container = new DI52Container();
	}

	/**
	 * @inheritDoc
	 */
	public function bind( string $id, $implementation = null, array $afterBuildMethods = null ) {
		return $this->container->bind( $id, $implementation, $afterBuildMethods );
	}

	/**
	 * @inheritDoc
	 */
	public function get( string $id ) {
		return $this->container->get( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function has( string $id ) {
		return $this->container->has( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function singleton( string $id, $implementation = null, array $afterBuildMethods = null ) {
		$this->container->singleton( $id, $implementation, $afterBuildMethods );
	}

	/**
	 * Defer all other calls to the container object.
	 */
	public function __call( $name, $args ) {
		return $this->container->{$name}( ...$args );
	}
}
