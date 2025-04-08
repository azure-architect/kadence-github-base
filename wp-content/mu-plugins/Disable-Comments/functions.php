<?php

/**
 * Plugin Name: Disable Comments
 * Description: Disables comments functionality across the entire site
 * Version: 1.0
 * Author: Locally Known Pro
 * Author URI: https://locallyknown.pro
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: disable-comments
 * Domain Path: /languages  
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Disable comments functionality
 */
function client_template_disable_comments()
{
  // Close comments on the front-end
  add_filter('comments_open', '__return_false', 20, 2);
  add_filter('pings_open', '__return_false', 20, 2);

  // Hide existing comments
  add_filter('comments_array', '__return_empty_array', 10, 2);

  // Remove comments page in menu
  add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
  });

  // Remove comments from admin bar
  add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
  });

  // Remove support for comments and trackbacks from all post types
  add_action('init', function () {
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
      if (post_type_supports($post_type, 'comments')) {
        remove_post_type_support($post_type, 'comments');
        remove_post_type_support($post_type, 'trackbacks');
      }
    }
  });

  // Redirect any user trying to access comments page
  add_action('admin_init', function () {
    global $pagenow;

    if ($pagenow === 'edit-comments.php') {
      wp_redirect(admin_url());
      exit;
    }
  });
}

// Check if comments should be disabled (can be filtered by child theme)
if (apply_filters('client_template_disable_comments', true)) {
  client_template_disable_comments();
}
