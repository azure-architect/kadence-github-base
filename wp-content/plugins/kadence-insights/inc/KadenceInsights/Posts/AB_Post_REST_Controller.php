<?php
/**
 * The provider for all rest controller related functionality.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */

namespace KadenceWP\Insights\Posts;

use WP_REST_Posts_Controller;
use WP_REST_Server;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all rest controller related functionality.
 *
 * @since 0.1.0
 *
 * @package KadenceWP\Insights
 */
class AB_Post_REST_Controller extends WP_REST_Posts_Controller {

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/auto-draft',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_auto_draft' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Creates an auto draft.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function create_auto_draft( $request ) {
		require_once ABSPATH . 'wp-admin/includes/post.php';

		unset( $_REQUEST['content'], $_REQUEST['excerpt'] );
		$post = get_default_post_to_edit( $this->post_type, true );

		$request->set_param( 'context', 'edit' );

		return $this->prepare_item_for_response( $post, $request );
	}
	/**
	 * Checks if a given request has access to create items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( get_post_type_object( $this->post_type )->cap->edit_posts ) ) {
			return new WP_Error( 'rest_cannot_view', __( 'You do not have permission to view these posts.', 'kadence-insights' ) );
		}

		return parent::get_items_permissions_check( $request );
	}
	/**
	 * Checks if a given request has access to create items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_post', $request['id'] ) ) {
			return new WP_Error( 'rest_cannot_view', __( 'You do not have permission to view these posts.', 'kadence-insights' ) );
		}

		return parent::get_item_permissions_check( $request );
	}
}
