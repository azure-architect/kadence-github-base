<?php
/**
 * Kadence_White_Label Class
 *
 * @package Kadence White Label
 */

namespace KadenceWP\KadenceWhiteLabel;

use WP_Customize_Themes_Panel;
use KadenceWP\KadenceWhiteLabel\Icon;
use KadenceWP\KadencePro\Uplink\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Kadence White Label Main Class
 */
class Plugin {
	/**
	 * Base path.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $settings = array();
	/**
	 * Action on init.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}
	/**
	 * Init function.
	 */
	public function init() {
		$settings = $this->get_settings();
		if ( isset( $settings['enable_white_label'] ) && true === $settings['enable_white_label'] ) {
			// Change Theme Details.
			add_filter( 'all_themes', array( $this, 'replace_details_on_network_themes_page' ) );
			add_filter( 'wp_prepare_themes_for_js', array( $this, 'replace_details_on_themes_page' ) );
			add_filter( 'gettext', array( $this, 'replace_branding_in_strings' ), 90, 3 );
			add_filter( 'kadence_theme_dashboard_logo', array( $this, 'replace_dashboard_logo' ) );
			if ( isset( $settings['hide_theme_page'] ) && true === $settings['hide_theme_page'] ) {
				add_action( 'admin_print_styles-appearance_page_kadence', array( $this, 'theme_dash_scripts' ) );
			}
			// Replace status text on Dashboard.
			add_filter( 'update_right_now_text', array( $this, 'replace_details_on_dashboard_status' ) );
			// Replace details on Customizer page.
			add_action( 'customize_register', array( $this, 'replace_theme_details_in_customizer' ) );
			// Change Agency Logo
			add_filter( 'kadence_blocks_dash_logo', array( $this, 'replace_dashboard_logo' ) );
			// Hide White label
			add_filter( 'kadence_white_label_menu_type', array( $this, 'menu_type' ) );
			// Replace Theme api results.
			add_action( 'admin_enqueue_scripts', array( $this, 'theme_update_update_page_scripts' ) );
			// add_filter( 'themes_api_result', array( $this, 'replace_theme_details_on_api_requests' ), 99, 3 );
			add_filter( 'site_transient_update_themes', array( $this, 'replace_theme_details_on_updates_page' ), 20 );
			// Trigger an update to the white label hidden setting.
			add_action( 'admin_init', array( $this, 'update_hidden_white_label' ) );
			// Notices.
			add_action( 'admin_notices', array( $this, 'remove_admin_notices' ), 1 );
			// White Label Plugin.
			add_filter( 'all_plugins', array( $this, 'replace_plugin_details_on_plugins_page' ) );
			add_filter( 'plugins_api_result', array( $this, 'replace_plugin_details_on_api_requests' ), 99, 3 );
			add_filter( 'site_transient_update_plugins', array( $this, 'replace_plugin_details_on_updates_page' ), 20 );
			add_filter( 'kadence_blocks_brand_name', array( $this, 'replace_blocks_brand_name' ), 90, 1 );
			// Blocks filter. 
			add_filter( 'kadence_blocks_admin_svg_icon', array( $this, 'change_menu_icon' ) );
			add_action( 'admin_print_styles-toplevel_page_kadence-blocks', array( $this, 'blocks_dash_scripts' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'add_block_editor_changes' ), 1 );
			// Disable AI.
			if ( isset( $settings['disable_ai'] ) && true === $settings['disable_ai'] ) {
				define( 'KADENCE_BLOCKS_AI_DISABLED', true );
				// Notices.
				add_action( 'admin_menu', array( $this, 'remove_admin_submenu' ), 999 );
			}
		} elseif ( is_admin() ) {
			add_filter( 'plugin_action_links_kadence-white-label/kadence-white-label.php', array( $this, 'add_settings_link' ) );
		}
	}
	/**
	 * Add settings link
	 *
	 * @param array $links plugin activate/deactivate links array.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=kadence-white-label-settings' ) ) . '">' . __( 'Settings', 'kadence-white-label' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}
	/**
	 * Remove admin notices.
	 */
	public function remove_admin_notices() {
		if ( class_exists( '\KadenceWP\KadencePro\Uplink\Connect' ) ) {
			$theme_pro_connect = \KadenceWP\KadencePro\Uplink\Connect::get_instance();
			remove_action( 'admin_notices', array( $theme_pro_connect, 'inactive_notice' ) );
		}
		if ( class_exists( '\KadenceWP\KadenceBlocksPro\Uplink\Connect' ) ) {
			$blocks_pro_connect = \KadenceWP\KadenceBlocksPro\Uplink\Connect::get_instance();
			remove_action( 'admin_notices', array( $blocks_pro_connect, 'inactive_notice' ) );
		}
	}
	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		if ( empty( self::$settings ) ) {
			$default_settings = array(
				'enable_white_label' => true,
				'hide_theme_page'    => true,
				'blocks_icon'        => 'cube',
			);
			$settings         = json_decode( get_option( 'kadence_white_label' ), true );
			$settings         = wp_parse_args( $settings, $default_settings );
			self::$settings   = $settings;
		}
		return self::$settings;
	}
	/**
	 * Add scripts to the theme dashboard.
	 */
	public function theme_dash_scripts() {
		$settings = $this->get_settings();
		wp_enqueue_style( 'kadence-white-label-dashboard', KADENCE_WHITE_URL . '/build/dashboard.css', array( 'wp-components' ), KADENCE_WHITE_VERSION );
		wp_enqueue_script( 'kadence-white-label-dashboard', KADENCE_WHITE_URL . '/build/dashboard.js', array( 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-components', 'wp-api', 'wp-hooks', 'wp-edit-post', 'lodash', 'wp-block-library', 'wp-block-editor', 'wp-editor', 'jquery', 'kadence-dashboard' ), KADENCE_WHITE_VERSION, true );
		wp_localize_script(
			'kadence-white-label-dashboard',
			'kadenceWhiteLabelParams',
			array(
				'themeURL' => ! empty( $settings['theme_url'] ) ? $settings['theme_url'] : '',
				'hasPro'   => class_exists( 'Kadence_Theme_Pro' ) ? true : false,
			),
		);
	}
	/**
	 * Add scripts to the blocks dashboard.
	 */
	public function blocks_dash_scripts() {
		$settings = $this->get_settings();
		wp_enqueue_style( 'kadence-white-label-dashboard', KADENCE_WHITE_URL . '/build/dashboard.css', array( 'wp-components' ), KADENCE_WHITE_VERSION );
		wp_enqueue_script( 'kadence-white-label-dashboard', KADENCE_WHITE_URL . '/build/dashboard.js', array( 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-components', 'wp-api', 'wp-hooks', 'wp-edit-post', 'lodash', 'wp-block-library', 'wp-block-editor', 'wp-editor', 'jquery', 'kadence-dashboard' ), KADENCE_WHITE_VERSION, true );
		wp_localize_script(
			'kadence-white-label-dashboard',
			'kadenceWhiteLabelParams',
			array(
				'themeURL' => ! empty( $settings['theme_url'] ) ? $settings['theme_url'] : '',
				'hasPro'   => class_exists( 'Kadence_Theme_Pro' ) ? true : false,
			),
		);
	}
	/**
	 * Add scripts to the block editor.
	 */
	public function add_block_editor_changes() {
		$settings = $this->get_settings();
		wp_enqueue_style( 'kadence-white-label-dashboard', KADENCE_WHITE_URL . '/build/dashboard.css', array( 'wp-components' ), KADENCE_WHITE_VERSION );
		wp_enqueue_script( 'kadence-white-label-dashboard', KADENCE_WHITE_URL . '/build/dashboard.js', array( 'wp-blocks', 'wp-i18n', 'wp-element' ), KADENCE_WHITE_VERSION, true );
		wp_localize_script(
			'kadence-white-label-dashboard',
			'kadenceWhiteLabelParams',
			array(
				'blocksName' => ! empty( $settings['blocks_name'] ) ? $settings['blocks_name'] : '',
				'blocksIcon' => ! empty( $settings['blocks_icon'] ) ? $settings['blocks_icon'] : 'cube',
				'blocksIconSVG' => ! empty( $settings['blocks_icon_svg'] ) ? $settings['blocks_icon_svg'] : '',
				'themeURL' => ! empty( $settings['theme_url'] ) ? $settings['theme_url'] : '',
				'hasPro'   => class_exists( 'Kadence_Theme_Pro' ) ? true : false,
			),
		);
	}
	/**
	 * Replace dashboard logo.
	 *
	 * @param string $logo Logo.
	 * @return string
	 */
	public function replace_dashboard_logo( $logo ) {
		$settings = $this->get_settings();
		if ( ! empty( $settings['agency_image'] ) ) {
			$logo = $settings['agency_image'];
		}
		return $logo;
	}
	/**
	 * Update hidden white label.
	 */
	public function update_hidden_white_label() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_GET['page'] ) && ( 'kadence' == $_GET['page'] ) && isset( $_GET['show_white_label'] ) && 'true' == $_GET['show_white_label'] ) {
			$settings = json_decode( get_option( 'kadence_white_label' ), true );
			$settings['hide_white_label'] = 0;
			update_option( 'kadence_white_label', wp_json_encode( $settings ) );
			$redirect = site_url( remove_query_arg( 'show_white_label', false ) );
			wp_safe_redirect( $redirect );
			exit;
		}
	}
	/**
	 * Remove admin submenu.
	 */
	public function remove_admin_submenu() {
		$page = remove_submenu_page( 'kadence-blocks', 'kadence-blocks-home' );
	}
	/**
	 * Change menu icon.
	 *
	 * @param string $icon Icon.
	 * @return string
	 */
	public function change_menu_icon( $icon ) {
		$settings = $this->get_settings();
		if ( ! empty( $settings['blocks_icon'] ) ) {
			if ( 'custom' === $settings['blocks_icon'] ) {
				$icon = $settings['blocks_icon_svg'];
			} else {
				$icon = Icon::get_icon( $settings['blocks_icon'] );
			}
		}
		return $icon;
	}
	/**
	 * Change the menu type.
	 *
	 * @param string $menu_type Menu type.
	 * @return string
	 */
	public function menu_type( $menu_type ) {
		$settings = $this->get_settings();
		if ( isset( $settings['hide_white_label'] ) && $settings['hide_white_label'] ) {
			$menu_type = 'hidden';
		}
		if ( defined( 'KADENCE_WHITE_LABEL_CONTROLS_HIDDEN' ) ) {
			if ( KADENCE_WHITE_LABEL_CONTROLS_HIDDEN ) {
				$menu_type = 'hidden';
			} else {
				$menu_type = 'submenu';
			}
		}
		return $menu_type;
	}
	/**
	 * Replace theme details in customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize WP Customize Manager.
	 */
	public function replace_theme_details_in_customizer( $wp_customize ) {
		$settings = $this->get_settings();
		if ( ! empty( $settings['theme_name'] ) ) {
			$panel = $wp_customize->get_panel( 'themes' );
			$wp_customize->add_panel( new \WP_Customize_Themes_Panel( $wp_customize, 'themes', array(
				'title'       => $settings['theme_name'],
				'description' => $panel->description,
				'capability'  => $panel->capability,
				'priority'    => $panel->priority,
			) ) );
		}
	}
	/**
	 * Replace branding in strings.
	 *
	 * @param string $translated_text Translated text.
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	public function replace_branding_in_strings( $translated_text, $text, $domain ) {
		if ( 'kadence' === $domain ) {
			$settings = $this->get_settings();
			switch ( $text ) {
				case 'Kadence':
					if ( ! empty( $settings['theme_name_short'] ) ) {
						$translated_text = $settings['theme_name_short'];
					} else if ( ! empty( $settings['theme_name'] ) ) {
						$translated_text = $settings['theme_name'];
					}
					break;
				case 'Kadence - Next Generation Theme':
					if ( ! empty( $settings['theme_name'] ) ) {
						$translated_text = $settings['theme_name'];
					}
					break;
				case 'Learn how to use this':
					if ( ! empty( $settings['theme_name'] ) ) {
						$translated_text = '';
					}
					break;
			}
		} elseif ( 'kadence-blocks' === $domain ) {
			$settings = $this->get_settings();
			switch ( $text ) {
				case 'Kadence Blocks – Gutenberg Blocks for Page Builder Features':
					if ( ! empty( $settings['blocks_name'] ) ) {
						$translated_text = $settings['blocks_name'];
					}
					break;
				case 'Gutenberg Blocks with AI by Kadence WP – Page Builder Features':
					if ( ! empty( $settings['blocks_name'] ) ) {
						$translated_text = $settings['blocks_name'];
					}
					break;
				case 'Kadence':
					if ( ! empty( $settings['blocks_menu_name'] ) ) {
						$translated_text = $settings['blocks_menu_name'];
					}
					break;
			}
		} elseif ( 'kadence-blocks-pro' === $domain ) {
			$settings = $this->get_settings();
			switch ( $text ) {
				case 'Kadence Blocks - PRO Extension':
					if ( ! empty( $settings['blocks_pro_name'] ) ) {
						$translated_text = $settings['blocks_pro_name'];
					}
					break;
			}
		} elseif ( 'kadence-pro' === $domain ) {
			$settings = $this->get_settings();
			switch ( $text ) {
				case 'Kadence Pro Addon':
				case 'Kadence Pro':
					if ( ! empty( $settings['theme_pro_name'] ) ) {
						$translated_text = $settings['theme_pro_name'];
					}
					break;
				case 'Kadence Pro - Premium addon for the Kadence Theme':
					if ( ! empty( $settings['theme_pro_name'] ) ) {
						$translated_text = $settings['theme_pro_name'];
					}
					break;
			}
		}
		return $translated_text;
	}
	/**
	 * Replace theme details on network themes page.
	 *
	 * @param array $themes Themes.
	 * @return array
	 */
	public function replace_details_on_network_themes_page( $themes ) {
		$slug = 'kadence';

		if ( ! isset( $themes[ $slug ] ) ) {
			return $themes;
		}
		$settings = $this->get_settings();
		$themes[ $slug ] = $this->replace_theme_details( $themes[ $slug ], $settings );
		// Check for child themes.
		if ( ! empty( $settings['theme_name'] ) ) {
			foreach ( $themes as $theme_slug => $theme_data ) {
				if ( isset( $theme_data['parent'] ) && 'Kadence' == $theme_data['parent'] ) {
					$themes[ $theme_slug ]['parent'] = $settings['theme_name'];
				}
			}
		}

		return $themes;
	}
	/**
	 * Replace theme details on themes page.
	 *
	 * @param array $themes Themes.
	 * @return array
	 */
	public function replace_details_on_themes_page( $themes ) {
		$slug = 'kadence';

		if ( ! isset( $themes[ $slug ] ) ) {
			return $themes;
		}

		$settings = $this->get_settings();
		$themes[ $slug ] = $this->replace_theme_details( $themes[ $slug ], $settings );
		// Check for child themes.
		if ( ! empty( $settings['theme_name'] ) ) {
			foreach ( $themes as $theme_slug => $theme_data ) {
				if ( isset( $theme_data['parent'] ) && 'Kadence' == $theme_data['parent'] ) {
					$themes[ $theme_slug ]['parent'] = $settings['theme_name'];
				}
			}
		}

		return $themes;
	}
	/**
	 * Replace theme details of the theme data array.
	 *
	 * @param array $theme_data the themes data.
	 * @return array
	 */
	public function replace_theme_details( $theme_data, $settings ) {
		$mapping = array(
			'name'         => 'theme_name',
			'description'  => 'theme_desc',
			'author'       => 'agency_name',
			'authorUri'    => 'agency_url',
			'authorAndUri' => 'agency_name',
			'screenshot'   => 'theme_image',
		);
		foreach ( $mapping as $key => $setting_key ) {
			if ( isset( $settings[ $setting_key ] ) ) {
				if ( 'authorAndUri' === $key ) {
					$theme_data[ $key ] = '<a href="' . ( ! empty( $settings['agency_url'] ) ? esc_url( $settings['agency_url'] ) : '#' ) . '">' . esc_html( $settings[ $setting_key ] ) . '</a>';
				} else if ( 'theme_image' === $setting_key ) {
					$theme_data[ $key ] = array( $settings[ $setting_key ] );
				} else {
					$theme_data[ $key ] = $settings[ $setting_key ];
				}
			}
		}
		if ( ! empty( $theme_data['update'] ) ) {
			if ( ! empty( $settings['theme_name'] ) ) {
				$theme_data['update'] = str_replace( 'Kadence', $settings['theme_name'], $theme_data['update'] );
			}
			if ( ! empty( $settings['agency_url'] ) ) {
				$theme_data['update'] = str_replace(
					'https://wordpress.org/themes/kadence/?TB_iframe=true&#038;width=1024&#038;height=800',
					add_query_arg(
						array(
							'TB_iframe' => true,
							'width'     => '1024',
							'hight'     => '800',
						),
						$settings['agency_url']
					),
					$theme_data['update']
				);
			}
		}
		return $theme_data;
	}
	/**
	 * Replace "WordPress %1$s running %2$s theme." text on Dashboard page.
	 *
	 * @param string $content
	 */
	public function replace_details_on_dashboard_status( $content ) {
		$settings = $this->get_settings();

		if ( ! empty( $settings['theme_name'] ) ) {
			$content = str_replace( '%2$s', '<a href="themes.php">' . $settings['theme_name'] . '</a>', $content );
		}

		return $content;
	}
	/**
	 * Replace theme details on themes page.
	 *
	 */
	public function theme_update_update_page_scripts() {
		global $pagenow;

		if ( 'update-core.php' !== $pagenow ) {
			return;
		}
		$settings = $this->get_settings();
		$script = '';
		if ( ! empty( $settings['theme_image'] ) ) {
			$image = $settings['theme_image'];
			$script .= "document.querySelectorAll(
				'#update-themes-table .plugin-title .updates-table-screenshot[src*=\"kadence/screenshot\"]'
			).forEach(function(theme) {
				theme.src = '$image';
			});";
		}
		if ( ! empty( $settings['theme_name'] ) ) {
			$name = $settings['theme_name'];
			$script .= "document.querySelectorAll('#update-themes-table .plugin-title strong')
				.forEach(function(plugin) {
					if (plugin.innerText === 'Kadence') {
						plugin.innerText = '$name';
					}
				});";
		}
		if ( ! empty( $script ) ) {
			wp_add_inline_script( 'updates', $script );
		}
	}
	/**
	 * Replace theme details on updates page.
	 *
	* @param object $transient Theme details.
	 */
	public function replace_theme_details_on_updates_page( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}
		$settings = $this->get_settings();
		$theme_file = 'kadence';
		if ( isset( $transient->response[ $theme_file ] ) ) {
			if ( ! empty( $settings['theme_image'] ) ) {
				// $transient->response[ $theme_file ]->icons = array( '2x' => $settings['theme_image'], '1x' => $settings['theme_image'] );
			}
			if ( ! empty( $settings['theme_name'] ) ) {
				$transient->response[ $theme_file ]['theme'] = $settings['theme_name'];
			}
		}
		return $transient;
	}
	/**
	 * Replace plugin details on updates page.
	 *
	 * @param object $transient Plugin details.
	 */
	public function replace_plugin_details_on_updates_page( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}
		$settings = $this->get_settings();
		$blocks_file = 'kadence-blocks/kadence-blocks.php';
		if ( isset( $transient->response[ $blocks_file ] ) ) {
			if ( ! empty( $settings['blocks_image'] ) ) {
				$transient->response[ $blocks_file ]->icons = array( '2x' => $settings['blocks_image'], '1x' => $settings['blocks_image'] );
			}
			if ( ! empty( $settings['blocks_name'] ) ) {
				$transient->response[ $blocks_file ]->plugin_name = $settings['blocks_name'];
			}
		}
		$blocks_pro_file = 'kadence-blocks-pro/kadence-blocks-pro.php';
		if ( isset( $transient->response[ $blocks_pro_file ] ) ) {
			if ( ! empty( $settings['blocks_pro_image'] ) ) {
				$transient->response[ $blocks_pro_file ]->icons = array( '2x' => $settings['blocks_pro_image'], '1x' => $settings['blocks_pro_image'] );
			}
			if ( ! empty( $settings['blocks_pro_name'] ) ) {
				$transient->response[ $blocks_pro_file ]->plugin_name = $settings['blocks_pro_name'];
			}
		}
		$white_file = 'kadence-white-label/kadence-white-label.php';
		if ( isset( $transient->response[ $white_file ] ) ) {
			if ( ! empty( $settings['plugin_image'] ) ) {
				$transient->response[ $white_file ]->icons = array( '2x' => $settings['plugin_image'], '1x' => $settings['plugin_image'] );
			}
			if ( ! empty( $settings['plugin_name'] ) ) {
				$transient->response[ $white_file ]->plugin_name = $settings['plugin_name'];
			}
		}
		$pro_file = 'kadence-pro/kadence-pro.php';
		if ( isset( $transient->response[ $pro_file ] ) ) {
			if ( ! empty( $settings['theme_pro_image'] ) ) {
				$transient->response[ $pro_file ]->icons = array( '2x' => $settings['theme_pro_image'], '1x' => $settings['theme_pro_image'] );
			}
			if ( ! empty( $settings['theme_pro_name'] ) ) {
				$transient->response[ $pro_file ]->plugin_name = $settings['theme_pro_name'];
			}
		}

		return $transient;
	}
	/**
	 * Replace theme details on API requests.
	 *
	 * @param object $result Theme details.
	 * @param string $action Action.
	 * @param array $args Args.
	 */
	public function replace_theme_details_on_api_requests( $result, $action, $args ) {
		if ( ! isset( $result->slug ) ) {
			return $result;
		}
		return $result;
	}
	/**
	 * Replace plugin details on updates page.
	 *
	 * @param object $result Plugin details.
	 * @param string $action Action.
	 * @param array $args Args.
	 */
	public function replace_plugin_details_on_api_requests( $result, $action, $args ) {
		if ( ! isset( $result->slug ) ) {
			return $result;
		}
		$settings = $this->get_settings();
		if ( 'kadence-blocks' === $result->slug ) {
			$result->name            = ! empty( $settings['blocks_name'] ) ? $settings['blocks_name'] : $result->name;
			$result->author          = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $result->author;
			$result->author_profile  = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->author_profile;
			$result->contributors    = array();
			$result->sections        = array( 'description' => ( ! empty( $settings['blocks_desc'] ) ? $settings['blocks_desc'] : '' ) );
			$result->banners         = array();
			$result->screenshots     = array();
			$result->upgrade_notice  = array();
			$result->external        = 'extra';
			$result->active_installs = 1;
			$result->rating          = '';
			$result->ratings         = array();
			$result->donate_link     = '';
			$result->versions        = array();
			$result->preview_link    = '';
			$result->support_url     = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->support_url;
			$result->homepage        = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->homepage;
		} elseif ( 'kadence-theme-pro' === $result->slug ) {
			$result->name            = ! empty( $settings['theme_pro_name'] ) ? $settings['theme_pro_name'] : $result->name;
			$result->author          = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $result->author;
			$result->author_profile  = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->author_profile;
			$result->contributors    = array();
			$result->sections        = array( 'description' => ( ! empty( $settings['theme_pro_desc'] ) ? $settings['theme_pro_desc'] : '' ) );
			$result->banners         = array();
			$result->screenshots     = array();
			$result->upgrade_notice  = array();
			$result->external        = 'extra';
			$result->active_installs = 1;
			$result->rating          = '';
			$result->ratings         = array();
			$result->donate_link     = '';
			$result->versions        = array();
			$result->preview_link    = '';
			$result->support_url     = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->support_url;
			$result->homepage        = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->homepage;
		} elseif ( 'kadence-blocks-pro' === $result->slug ) {
			$result->name            = ! empty( $settings['blocks_pro_name'] ) ? $settings['blocks_pro_name'] : $result->name;
			$result->author          = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $result->author;
			$result->author_profile  = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->author_profile;
			$result->contributors    = array();
			$result->sections        = array( 'description' => ( ! empty( $settings['blocks_pro_desc'] ) ? $settings['blocks_pro_desc'] : '' ) );
			$result->banners         = array();
			$result->screenshots     = array();
			$result->upgrade_notice  = array();
			$result->external        = 'extra';
			$result->active_installs = 1;
			$result->rating          = '';
			$result->ratings         = array();
			$result->donate_link     = '';
			$result->versions        = array();
			$result->preview_link    = '';
			$result->support_url     = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->support_url;
			$result->homepage        = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->homepage;
		} elseif ( 'kadence-white-label' === $result->slug ) {
			$result->name            = ! empty( $settings['plugin_name'] ) ? $settings['plugin_name'] : $result->name;
			$result->author          = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $result->author;
			$result->author_profile  = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->author_profile;
			$result->contributors    = array();
			$result->sections        = array( 'description' => ( ! empty( $settings['plugin_desc'] ) ? $settings['plugin_desc'] : '' ) );
			$result->banners         = array();
			$result->screenshots     = array();
			$result->upgrade_notice  = array();
			$result->external        = 'extra';
			$result->active_installs = 1;
			$result->rating          = '';
			$result->ratings         = array();
			$result->donate_link     = '';
			$result->versions        = array();
			$result->preview_link    = '';
			$result->support_url     = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->support_url;
			$result->homepage        = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $result->homepage;
		}
		return $result;
	}
	/**
	 * Replace blocks brand name.
	 *
	 * @param string $brand_name Brand name.
	 * @return string
	 */
	public function replace_blocks_brand_name( $brand_name ) {
		$settings = $this->get_settings();
		if ( ! empty( $settings['blocks_name'] ) ) {
			$brand_name = $settings['blocks_name'];
		}
		return $brand_name;
	}
	/**
	 * Replace plugin details on plugins page.
	 *
	 * @param array $plugins Plugins.
	 * @return array
	 */
	public function replace_plugin_details_on_plugins_page( $plugins ) {
		$settings = $this->get_settings();
		$blocks_file = 'kadence-blocks/kadence-blocks.php';
		if ( isset( $plugins[ $blocks_file ] ) ) {
			$plugins[ $blocks_file ]['Name']        = ! empty( $settings['blocks_name'] ) ? $settings['blocks_name'] : $plugins[ $blocks_file ]['Name'];
			$plugins[ $blocks_file ]['Title']       = ! empty( $settings['blocks_name'] ) ? $settings['blocks_name'] : $plugins[ $blocks_file ]['Title'];
			$plugins[ $blocks_file ]['Description'] = ! empty( $settings['blocks_desc'] ) ? $settings['blocks_desc'] : $plugins[ $blocks_file ]['Description'];
			$plugins[ $blocks_file ]['Author']      = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $plugins[ $blocks_file ]['Author'];
			$plugins[ $blocks_file ]['AuthorName']  = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $plugins[ $blocks_file ]['AuthorName'];
			$plugins[ $blocks_file ]['AuthorURI']   = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $plugins[ $blocks_file ]['AuthorURI'];
			$plugins[ $blocks_file ]['PluginURI']   = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $plugins[ $blocks_file ]['PluginURI'];
			// $plugins[ $blocks_file ]['Icon']        = ! empty( $settings['blocks_image'] ) ? $settings['blocks_image'] : $plugins[ $blocks_file ]['Icon'];
		}
		$blocks_pro_file = 'kadence-blocks-pro/kadence-blocks-pro.php';
		if ( isset( $plugins[ $blocks_pro_file ] ) ) {
			$plugins[ $blocks_pro_file ]['Name']        = ! empty( $settings['blocks_pro_name'] ) ? $settings['blocks_pro_name'] : $plugins[ $blocks_pro_file ]['Name'];
			$plugins[ $blocks_pro_file ]['Title']       = ! empty( $settings['blocks_pro_name'] ) ? $settings['blocks_pro_name'] : $plugins[ $blocks_pro_file ]['Title'];
			$plugins[ $blocks_pro_file ]['Description'] = ! empty( $settings['blocks_pro_desc'] ) ? $settings['blocks_pro_desc'] : $plugins[ $blocks_pro_file ]['Description'];
			$plugins[ $blocks_pro_file ]['Author']      = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $plugins[ $blocks_pro_file ]['Author'];
			$plugins[ $blocks_pro_file ]['AuthorName']  = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $plugins[ $blocks_pro_file ]['AuthorName'];
			$plugins[ $blocks_pro_file ]['AuthorURI']   = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $plugins[ $blocks_pro_file ]['AuthorURI'];
			$plugins[ $blocks_pro_file ]['PluginURI']   = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $plugins[ $blocks_pro_file ]['PluginURI'];
			// $plugins[ $blocks_pro_file ]['Icon']        = ! empty( $settings['blocks_pro_image'] ) ? $settings['blocks_pro_image'] : $plugins[ $blocks_pro_file ]['Icon'];
		}
		// Theme Pro.
		$theme_pro_file = 'kadence-pro/kadence-pro.php';
		if ( isset( $plugins[ $theme_pro_file ] ) ) {
			$plugins[ $theme_pro_file ]['Name']        = ! empty( $settings['theme_pro_name'] ) ? $settings['theme_pro_name'] : $plugins[ $theme_pro_file ]['Name'];
			$plugins[ $theme_pro_file ]['Title']       = ! empty( $settings['theme_pro_name'] ) ? $settings['theme_pro_name'] : $plugins[ $theme_pro_file ]['Title'];
			$plugins[ $theme_pro_file ]['Description'] = ! empty( $settings['theme_pro_desc'] ) ? $settings['theme_pro_desc'] : $plugins[ $theme_pro_file ]['Description'];
			$plugins[ $theme_pro_file ]['Author']      = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $plugins[ $theme_pro_file ]['Author'];
			$plugins[ $theme_pro_file ]['AuthorName']  = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $plugins[ $theme_pro_file ]['AuthorName'];
			$plugins[ $theme_pro_file ]['AuthorURI']   = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $plugins[ $theme_pro_file ]['AuthorURI'];
			$plugins[ $theme_pro_file ]['PluginURI']   = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $plugins[ $theme_pro_file ]['PluginURI'];
			// $plugins[ $theme_pro_file ]['Icon']        = ! empty( $settings['theme_pro_image'] ) ? $settings['theme_pro_image'] : $plugins[ $theme_pro_file ]['Icon'];
		}
		$white_label_file = 'kadence-white-label/kadence-white-label.php';
		if ( isset( $plugins[ $white_label_file ] ) ) {
			$plugins[ $white_label_file ]['Name']        = ! empty( $settings['plugin_name'] ) ? $settings['plugin_name'] : $plugins[ $white_label_file ]['Name'];
			$plugins[ $white_label_file ]['Title']       = ! empty( $settings['plugin_name'] ) ? $settings['plugin_name'] : $plugins[ $white_label_file ]['Title'];
			$plugins[ $white_label_file ]['Description'] = ! empty( $settings['plugin_desc'] ) ? $settings['plugin_desc'] : $plugins[ $white_label_file ]['Description'];
			$plugins[ $white_label_file ]['Author']      = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $plugins[ $white_label_file ]['Author'];
			$plugins[ $white_label_file ]['AuthorName']  = ! empty( $settings['agency_name'] ) ? $settings['agency_name'] : $plugins[ $white_label_file ]['AuthorName'];
			$plugins[ $white_label_file ]['AuthorURI']   = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $plugins[ $white_label_file ]['AuthorURI'];
			$plugins[ $white_label_file ]['PluginURI']   = ! empty( $settings['agency_url'] ) ? $settings['agency_url'] : $plugins[ $white_label_file ]['PluginURI'];
			// $plugins[ $white_label_file ]['Icon']        = ! empty( $settings['plugin_image'] ) ? $settings['plugin_image'] : $plugins[ $white_label_file ]['Icon'];
		}
		return $plugins;
	}
}

new Plugin();
