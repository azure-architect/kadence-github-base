<?php

/**
 * Plugin Name: Performance Optimization
 * Description: Optimizes WordPress for better performance
 * Version: 1.0
 * Author: Locally Known Pro
 * Author URI: https://locallyknown.pro
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: performance-optimization    
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Add various performance optimizations
 */

// Remove WordPress version number
remove_action('wp_head', 'wp_generator');

// Remove wlwmanifest link
remove_action('wp_head', 'wlwmanifest_link');

// Remove RSD link
remove_action('wp_head', 'rsd_link');

// Remove shortlink
remove_action('wp_head', 'wp_shortlink_wp_head');

// Remove adjacent posts link
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

// Remove emoji scripts
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

// Remove REST API link
remove_action('wp_head', 'rest_output_link_wp_head');

// Disable self pingbacks
function client_template_disable_self_pingbacks(&$links)
{
  foreach ($links as $key => $link) {
    if (0 === strpos($link, get_option('home'))) {
      unset($links[$key]);
    }
  }
}
add_action('pre_ping', 'client_template_disable_self_pingbacks');

// Limit post revisions
if (!defined('WP_POST_REVISIONS')) {
  define('WP_POST_REVISIONS', 5);
}

// Disable dashboard widgets for non-admin users
function client_template_disable_dashboard_widgets_for_non_admins()
{
  if (!current_user_can('manage_options')) {
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
    remove_meta_box('dashboard_php_nag', 'dashboard', 'normal');
  }
}
add_action('wp_dashboard_setup', 'client_template_disable_dashboard_widgets_for_non_admins');

// Add defer to scripts
function client_template_defer_scripts($tag, $handle, $src)
{
  // Add scripts to defer here
  $defer_scripts = array(
    'jquery-migrate',
    'contact-form-7'
  );

  if (in_array($handle, $defer_scripts)) {
    return str_replace(' src', ' defer src', $tag);
  }

  return $tag;
}
add_filter('script_loader_tag', 'client_template_defer_scripts', 10, 3);

// Optimize database transients
function client_template_delete_expired_transients_on_save()
{
  global $wpdb;

  if (mt_rand(1, 10) !== 1) return;

  $time = time();
  $expired = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_%' AND option_value < {$time}");

  if (!empty($expired)) {
    foreach ($expired as $transient) {
      $name = str_replace('_transient_timeout_', '', $transient);
      delete_transient($name);
    }
  }
}
add_action('save_post', 'client_template_delete_expired_transients_on_save');
