<?php
/**
 * Kadence White Label Settings Class
 *
 * @package Kadence White Label
 */
namespace KadenceWP\KadenceWhiteLabel;

use \Kadence_Settings_Engine;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Kadence_White Label_Settings class
 */
class Settings {
	const OPT_NAME = 'kadence_white_label';

	/**
	 * Action on init.
	 */
	public function __construct() {
		require_once KADENCE_WHITE_PATH . 'inc/settings/load.php';
		// Need to load this with priority higher then 10 so class is loaded.
		add_action( 'after_setup_theme', array( $this, 'add_sections' ), 20 );
	}
	/**
	 * Add sections to settings.
	 */
	public function add_sections() {
		if ( ! class_exists( 'Kadence_Settings_Engine' ) ) {
			return;
		}
		$args = array(
			'opt_name'                         => self::OPT_NAME,
			'menu_icon'                        => '',
			'menu_title'                       => __( 'White Label', 'kadence-white-label' ),
			'page_title'                       => __( 'White Label Settings', 'kadence-white-label' ),
			'page_slug'                        => 'kadence-white-label-settings',
			'page_permissions'                 => 'manage_options',
			'menu_type'                        => apply_filters( 'kadence_white_label_menu_type', 'submenu' ),
			'page_parent'                      => 'themes.php',
			'page_priority'                    => null,
			'footer_credit'                    => '',
			'class'                            => '',
			'admin_bar'                        => false,
			'admin_bar_priority'               => 999,
			'admin_bar_icon'                   => '',
			'show_import_export'               => false,
			'version'                          => KADENCE_WHITE_VERSION,
			'logo'                             => apply_filters( 'kadence_white_label_dashboard_logo', KADENCE_WHITE_URL . 'assets/kadence-logo.png' ),
			'changelog'                        => KADENCE_WHITE_PATH . 'changelog.txt',
		);
		$args['tabs'] = array(
			'settings' => array(
				'id' => 'settings',
				'title' => __( 'Settings', 'kadence-white-label' ),
			),
			'changelog' => array(
				'id' => 'changelog',
				'title' => __( 'Changelog', 'kadence-white-label' ),
			),
		);
		$args['sidebar'] = array(
			'facebook' => array(
				'title' => __( 'Web Creators Community', 'kadence-white-label' ),
				'description' => __( 'Join our community of fellow kadence users creating effective websites! Share your site, ask a question and help others.', 'kadence-white-label' ),
				'link' => 'https://www.facebook.com/groups/webcreatorcommunity',
				'link_text' => __( 'Join our Facebook Group', 'kadence-white-label' ),
			),
			'docs' => array(
				'title' => __( 'Documentation', 'kadence-white-label' ),
				'description' => __( 'Need help? We have a knowledge base full of articles to get you started.', 'kadence-white-label' ),
				'link' => 'https://www.kadencewp.com/help-center/',
				'link_text' => __( 'Browse Docs', 'kadence-white-label' ),
			),
			'support' => array(
				'title' => __( 'Support', 'kadence-white-label' ),
				'description' => __( 'Have a question, we are happy to help! Get in touch with our support team.', 'kadence-white-label' ),
				'link' => 'https://www.kadencewp.com/premium-support-tickets/',
				'link_text' => __( 'Submit a Ticket', 'kadence-white-label' ),
			),
		);
		Kadence_Settings_Engine::set_args( self::OPT_NAME, $args );
		Kadence_Settings_Engine::set_section(
			self::OPT_NAME,
			array(
				'id'     => 'kw_general',
				'title'  => __( 'General', 'kadence-white-label' ),
				'long_title'  => __( 'General Details', 'kadence-white-label' ),
				'desc'   => '',
				'fields' => array(
					array(
						'id'      => 'enable_white_label',
						'type'    => 'switch',
						'title'   => __( 'Enable White Label', 'kadence-white-label' ),
						'default' => 1,
					),
					array(
						'id'    => 'agency_info',
						'type'  => 'title',
						'title' => __( 'Agency Details', 'kadence-white-label' ),
					),
					array(
						'id'    => 'agency_name',
						'type'  => 'text',
						'title' => __( 'Agency Name', 'kadence-white-label' ),
					),
					array(
						'id'    => 'agency_url',
						'type'  => 'text',
						'title' => __( 'Agency URL', 'kadence-white-label' ),
					),
					array(
						'id'    => 'agency_image',
						'type'  => 'image',
						'title' => __( 'Agency Image', 'kadence-white-label' ),
						'help'  => __( 'Suggested size: 300px by 300px.', 'kadence-white-label' ),
					),
					array(
						'id'    => 'plugin_info',
						'type'  => 'title',
						'title' => __( 'White Label Plugin Settings', 'kadence-white-label' ),
					),
					array(
						'id'    => 'plugin_name',
						'type'  => 'text',
						'title' => __( 'White Label Plugin Name', 'kadence-white-label' ),
					),
					array(
						'id'    => 'plugin_desc',
						'type'  => 'textarea',
						'title' => __( 'White Label Plugin Description', 'kadence-white-label' ),
					),
					array(
						'id'    => 'plugin_image',
						'type'  => 'image',
						'title' => __( 'White Label Plugin Icon Image', 'kadence-white-label' ),
						'help'  => __( 'This is the icon that will show in the plugin update list, suggested size 256px by 256px', 'kadence-white-label' ),
					),
				),
			)
		);
		Kadence_Settings_Engine::set_section(
			self::OPT_NAME,
			array(
				'id'     => 'kw_theme',
				'title'  => __( 'Theme Settings', 'kadence-white-label' ),
				'long_title'  => __( 'Theme Settings', 'kadence-white-label' ),
				'desc'   => '',
				'fields' => array(
					array(
						'id'    => 'theme_name',
						'type'  => 'text',
						'title' => __( 'Theme Name', 'kadence-white-label' ),
					),
					array(
						'id'    => 'theme_name_short',
						'type'  => 'text',
						'title' => __( 'Theme Admin Settings Page', 'kadence-white-label' ),
						'help'  => __( 'Suggested 18 characters or less in length.', 'kadence-white-label' ),
					),
					array(
						'id'    => 'theme_desc',
						'type'  => 'textarea',
						'title' => __( 'Theme Description', 'kadence-white-label' ),
					),
					array(
						'id'    => 'theme_image',
						'type'  => 'image',
						'title' => __( 'Theme Screenshot Image', 'kadence-white-label' ),
						'help'  => __( 'Suggested size: 1200px x 900px.', 'kadence-white-label' ),
					),
					array(
						'id'      => 'hide_theme_page',
						'type'    => 'switch',
						'title'   => __( 'Hide Theme Page Extras', 'kadence-white-label' ),
						'help'    => __( 'This allows you to hide the getting started, changelog, starter templates tabs and sidebar on the  "Kadence" page under the appearance nav.', 'kadence-white-label' ),
						'default' => 1,
					),
					array(
						'id'    => 'theme_pro_info',
						'type'  => 'title',
						'title' => __( 'Kadence Theme Pro Addon', 'kadence-white-label' ),
					),
					array(
						'id'    => 'theme_pro_name',
						'type'  => 'text',
						'title' => __( 'Kadence Theme Pro Addon Name', 'kadence-white-label' ),
					),
					array(
						'id'    => 'theme_pro_desc',
						'type'  => 'textarea',
						'title' => __( 'Kadence Theme Pro Addon Description', 'kadence-white-label' ),
					),
					array(
						'id'    => 'theme_pro_image',
						'type'  => 'image',
						'title' => __( 'Kadence Theme Pro Addon Icon Image', 'kadence-white-label' ),
						'help'  => __( 'This is the icon that will show in the plugin update list, suggested size 256px by 256px', 'kadence-white-label' ),
					),
				),
			)
		);
		Kadence_Settings_Engine::set_section(
			self::OPT_NAME,
			array(
				'id'     => 'kw_blocks',
				'title'  => __( 'Blocks Settings', 'kadence-white-label' ),
				'long_title'  => __( 'Blocks Settings', 'kadence-white-label' ),
				'desc'   => '',
				'fields' => array(
					array(
						'id'    => 'blocks_name',
						'type'  => 'text',
						'title' => __( 'Kadence Blocks Name', 'kadence-white-label' ),
					),
					array(
						'id'    => 'blocks_desc',
						'type'  => 'textarea',
						'title' => __( 'Kadence Blocks Description', 'kadence-white-label' ),
					),
					array(
						'id'    => 'blocks_image',
						'type'  => 'image',
						'title' => __( 'Kadence Blocks Icon Image', 'kadence-white-label' ),
						'help'  => __( 'This is the icon that will show in the plugin update list, suggested size 256px by 256px', 'kadence-white-label' ),
					),
					array(
						'id'    => 'blocks_menu_name',
						'type'  => 'text',
						'title' => __( 'Blocks admin menu name', 'kadence-white-label' ),
						'help'  => __( 'Suggested 12 characters or less in length.', 'kadence-white-label' ),
					),
					array(
						'id' => 'blocks_icon',
						'type' => 'image_select',
						'title' => __( 'Choose a menu icon', 'kadence-woo-extras' ),
						'options' => array(
							array(
								'value' => 'cube',
								'alt' => 'Cube',
								'img' => KADENCE_WHITE_URL . 'assets/icons/cube.png',
							),
							array(
								'value' => 'cog',
								'alt' => __( 'Cog', 'kadence-white-label' ),
								'img' => KADENCE_WHITE_URL . 'assets/icons/cog.png',
							),
							array(
								'value' => 'equalizer',
								'alt' => __( 'Equalizer', 'kadence-white-label' ),
								'img' => KADENCE_WHITE_URL . 'assets/icons/equalizer.png',
							),
							array(
								'value' => 'pencil-paper',
								'alt' =>__( 'Pencil Paper', 'kadence-white-label' ),
								'img' => KADENCE_WHITE_URL . 'assets/icons/pencil-paper.png',
							),
							array(
								'value' => 'pencil-ruler',
								'alt' => __( 'Pencil Ruler', 'kadence-white-label' ),
								'img' => KADENCE_WHITE_URL . 'assets/icons/pencil-ruler.png',
							),
							array(
								'value' => 'spreadsheet',
								'alt' => __( 'Spreadsheet', 'kadence-white-label' ),
								'img' => KADENCE_WHITE_URL . 'assets/icons/spreadsheet.png',
							),
							array(
								'value' => 'custom',
								'alt' => __( 'Custom', 'kadence-white-label' ),
								'img' => KADENCE_WHITE_URL . 'assets/icons/custom.png',
							),
						),
						'default' => 'cube',
					),
					array(
						'id'    => 'blocks_icon_svg',
						'type'  => 'textarea',
						'title' => __( 'Paste a custom svg icon code', 'kadence-white-label' ),
						'help'  => __( 'Make sure the code starts with svg and has fill="currentColor"', 'kadence-white-label' ),
						'required' => array( 'blocks_icon', '=', 'custom' ),
					),
					array(
						'id'    => 'blocks_pro_info',
						'type'  => 'title',
						'title' => __( 'Kadence Blocks Pro', 'kadence-white-label' ),
					),
					array(
						'id'    => 'blocks_pro_name',
						'type'  => 'text',
						'title' => __( 'Kadence Blocks Pro Name', 'kadence-white-label' ),
					),
					array(
						'id'    => 'blocks_pro_desc',
						'type'  => 'textarea',
						'title' => __( 'Kadence Blocks Pro Description', 'kadence-white-label' ),
					),
					array(
						'id'    => 'blocks_pro_image',
						'type'  => 'image',
						'title' => __( 'Kadence Blocks Pro Icon Image', 'kadence-white-label' ),
						'help'  => __( 'This is the icon that will show in the plugin update list, suggested size 256px by 256px', 'kadence-white-label' ),
					),
				),
			)
		);
		Kadence_Settings_Engine::set_section(
			self::OPT_NAME,
			array(
				'id'     => 'kw_extra',
				'title'  => __( 'Extra Settings', 'kadence-white-label' ),
				'long_title'  => __( 'Extra Settings', 'kadence-white-label' ),
				'desc'   => '',
				'fields' => array(
					array(
						'id'      => 'hide_white_label',
						'type'    => 'switch',
						'title'   => __( 'Hide White Label', 'kadence-white-label' ),
						'help'    => __( 'This hides the white label settings to all users even admins. To get it back you must use a defined constant in your wp-config.php file or go to the theme settings page and add &show_white_label=true to the end of the url.', 'kadence-white-label' ),
						'default' => 0,
					),
					array(
						'id'      => 'disable_ai',
						'type'    => 'switch',
						'title'   => __( 'Disable AI', 'kadence-white-label' ),
						'help'    => __( 'This hides the Kadence AI settings in Kadence Blocks from all users', 'kadence-white-label' ),
						'default' => 0,
					),
				),
			)
		);
	}
}
new Settings();
