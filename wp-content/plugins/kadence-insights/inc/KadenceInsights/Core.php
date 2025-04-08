<?php
/**
 * The main plugin class.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */

namespace KadenceWP\Insights;

use InvalidArgumentException;
use RuntimeException;
use KadenceWP\Insights\StellarWP\ContainerContract\ContainerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The primary class responsible for booting up the plugin.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */
class Core {

	public const PLUGIN_FILE         = 'kadence_insights.plugin_file';

	/**
	 * The server path to the plugin's main file.
	 *
	 * @var string
	 */
	private string $plugin_file;

	/**
	 * The centralized container.
	 *
	 * @since 0.1.0
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * The singleton instance for the plugin.
	 *
	 * @var Core
	 */
	private static self $instance;

	/**
	 * An array of providers to register within the container.
	 *
	 * @since 0.1.0
	 *
	 * @var array<int,string>
	 */
	private $providers = [
		Admin\Provider::class,
		Analytics\Provider::class,
		Posts\Provider::class,
		Blocks\Provider::class,
	];
	/**
	 * @param  string    $plugin_file The full server path to the main plugin file.
	 * @param  Container $container The container instance.
	 */
	private function __construct(
		string $plugin_file,
		Container $container
	) {
		$this->plugin_file = $plugin_file;
		$this->container   = $container;
		$this->container->singleton( \KadenceWP\Insights\Psr\Container\ContainerInterface::class, $this->container );
		$this->container->singleton( KadenceWP\Insights\Psr\Container\ContainerInterface::class, $this->container );
		$this->container->singleton( ContainerInterface::class, $this->container );

		// Set container variables available to pre bootstrap providers.
		$this->container->setVar( self::PLUGIN_FILE, $this->plugin_file );
	}
	/**
	 * Get the singleton instance of our plugin.
	 *
	 * @param  string|null    $plugin_file  The full server path to the main plugin file.
	 * @param  Container|null $container    The container instance.
	 *
	 * @throws InvalidArgumentException If no existing instance and no plugin file or container is provided.
	 *
	 * @return Core
	 */
	public static function instance( ?string $plugin_file = null, ?Container $container = null ): Core {
		if ( ! isset( self::$instance ) ) {
			if ( ! $plugin_file ) {
				throw new InvalidArgumentException( 'You must provide a $plugin_file path' );
			}

			if ( ! $container ) {
				throw new InvalidArgumentException( sprintf( 'You must provide a %s instance!', Container::class ) );
			}

			self::$instance = new self( $plugin_file, $container );
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @action plugins_loaded
	 *
	 * @return void
	 */
	public function init(): void {

		$this->container->setVar( self::PLUGIN_FILE, $this->plugin_file );

		// Register all providers.
		foreach ( $this->providers as $class ) {
			$this->container->get( $class )->register( $this->container );
		}
	}

	/**
	 * Returns the container instance.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Prevent wakeup.
	 *
	 * @throws RuntimeException When attempting to wake up this instance.
	 */
	public function __wakeup(): void {
		throw new RuntimeException( 'method not implemented' );
	}

	/**
	 * Prevent sleep.
	 *
	 * @throws RuntimeException When attempting to sleep this instance.
	 */
	public function __sleep(): array {
		throw new RuntimeException( 'method not implemented' );
	}

	/**
	 * Prevent cloning.
	 *
	 * @throws RuntimeException When attempting to clone this instance.
	 */
	private function __clone() {
		throw new RuntimeException( 'Cloning not allowed' );
	}
}
