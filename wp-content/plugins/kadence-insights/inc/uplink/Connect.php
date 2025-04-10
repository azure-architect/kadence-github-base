<?php

namespace KadenceWP\Insights\Uplink;

use KadenceWP\Insights\Container;
use KadenceWP\Insights\StellarWP\Uplink\Register;
use KadenceWP\Insights\StellarWP\Uplink\Config;
use KadenceWP\Insights\StellarWP\Uplink\Uplink;
use KadenceWP\Insights\StellarWP\Uplink\Resources\Collection;
use KadenceWP\Insights\StellarWP\Uplink\Admin\License_Field;
use function KadenceWP\Insights\StellarWP\Uplink\get_resource;
use function KadenceWP\Insights\StellarWP\Uplink\set_license_key;
use function KadenceWP\Insights\StellarWP\Uplink\get_license_key;
use function KadenceWP\Insights\StellarWP\Uplink\validate_license;
use function KadenceWP\Insights\StellarWP\Uplink\get_license_field;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Connect
 * @package KadenceWP\KadenceShopKit\Uplink
 */
class Connect {

	/**
	 * Instance of this class
	 *
	 * @var null
	 */
	private static $instance = null;
	/**
	 * Instance Control
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		// Load licensing.
		add_action( 'plugins_loaded', [ $this, 'load_licensing' ], 2 );
	}
	/**
	 * Plugin specific text-domain loader.
	 *
	 * @return void
	 */
	public function load_licensing() {
		$container = new Container();
		Config::set_container( $container );
		Config::set_hook_prefix( 'kadence-insights' );
		Uplink::init();

		$plugin_slug    = 'kadence-insights';
		$plugin_name    = 'Kadence Insights';
		$plugin_version = KADENCE_INSIGHTS_VERSION;
		$plugin_path    = 'kadence-insights/kadence-insights.php';
		$plugin_class   = KadenceWP\Insights\Uplink\Helper::class;
		$license_class  = KadenceWP\Insights\Uplink\Helper::class;

		Register::plugin(
			$plugin_slug,
			$plugin_name,
			$plugin_version,
			$plugin_path,
			$plugin_class,
			$license_class
		);
		add_filter(
			'stellarwp/uplink/kadence-insights/api_get_base_url',
			function( $url ) {
				return 'https://licensing.kadencewp.com';
			}
		);
		add_filter(
			'stellarwp/uplink/kadence-insights/messages/valid_key',
			function ( $message, $expiration ) {
				return esc_html__( 'Your license key is valid', 'kadence-insights' );
			},
			10,
			2
		);
		add_filter(
			'stellarwp/uplink/kadence-insights/admin_js_source',
			function ( $url ) {
				return KADENCE_INSIGHTS_URL . 'inc/uplink/admin-views/license-admin.js';
			}
		);
		add_filter(
			'stellarwp/uplink/kadence-insights/admin_css_source',
			function ( $url ) {
				return KADENCE_INSIGHTS_URL . 'inc/uplink/admin-views/license-admin.css';
			}
		);
		add_filter(
			'stellarwp/uplink/kadence-insights/field-template_path',
			function ( $path, $uplink_path ) {
				return KADENCE_INSIGHTS_PATH . 'inc/uplink/admin-views/field.php';
			},
			10,
			2
		);
		add_filter( 'kadence_settings_enqueue_args', array( $this, 'register_license_validation' ), 10, 2 );
		add_filter( 'stellarwp/uplink/kadence-insights/license_field_html_render', array( $this, 'get_license_field_html' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'inactive_notice' ) );
		add_action( 'kadence_settings_dash_side_panel', array( $this, 'render_settings_field' ) );
	}
	/**
	 * Register settings.
	 */
	public function register_license_validation( $args, $opt_name ) {
		if ( ! empty( $opt_name ) && 'kadence_insights' === $opt_name ) {
			$key          = get_license_key( 'kadence-insights' );
			$license_data = validate_license( 'kadence-insights', $key );
			if ( isset( $license_data ) && is_object( $license_data ) && method_exists( $license_data, 'is_valid' ) && $license_data->is_valid() ) {
				$license_status = true;
			} else {
				$license_status = false;
			}
			$args['licenseActive'] = $license_status;
		}
		return $args;
	}
	/**
	 * Get license field html.
	 */
	public function get_license_field_html( $field, $args ) {
		$field = sprintf(
			'<div class="%6$s" id="%2$s" data-slug="%2$s" data-plugin="%9$s" data-plugin-slug="%10$s" data-action="%11$s">
					<fieldset class="stellarwp-uplink__settings-group">
						<div class="stellarwp-uplink__settings-group-inline">
						%12$s
						%13$s
						</div>
						<input type="%1$s" name="%3$s" value="%4$s" placeholder="%5$s" class="regular-text stellarwp-uplink__settings-field" />
						%7$s
					</fieldset>
					%8$s
				</div>',
			! empty( $args['value'] ) ? 'hidden' : 'text',
			esc_attr( $args['path'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['value'] ),
			esc_attr( __( 'License Key', 'kadence-insights' ) ),
			esc_attr( $args['html_classes'] ?: '' ),
			$args['html'],
			'<input type="hidden" value="' . wp_create_nonce( 'stellarwp_uplink_group_' ) . '" class="wp-nonce" />',
			esc_attr( $args['plugin'] ),
			esc_attr( $args['plugin_slug'] ),
			esc_attr( Config::get_hook_prefix_underscored() ),
			! empty( $args['value'] ) ? '<input type="text" name="obfuscated-key" disabled value="' . $this->obfuscate_key( $args['value'] ) . '" class="regular-text stellarwp-uplink__settings-field-obfuscated" />' : '',
			! empty( $args['value'] ) ? '<button type="submit" class="button button-secondary stellarwp-uplink-license-key-field-clear">' . esc_html__( 'Clear', 'kadence-insights' ) . '</button>' : ''
		);

		return $field;
	}
	/**
	 * Obfuscate license key.
	 */
	public function obfuscate_key( $key ) {
		$start = 3;
		$length = mb_strlen( $key ) - $start - 3;
		$mask_string = preg_replace( '/\S/', 'X', $key );
		$mask_string = mb_substr( $mask_string, $start, $length );
		$input_string = substr_replace( $key, $mask_string, $start, $length );
		return $input_string;
	}
	/**
	 * Register settings
	 */
	public function render_settings_field( $slug ) {
		if ( empty( $slug ) || 'kadence_insights' !== $slug ) {
			return;
		}
		?>
		<div class="license-section sidebar-section components-panel">
			<div class="components-panel__body is-opened">
				<?php
				get_license_field()->render_single( 'kadence-insights' );
				?>
			</div>
		</div>
		<?php
	}
	/**
	 * Displays an inactive notice when the software is inactive.
	 */
	public function inactive_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_GET['page'] ) && ( 'kadence-insights-settings' == $_GET['page'] ) ) {
			// For Now, clear when on the settings page.
			set_transient( 'kadence_insights_license_status_check', false );
			return;
		}
		$valid_license   = false;
		// Add below once we've given time for everyones cache to update.
		// $plugin          = get_resource( 'kadence-insights' );
		// if ( $plugin ) {
		// 	$valid_license = $plugin->has_valid_license();
		// }
		$key = get_license_key( 'kadence-insights' );
		if ( ! empty( $key ) ) {
			// Check with transient first, if not then check with server.
			$status = get_transient( 'kadence_insights_license_status_check' );
			if ( false === $status || ( strpos( $status, $key ) === false ) ) {
				$license_data = validate_license( 'kadence-insights', $key );
				if ( isset( $license_data ) && is_object( $license_data ) && method_exists( $license_data, 'is_valid' ) && $license_data->is_valid() ) {
					$status = 'valid';
				} else {
					$status = 'invalid';
				}
				$status = $key . '_' . $status;
				set_transient( 'kadence_insights_license_status_check', $status, WEEK_IN_SECONDS );
			}
			if ( strpos( $status, $key ) !== false ) {
				$valid_check = str_replace( $key . '_', '', $status );
				if ( 'valid' === $valid_check ) {
					$valid_license = true;
				}
			}
		}
		if ( ! $valid_license ) {
			echo '<div class="error">';
			echo '<p>' . __( 'Kadence Insights has not been activated.', 'kadence-insights' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=kadence-insights-settings&license=show' ) ) . '">' . __( 'Click here to activate.', 'kadence-insights' ) . '</a></p>';
			echo '</div>';
		}
	}
}
Connect::get_instance();
