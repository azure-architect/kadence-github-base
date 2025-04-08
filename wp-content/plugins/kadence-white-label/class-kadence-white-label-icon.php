<?php
/**
 * Kadence_White_Label Class
 *
 * @package Kadence White Label
 */

namespace KadenceWP\KadenceWhiteLabel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Kadence White Label Main Class
 */
class Icon {

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
	}
	/**
	 * Get an SVG Icon
	 *
	 * @param string $icon the icon name.
	 */
	public static function get_icon( $icon = 'cube' ) {
		$display_title = false;
		$output = '';
		switch ( $icon ) {
			case 'cube':
				$output .= '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" style="color:#f0f0f1" viewBox="0 0 32 32">
				<path d="M15 2l-15 6v14l15 8 15-8v-14l-15-6zM14 27.2l-12-6.4v-9.733l12 6.4v9.733zM2.594 9.117l12.406-4.963 12.406 4.963-12.406 6.617-12.406-6.617z"></path>
				</svg>';
				break;
			case 'equalizer':
				$output .= '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" style="color:#f0f0f1" viewBox="0 0 32 32">
				<path d="M30 2h-28c-1.1 0-2 0.9-2 2v24c0 1.1 0.9 2 2 2h28c1.1 0 2-0.9 2-2v-24c0-1.1-0.9-2-2-2zM30 27.996c-0.001 0.001-0.002 0.003-0.004 0.004h-27.993c-0.001-0.001-0.002-0.002-0.003-0.004v-23.993c0.001-0.001 0.002-0.002 0.004-0.004h27.993c0.001 0.001 0.003 0.002 0.004 0.004l-0 23.993zM13.5 22h-1.5v-16h-4v16h-1.5c-0.275 0-0.5 0.225-0.5 0.5v3c0 0.275 0.225 0.5 0.5 0.5h7c0.275 0 0.5-0.225 0.5-0.5v-3c0-0.275-0.225-0.5-0.5-0.5zM18.5 10h1.5v16h4v-16h1.5c0.275 0 0.5-0.225 0.5-0.5v-3c0-0.275-0.225-0.5-0.5-0.5h-7c-0.275 0-0.5 0.225-0.5 0.5v3c0 0.275 0.225 0.5 0.5 0.5z"></path>
				</svg>';
				break;
			case 'spreadsheet':
				$output .= '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="currentColor" style="color:#f0f0f1">
				<path d="M22 8h7.254c-0.157-0.252-0.345-0.531-0.572-0.841-0.694-0.947-1.662-2.053-2.724-3.116s-2.169-2.030-3.116-2.724c-0.31-0.227-0.589-0.416-0.841-0.572v7.254z"></path>
				<path d="M14 22h4v4h-4v-4z"></path>
				<path d="M20 22h4v4h-4v-4z"></path>
				<path d="M20 16h4v4h-4v-4z"></path>
				<path d="M14 16h4v4h-4v-4z"></path>
				<path d="M21 10c-0.552 0-1-0.448-1-1v-9h-15.5c-1.378 0-2.5 1.121-2.5 2.5v27c0 1.378 1.121 2.5 2.5 2.5h23c1.378 0 2.5-1.122 2.5-2.5v-19.5h-9zM26 28h-20v-14h20v14z"></path>
				<path d="M8 16h4v4h-4v-4z"></path>
				<path d="M8 22h4v4h-4v-4z"></path>
				</svg>';
				break;
			case 'pencil-paper':
				$output .= '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="currentColor" style="color:#f0f0f1">
				<path d="M24 17v11h-20v-20h11l4-4h-16c-1.65 0-3 1.35-3 3v22c0 1.65 1.35 3 3 3h22c1.65 0 3-1.35 3-3v-16l-4 4z"></path>
				<path d="M27 0l-19 19v5h5l19-19c0-3-2-5-5-5zM13 20l-1.5-1.5 15-15 1.5 1.5-15 15z"></path>
				</svg>';
				break;
			case 'cog':
				$output .= '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="currentColor" style="color:#f0f0f1">
				<path d="M29.181 19.070c-1.679-2.908-0.669-6.634 2.255-8.328l-3.145-5.447c-0.898 0.527-1.943 0.829-3.058 0.829-3.361 0-6.085-2.742-6.085-6.125h-6.289c0.008 1.044-0.252 2.103-0.811 3.070-1.679 2.908-5.411 3.897-8.339 2.211l-3.144 5.447c0.905 0.515 1.689 1.268 2.246 2.234 1.676 2.903 0.672 6.623-2.241 8.319l3.145 5.447c0.895-0.522 1.935-0.82 3.044-0.82 3.35 0 6.067 2.725 6.084 6.092h6.289c-0.003-1.034 0.259-2.080 0.811-3.038 1.676-2.903 5.399-3.894 8.325-2.219l3.145-5.447c-0.899-0.515-1.678-1.266-2.232-2.226zM16 22.479c-3.578 0-6.479-2.901-6.479-6.479s2.901-6.479 6.479-6.479c3.578 0 6.479 2.901 6.479 6.479s-2.901 6.479-6.479 6.479z"></path>
				</svg>';
				break;
			case 'pencil-ruler':
				$output .= '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" style="color:#f0f0f1">
				<path d="M23.888 19.113l-6.294-6.294 5.078-5.078c1.769-1.769 1.769-4.647 0-6.416-0.856-0.853-1.994-1.325-3.206-1.325s-2.35 0.472-3.206 1.328l-5.078 5.078-6.294-6.294c-0.194-0.194-0.513-0.194-0.706 0l-4.034 4.034c-0.194 0.194-0.194 0.513 0 0.706l6.294 6.294-4.147 4.147c-0.119 0.119-0.206 0.269-0.253 0.431l-2 7c-0.1 0.35-0.003 0.725 0.253 0.981 0.191 0.191 0.447 0.294 0.706 0.294 0.091 0 0.184-0.012 0.275-0.038l7-2c0.162-0.047 0.312-0.134 0.431-0.253l4.147-4.147 6.294 6.294c0.097 0.097 0.225 0.147 0.353 0.147s0.256-0.050 0.353-0.147l4.034-4.034c0.197-0.197 0.197-0.516 0-0.709zM16.059 4.353l0.794-0.794 3.584 3.584-0.794 0.794-3.584-3.584zM16.794 6.5l-10.494 10.491c-0.741-0.059-1.413-0.453-1.838-1.044l10.887-10.887 1.444 1.441zM4.397 17.297c0.484 0.366 1.044 0.594 1.634 0.672 0.078 0.594 0.306 1.15 0.672 1.634 0.159 0.209 0.341 0.403 0.544 0.572l-1.841 0.525-2.106-2.106 0.525-1.837c0.169 0.2 0.359 0.381 0.572 0.541zM7.009 17.7l10.491-10.494 1.441 1.441-10.891 10.887c-0.591-0.422-0.981-1.097-1.041-1.834zM19.466 2c0.678 0 1.313 0.262 1.794 0.744 0.987 0.987 0.987 2.597 0 3.584l-0.113 0.113-3.588-3.588 0.113-0.112c0.481-0.478 1.116-0.741 1.794-0.741zM1.206 4.5l3.328-3.328 0.794 0.794-1.534 1.534 0.706 0.706 1.534-1.534 1.294 1.294-1.534 1.534 0.706 0.706 1.534-1.534 1.294 1.294-1.534 1.534 0.706 0.706 1.534-1.534 0.441 0.441-3.328 3.328-5.941-5.941zM2.984 19.694l1.322 1.322-1.853 0.528 0.531-1.85zM19.5 22.794l-5.941-5.941 3.328-3.328 0.441 0.441-1.534 1.534 0.706 0.706 1.534-1.534 1.294 1.294-1.534 1.534 0.706 0.706 1.534-1.534 1.294 1.294-1.534 1.534 0.706 0.706 1.534-1.534 0.794 0.794-3.328 3.328z"></path>
				</svg>';
				break;
		}

		$output = apply_filters( 'kadence_white_label_svg_icon', $output, $icon );

		return $output;
	}
	/**
	 * Print an SVG Icon
	 *
	 * @param string $icon the icon name.
	 * @param string $icon_title the icon title for screen readers.
	 * @param bool   $base if the baseline class should be added.
	 */
	public function print_icon( $icon = 'search' ) {
		echo $this->get_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
