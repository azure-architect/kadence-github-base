<?php
/**
 * The provider hooking Admin class methods to WordPress events.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */

namespace KadenceWP\Insights\Admin;

use Kadence_Settings_Engine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all Admin related functionality.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */
class Settings {
	const OPT_NAME = 'kadence_insights';

	/**
	 * Add sections to settings.
	 */
	public function add_sections() {
		if ( ! class_exists( 'Kadence_Settings_Engine' ) ) {
			return;
		}
		$args = array(
			'v2'                               => true,
			'opt_name'                         => self::OPT_NAME,
			'menu_icon'                        => $this->get_icon_svg(),
			'menu_title'                       => __( 'Settings', 'kadence-insights' ),
			'page_title'                       => __( 'Kadence Insights - A/B Testing', 'kadence-insights' ),
			'page_slug'                        => 'kadence-insights-settings',
			'page_permissions'                 => 'edit_pages',
			'page_parent'                      => 'kadence-insights',
			'menu_type'                        => 'submenu',
			'footer_credit'                    => '',
			'class'                            => '',
			'admin_bar'                        => false,
			'admin_bar_priority'               => 999,
			'admin_bar_icon'                   => '',
			'show_import_export'               => false,
			'version'                          => KADENCE_INSIGHTS_VERSION,
			'logo'                             => KADENCE_INSIGHTS_URL . 'assets/kadence-logo.png',
			'changelog'                        => KADENCE_INSIGHTS_PATH . 'changelog.txt',
			'license'                          => 'hidden-side-panel',
		);
		$args['tabs'] = array(
			'settings' => array(
				'id' => 'settings',
				'title' => __( 'Settings', 'kadence-insights' ),
			),
		);
		$args['started'] = array(
			'title' => __( 'Welcome to Kadence Insights', 'kadence-insights' ),
			'description' => __( 'No-code solution for A/B testing content on your site', 'kadence-insights' ),
			'video_url' => '',
			'link_url' => 'https://www.kadencewp.com/help-center/',
			'link_text' => __( 'View Knowledge Base', 'kadence-insights' ),
		);
		$args['sidebar'] = array(
			'facebook' => array(
				'title' => __( 'Web Creators Community', 'kadence-insights' ),
				'description' => __( 'Join our community of fellow kadence users creating effective websites! Share your site, ask a question and help others.', 'kadence-insights' ),
				'link' => 'https://www.facebook.com/groups/webcreatorcommunity',
				'link_text' => __( 'Join our Facebook Group', 'kadence-insights' ),
			),
			'docs' => array(
				'title' => __( 'Documentation', 'kadence-insights' ),
				'description' => __( 'Need help? We have a knowledge base full of articles to get you started.', 'kadence-insights' ),
				'link' => 'https://www.kadencewp.com/help-center/',
				'link_text' => __( 'Browse Docs', 'kadence-insights' ),
			),
			'support' => array(
				'title' => __( 'Support', 'kadence-insights' ),
				'description' => __( 'Have a question, we are happy to help! Get in touch with our support team.', 'kadence-insights' ),
				'link' => 'https://www.kadencewp.com/premium-support-tickets/',
				'link_text' => __( 'Submit a Ticket', 'kadence-insights' ),
			),
		);
		Kadence_Settings_Engine::set_args( self::OPT_NAME, $args );
		Kadence_Settings_Engine::set_section(
			self::OPT_NAME,
			array(
				'id'     => 'ko_general',
				'title'  => __( 'General', 'kadence-insights' ),
				'long_title'  => __( 'General Settings', 'kadence-insights' ),
				'desc'   => '',
				'fields' => array(
					array(
						'id'       => 'enable_analytics',
						'type'     => 'switch',
						'title'    => __( 'Enable Local Analytics.', 'kadence-insights' ),
						'help'     => __( 'This will keep a record of views and goal events.', 'kadence-insights' ),
						'default'  => 1,
					),
					array(
						'id'       => 'google_analytics',
						'type'     => 'switch',
						'title'    => __( 'Enable Google Analytics Events Tracking.', 'kadence-insights' ),
						'help'     => __( 'Optional, if you want ab events to trigger events in Google analytics. Note that Google analytics must be running on your site and building reports requires knowledge of Google analytics.', 'kadence-insights' ),
						'default'  => 0,
					),
				),
			)
		);
	}
	/**
	 * Add option page menu
	 */
	public function add_menu() {
		add_menu_page( __( 'Kadence Insights - A/B Testing', 'kadence-insights' ), __( 'Insights', 'kadence-insights' ), $this->settings_user_capabilities(), 'kadence-insights', null, $this->get_icon_svg() );
	}
	/**
	 * Allow settings visibility to be changed.
	 */
	public function settings_user_capabilities() {
		$cap = apply_filters( 'kadence_optimize_admin_settings_capability', 'edit_pages' );
		return $cap;
	}
	/**
	 * Returns a base64 URL for the SVG for use in the menu.
	 *
	 * @param  bool $base64 Whether or not to return base64-encoded SVG.
	 * @return string
	 */
	private function get_icon_svg( $base64 = true ) {
		$svg = '<svg width="100%" height="100%" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M12.408,2.759c-0.146,-0.185 -0.233,-0.418 -0.233,-0.671c0,-0.599 0.486,-1.085 1.085,-1.085c0.599,-0 1.085,0.486 1.085,1.085c0,0.525 -0.374,0.963 -0.87,1.063l-1.65,4.768c0.122,0.175 0.194,0.389 0.194,0.619c-0,0.599 -0.486,1.085 -1.085,1.085c-0.599,-0 -1.085,-0.486 -1.085,-1.085c-0,-0.095 0.012,-0.187 0.035,-0.274l-1.197,-1.497l0.005,-0.007c-0.013,0.002 -0.027,0.003 -0.041,0.004l-1.503,2.446c0.103,0.166 0.162,0.361 0.162,0.571c0,0.599 -0.486,1.085 -1.085,1.085c-0.598,0 -1.085,-0.486 -1.085,-1.085c0,-0.565 0.434,-1.03 0.986,-1.08l1.513,-2.462c-0.097,-0.163 -0.153,-0.354 -0.153,-0.557c-0,-0.599 0.486,-1.085 1.085,-1.085c0.598,-0 1.085,0.486 1.085,1.085c-0,0.129 -0.023,0.253 -0.065,0.369l1.137,1.421c0.017,-0.003 0.034,-0.006 0.051,-0.008l1.629,-4.705Zm-6.183,6.374c0.358,0 0.648,0.291 0.648,0.648c0,0.358 -0.29,0.648 -0.648,0.648c-0.357,-0 -0.647,-0.29 -0.647,-0.648c-0,-0.357 0.29,-0.648 0.647,-0.648Zm4.709,-1.243c0.357,0 0.648,0.29 0.648,0.648c-0,0.357 -0.291,0.647 -0.648,0.647c-0.357,0 -0.648,-0.29 -0.648,-0.647c0,-0.358 0.291,-0.648 0.648,-0.648Zm-2.363,-2.856c0.357,0 0.647,0.29 0.647,0.648c0,0.357 -0.29,0.647 -0.647,0.647c-0.358,0 -0.648,-0.29 -0.648,-0.647c-0,-0.358 0.29,-0.648 0.648,-0.648Zm4.689,-3.594c0.358,0 0.648,0.29 0.648,0.648c-0,0.357 -0.29,0.647 -0.648,0.647c-0.357,0 -0.647,-0.29 -0.647,-0.647c-0,-0.358 0.29,-0.648 0.647,-0.648Z"/><path d="M2.207,3.209l-0.4,0.401l-0.804,-0.804l1.803,-1.803l0.005,0.005l0.005,-0.005l1.802,1.803l-0.804,0.804l-0.47,-0.471l0,9.482l9.482,-0l-0.436,-0.435l0.804,-0.804l1.803,1.802l-0.005,0.005l0.005,0.005l-1.803,1.803l-0.804,-0.804l0.436,-0.435l-10.619,-0l-0,-10.549Z"/></svg>';
		if ( $base64 ) {
			return 'data:image/svg+xml;base64,' . base64_encode( $svg );
		}

		return $svg;
	}
}
