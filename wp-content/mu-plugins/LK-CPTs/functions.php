<?php

/**
 * Plugin Name: Custom Post Types
 * Description: Registers custom post types used across client sites
 * Version: 1.0
 * Author: Locally Known Pro
 * Author URI: https://locallyknown.pro
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Register custom post types
 */
function client_template_register_post_types()
{
  // Team Members
  register_post_type('team', array(
    'labels' => array(
      'name'               => _x('Team Members', 'post type general name'),
      'singular_name'      => _x('Team Member', 'post type singular name'),
      'menu_name'          => _x('Team Members', 'admin menu'),
      'name_admin_bar'     => _x('Team Member', 'add new on admin bar'),
      'add_new'            => _x('Add New', 'team member'),
      'add_new_item'       => __('Add New Team Member'),
      'new_item'           => __('New Team Member'),
      'edit_item'          => __('Edit Team Member'),
      'view_item'          => __('View Team Member'),
      'all_items'          => __('All Team Members'),
      'search_items'       => __('Search Team Members'),
      'parent_item_colon'  => __('Parent Team Members:'),
      'not_found'          => __('No team members found.'),
      'not_found_in_trash' => __('No team members found in Trash.')
    ),
    'public'              => true,
    'has_archive'         => false,
    'menu_icon'           => 'dashicons-groups',
    'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
    'rewrite'             => array('slug' => 'team'),
    'show_in_rest'        => true,
    'menu_position'       => 5,
  ));

  // Testimonials
  register_post_type('testimonial', array(
    'labels' => array(
      'name'               => _x('Testimonials', 'post type general name'),
      'singular_name'      => _x('Testimonial', 'post type singular name'),
      'menu_name'          => _x('Testimonials', 'admin menu'),
      'name_admin_bar'     => _x('Testimonial', 'add new on admin bar'),
      'add_new'            => _x('Add New', 'testimonial'),
      'add_new_item'       => __('Add New Testimonial'),
      'new_item'           => __('New Testimonial'),
      'edit_item'          => __('Edit Testimonial'),
      'view_item'          => __('View Testimonial'),
      'all_items'          => __('All Testimonials'),
      'search_items'       => __('Search Testimonials'),
      'parent_item_colon'  => __('Parent Testimonials:'),
      'not_found'          => __('No testimonials found.'),
      'not_found_in_trash' => __('No testimonials found in Trash.')
    ),
    'public'              => true,
    'has_archive'         => false,
    'menu_icon'           => 'dashicons-format-quote',
    'supports'            => array('title', 'editor', 'thumbnail'),
    'rewrite'             => array('slug' => 'testimonials'),
    'show_in_rest'        => true,
    'menu_position'       => 5,
  ));

  // FAQs
  register_post_type('faq', array(
    'labels' => array(
      'name'               => _x('FAQs', 'post type general name'),
      'singular_name'      => _x('FAQ', 'post type singular name'),
      'menu_name'          => _x('FAQs', 'admin menu'),
      'name_admin_bar'     => _x('FAQ', 'add new on admin bar'),
      'add_new'            => _x('Add New', 'faq'),
      'add_new_item'       => __('Add New FAQ'),
      'new_item'           => __('New FAQ'),
      'edit_item'          => __('Edit FAQ'),
      'view_item'          => __('View FAQ'),
      'all_items'          => __('All FAQs'),
      'search_items'       => __('Search FAQs'),
      'parent_item_colon'  => __('Parent FAQs:'),
      'not_found'          => __('No faqs found.'),
      'not_found_in_trash' => __('No faqs found in Trash.')
    ),
    'public'              => true,
    'has_archive'         => false,
    'menu_icon'           => 'dashicons-editor-help',
    'supports'            => array('title', 'editor'),
    'rewrite'             => array('slug' => 'faqs'),
    'show_in_rest'        => true,
    'menu_position'       => 5,
  ));
}
add_action('init', 'client_template_register_post_types');

/**
 * Register custom taxonomies
 */
function client_template_register_taxonomies()
{
  // Team Member Departments
  register_taxonomy('department', 'team', array(
    'hierarchical'      => true,
    'labels'            => array(
      'name'              => _x('Departments', 'taxonomy general name'),
      'singular_name'     => _x('Department', 'taxonomy singular name'),
      'search_items'      => __('Search Departments'),
      'all_items'         => __('All Departments'),
      'parent_item'       => __('Parent Department'),
      'parent_item_colon' => __('Parent Department:'),
      'edit_item'         => __('Edit Department'),
      'update_item'       => __('Update Department'),
      'add_new_item'      => __('Add New Department'),
      'new_item_name'     => __('New Department Name'),
      'menu_name'         => __('Departments'),
    ),
    'show_ui'           => true,
    'show_admin_column' => true,
    'query_var'         => true,
    'rewrite'           => array('slug' => 'department'),
    'show_in_rest'      => true,
  ));

  // FAQ Categories
  register_taxonomy('faq_category', 'faq', array(
    'hierarchical'      => true,
    'labels'            => array(
      'name'              => _x('FAQ Categories', 'taxonomy general name'),
      'singular_name'     => _x('FAQ Category', 'taxonomy singular name'),
      'search_items'      => __('Search FAQ Categories'),
      'all_items'         => __('All FAQ Categories'),
      'parent_item'       => __('Parent FAQ Category'),
      'parent_item_colon' => __('Parent FAQ Category:'),
      'edit_item'         => __('Edit FAQ Category'),
      'update_item'       => __('Update FAQ Category'),
      'add_new_item'      => __('Add New FAQ Category'),
      'new_item_name'     => __('New FAQ Category Name'),
      'menu_name'         => __('FAQ Categories'),
    ),
    'show_ui'           => true,
    'show_admin_column' => true,
    'query_var'         => true,
    'rewrite'           => array('slug' => 'faq-category'),
    'show_in_rest'      => true,
  ));
}
add_action('init', 'client_template_register_taxonomies');
