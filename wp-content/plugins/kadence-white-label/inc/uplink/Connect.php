<?php

namespace KadenceWP\KadenceWhiteLabel\Uplink;

use KadenceWP\KadenceWhiteLabel\Container;
use KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Register;
use KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Config;
use KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Uplink;
use KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Resources\Collection;
use KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\Admin\License_Field;
use function KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\get_resource;
use function KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\set_license_key;
use function KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\get_license_key;
use function KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\validate_license;
use function KadenceWP\KadenceWhiteLabel\StellarWP\Uplink\get_license_field;
use function is_plugin_active_for_network;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Connect
 * @package KadenceWP\KadenceWhiteLabel\Uplink
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
		add_action( 'plugins_loaded', array( $this, 'load_licensing' ), 2 );
	}
	/**
	 * Plugin specific text-domain loader.
	 *
	 * @return void
	 */
	public function load_licensing() {
		$container = new Container();
		Config::set_container( $container );
		Config::set_hook_prefix( 'kadence-white-label' );
		Uplink::init();

		$plugin_slug    = 'kadence-white-label';
		$plugin_name    = 'Kadence White Label';
		$plugin_version = KADENCE_WHITE_VERSION;
		$plugin_path    = 'kadence-white-label/kadence-white-label.php';
		$plugin_class   = KadenceWP\KadenceWhiteLabel\Plugin::class;
		$license_class  = KadenceWP\KadenceWhiteLabel\Uplink\Helper::class;

		Register::plugin(
			$plugin_slug,
			$plugin_name,
			$plugin_version,
			$plugin_path,
			$plugin_class,
			$license_class,
		);
		add_filter(
			'stellarwp/uplink/kadence-white-label/api_get_base_url',
			function( $url ) {
				return 'https://licensing.kadencewp.com';
			}
		);
		add_filter(
			'stellarwp/uplink/kadence-white-label/messages/valid_key',
			function ( $message, $expiration ) {
				return esc_html__( 'Your license key is valid', 'kadence-white-label' );
			},
			10,
			2
		);
		add_filter(
			'stellarwp/uplink/kadence-white-label/admin_js_source',
			function ( $url ) {
				return KADENCE_WHITE_URL . 'inc/uplink/admin-views/license-admin.js';
			}
		);
		add_filter(
			'stellarwp/uplink/kadence-white-label/admin_css_source',
			function ( $url ) {
				return KADENCE_WHITE_URL . 'inc/uplink/admin-views/license-admin.css';
			}
		);
		add_filter( 
			'stellarwp/uplink/kadence-white-label/field-template_path',
			function ( $path, $uplink_path ) {
				return KADENCE_WHITE_PATH . 'inc/uplink/admin-views/field.php';
			},
			10,
			2
		);
		add_filter( 'stellarwp/uplink/kadence-white-label/license_field_html_render', array( $this, 'get_license_field_html' ), 10, 2 );

		add_action( 'network_admin_menu', array( $this, 'create_admin_pages' ), 1 );
		add_action( 'admin_notices', array( $this, 'inactive_notice' ) );
		add_action( 'kadence_settings_dash_side_panel', array( $this, 'render_settings_field' ) );
		// Save Network.
		add_action( 'network_admin_edit_kadence_license_update_network_options', array( $this, 'update_network_options' ) );
	}
	/**
	 * Register settings
	 */
	public function render_settings_field( $slug ) {
		if ( empty( $slug ) || 'kadence_white_label' !== $slug ) {
			return;
		}
		if ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( 'kadence-white-label/kadence-white-label.php' ) && $this->is_network_authorize_enabled() ) {
			?>
			<div class="license-section sidebar-section components-panel">
				<div class="components-panel__body is-opened">
					<?php
					echo esc_html__( 'Network License Controlled', 'kadence-white-label' );
					?>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="license-section sidebar-section components-panel">
				<div class="components-panel__body is-opened">
					<?php
					get_license_field()->render_single( 'kadence-white-label' );
					?>
				</div>
			</div>
			<?php
		}
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
			esc_attr( __( 'License Key', 'kadence-white-label' ) ),
			esc_attr( $args['html_classes'] ?: '' ),
			$args['html'],
			'<input type="hidden" value="' . wp_create_nonce( 'stellarwp_uplink_group_' ) . '" class="wp-nonce" />',
			esc_attr( $args['plugin'] ),
			esc_attr( $args['plugin_slug'] ),
			esc_attr( Config::get_hook_prefix_underscored() ),
			! empty( $args['value'] ) ? '<input type="text" name="obfuscated-key" disabled value="' . $this->obfuscate_key( $args['value'] ) . '" class="regular-text stellarwp-uplink__settings-field-obfuscated" />' : '',
			! empty( $args['value'] ) ? '<button type="submit" class="button button-secondary stellarwp-uplink-license-key-field-clear">' . esc_html__( 'Clear', 'kadence-white-label' ) . '</button>' : ''
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
	 * Check if network authorize is enabled.
	 */
	public function is_network_authorize_enabled() {
		$network_enabled = ! apply_filters( 'kadence_activation_individual_multisites', true );
		if ( ! $network_enabled && defined( 'KADENCE_ACTIVATION_NETWORK_ENABLED' ) && KADENCE_ACTIVATION_NETWORK_ENABLED ) {
			$network_enabled = true;
		}
		return $network_enabled;
	}
	/**
	 * This function here is hooked up to a special action and necessary to process
	 * the saving of the options. This is the big difference with a normal options
	 * page.
	 */
	public function update_network_options() {
		$options_id = $_REQUEST['option_page'];

		// Make sure we are posting from our options page.
		check_admin_referer( $options_id . '-options' );
		if ( isset( $_POST[ 'stellarwp_uplink_license_key_kadence-white-label' ] ) ) {
			$value = sanitize_text_field( trim( $_POST[ 'stellarwp_uplink_license_key_kadence-white-label' ] ) );
			set_license_key( 'kadence-white-label', $value );

			// At last we redirect back to our options page.
			wp_redirect( network_admin_url( 'settings.php?page=kadence-white-label-license' ) );
			exit;
		}
	}
	/**
	 * Register settings
	 */
	public function create_admin_pages() {
		if ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( 'kadence-white-label/kadence-white-label.php' ) && $this->is_network_authorize_enabled() ) {
			add_action( 'network_admin_menu', function() {
				add_submenu_page( 'settings.php',  __( 'Kadence White Label - License', 'kadence-white-label' ), __( 'White Label License', 'kadence-white-label' ), 'manage_options', 'kadence-white-label-license', array( $this, 'render_network_settings_page' ), 999 );
			}, 21 );
		}
	}
	/**
	 * Register settings
	 */
	public function render_network_settings_page() {
		$slug       = 'kadence-white-label';
		$field      = get_license_field();
		$key        = get_license_key( $slug );
		$action_postfix = Config::get_hook_prefix_underscored();
		$group          = $field->get_group_name( sanitize_title( $slug ) );
		wp_enqueue_script( sprintf( 'stellarwp-uplink-license-admin-%s', $slug ) );
		wp_enqueue_style( sprintf( 'stellarwp-uplink-license-admin-%s', $slug ) );
		echo '<h3>Kadence White Label</h3>';
		echo '<form action="edit.php?action=kadence_license_update_network_options" method="post" id="kadence-license-kadence-white-label">';
		settings_fields( $group );
		$html = sprintf( '<p class="tooltip description">%s</p>', __( 'A valid license key is required for support and updates', 'kadence-white-label' ) );
		$html .= '<div class="license-test-results"><img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="ajax-loading-license" alt="Loading" style="display: none"/>';
		$html .= '<div class="key-validity"></div></div>';
		echo '<div class="stellarwp-uplink__license-field">';
		echo '<label for="stellarwp_uplink_license_key_kadence-white-label">License Key</label>';
		echo sprintf(
			'<div class="%6$s" id="%2$s" data-slug="%2$s" data-plugin="%9$s" data-plugin-slug="%10$s" data-action="%11$s">
                    <fieldset class="stellarwp-uplink__settings-group">
                        <input type="%1$s" name="%3$s" value="%4$s" placeholder="%5$s" class="regular-text stellarwp-uplink__settings-field" />
                        %7$s
                    </fieldset>
				    %8$s
				</div>',
			'text',
			'kadence-white-label/kadence-white-label.php',
			'stellarwp_uplink_license_key_kadence-white-label',
			esc_attr( $key ),
			esc_attr__( 'License Key', 'kadence-white-label' ),
			'stellarwp-uplink-license-key-field',
			$html,
			'<input type="hidden" value="' . wp_create_nonce( 'stellarwp_uplink_group_' ) . '" class="wp-nonce" />',
			esc_attr( 'kadence-white-label/kadence-white-label.php' ),
			esc_attr( 'kadence-white-label' ),
			esc_attr( 'kadence_white_label' )
		);
		echo '</div>';
		submit_button( esc_html__( 'Save Changes', 'kadence-white-label' ) );
		echo '</form>';
	}
	/**
	 * Register settings
	 */
	public function render_settings_page() {
		$fields = Config::get_container()->get( License_Field::class );

		$fields->render_single( 'kadence-white-label' );
	}
	/**
	 * Displays an inactive notice when the software is inactive.
	 */
	public function inactive_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_GET['page'] ) && ( 'kadence-white-label-settings' == $_GET['page'] ) ) {
			return;
		}
		$valid_license   = false;
		$network_enabled = $this->is_network_authorize_enabled();
		// Add below once we've given time for everyones cache to update.
		// $plugin          = get_resource( 'kadence-white-label' );
		// if ( $plugin ) {
		// 	$valid_license = $plugin->has_valid_license();
		// }
		$key = get_license_key( 'kadence-white-label' );
		if ( ! empty( $key ) ) {
			// Check with transient first, if not then check with server.
			$status = get_transient( 'kadence_white_label_license_status_check' );
			if ( false === $status || ( strpos( $status, $key ) === false ) ) {
				$license_data = validate_license( 'kadence-white-label', $key );
				if ( isset( $license_data ) && is_object( $license_data ) && method_exists( $license_data, 'is_valid' ) && $license_data->is_valid() ) {
					$status = 'valid';
				} else {
					$status = 'invalid';
				}
				$status = $key . '_' . $status;
				set_transient( 'kadence_white_label_license_status_check', $status, WEEK_IN_SECONDS );
			}
			if ( strpos( $status, $key ) !== false ) {
				$valid_check = str_replace( $key . '_', '', $status );
				if ( 'valid' === $valid_check ) {
					$valid_license = true;
				}
			}
		}
		if ( ! $valid_license ) {
			if ( is_plugin_active_for_network( 'kadence-white-label/kadence-white-label.php' ) && $network_enabled ) {
				if ( current_user_can( 'manage_network_options' ) ) {
					echo '<div class="error">';
					echo '<p>' . esc_html__( 'Kadence White Label has not been activated.', 'kadence-white-label' ) . ' <a href="' . esc_url( network_admin_url( 'settings.php?page=kadence-white-label-license' ) ) . '">' . __( 'Click here to activate.', 'kadence-white-label' ) . '</a></p>';
					echo '</div>';
				}
			} else {
				echo '<div class="error">';
				echo '<p>' . __( 'Kadence White Label has not been activated.', 'kadence-white-label' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=kadence-white-label-settings' ) ) . '">' . __( 'Click here to activate.', 'kadence-white-label' ) . '</a></p>';
				echo '</div>';
			}
		}
	}
}
Connect::get_instance();
