<?php

/**
 * Plugin Name: Resource Guide
 * Author: Emerson This
 * Author URI: https://emersonthis.com/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: resourceguide
 * Version: 0.0.3
 * Plugin Type: Piklist
 */

# If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * update it as you release new versions.
 */
define( 'RESOURCE_GUIDE_VERSION', '0.0.3' );

// More globals
$resourcePostType = 'rg_resource';
$prefix = 'rg_';
$textdomain = 'resourceguide'; // This should match style.css

function activate_resource_guide() {
  require_once plugin_dir_path( __FILE__) . 'Setup.class.php';
  $roles = new resourceguide\Setup();
  $roles->activate();
}
register_activation_hook( __FILE__, 'activate_resource_guide' );

function deactivate_resource_guide() {
  require_once plugin_dir_path( __FILE__ ) . 'Setup.class.php';
  $roles = new resourceguide\Setup();
  $roles->deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_resource_guide' );


# Check and warn if piklist library plugin isn't active
add_action('init', 'rg_init_function');
function rg_init_function(){
  if(is_admin()){
   include_once(__DIR__ . '/piklist-checker.php');
 
   if (!piklist_checker::check(__FILE__)){
     return;
   }
  }
}

# enqueue styles
add_action( 'wp_enqueue_scripts', 'rg_enqueue_styles' );
function rg_enqueue_styles() {
    wp_enqueue_style( 'rg-style', plugins_url( 'resource-guide.css', __FILE__ ),
        [],
        RESOURCE_GUIDE_VERSION
    );
}

require_once plugin_dir_path( __FILE__) . 'template-tags.php';

# register  shortcodes
function resource_list_func( $atts ){
  return rg_resource_filters() . rg_list_of_resources();
}
add_shortcode( 'resource-list', 'resource_list_func' );

# post types
add_filter('piklist_post_types', 'rg_post_types');

  function rg_post_types($post_types) {

  	global $resourcePostType;

    $post_types[$resourcePostType] = array(
      'labels' => piklist('post_type_labels', 'Resource')
      ,'title' => __('Enter the name of this resource')
      ,'menu_icon' => 'dashicons-sos'
      ,'page_icon' => 'dashicons-sos'
      ,'supports' => array(
        'title'
      )
      ,'public' => true
      ,'has_archive' => true
      ,'rewrite' => array(
        'slug' => 'resource'
      )
      ,'capability_type' => 'post'
      ,'capabilities' => array(
        'edit_posts' => 'edit_resources',
        'edit_others_posts' => 'edit_other_resources',
        'publish_posts' => 'publish_resources',
        'read_private_posts' => 'read_private_resources',
        'delete_post' => 'delete_resource',
        'edit_published_posts' => 'edit_published_resources'
      )
      ,'map_meta_cap' => true
    );
    return $post_types;
  }


# register taxonomies
add_filter('piklist_taxonomies', 'rg_resource_tax');
function rg_resource_tax($taxonomies) {

  global $resourcePostType;

    $taxonomies[] = array(
      'post_type' => $resourcePostType
      ,'name' => 'Location'
      ,'show_admin_column' => true
      ,'configuration' => array(
        'hierarchical' => true
        ,'labels' => piklist('taxonomy_labels', 'Location')
        ,'hide_meta_box' => false
        ,'show_ui' => true
        ,'query_var' => true
        ,'rewrite' => array(
          'slug' => 'resource-location'
        )
      )
    );

    $taxonomies[] = array(
      'post_type' => $resourcePostType
      ,'name' => 'Category'
      ,'show_admin_column' => true
      ,'configuration' => array(
        'hierarchical' => true
        ,'labels' => piklist('taxonomy_labels', 'Category')
        ,'hide_meta_box' => false
        ,'show_ui' => true
        ,'query_var' => true
        ,'rewrite' => array(
          'slug' => 'resource-category'
        )
      )
    );

  return $taxonomies;
}

# Single resource template
add_filter( 'single_template', 'rg_get_custom_post_type_template' );
 
function rg_get_custom_post_type_template( $single_template ) {
    global $post;
    global $resourcePostType;
 
    if ( $resourcePostType === $post->post_type ) {
        $single_template = dirname( __FILE__ ) . '/single-resource.php';
    }
 
    return $single_template;
}


# Printmode template
add_filter( 'template_include', 'rg_print_page_template', 99 );
function rg_print_page_template( $template ) {
    // @TODO 'rgprintmode' needs to match the param in the button.
    // @SEE: template-tags.php... 
    if ( $_GET['rgprintmode'] === '1'  ) {
        $new_template = plugin_dir_path( __FILE__ ) . 'templates/printmode.php';
        return $new_template ;
    }
    return $template;
}

# Notify when new resource is submitted
function rg_new_resource_notify( $postId ) {
  $newResource = get_post($postId); # get the pending resource / post
  $users = get_users( # get all users with appropriate roles to approve
    [ 
      'role_in'    => [
        'editor',
        'administrator'
      ]
    ]
  );
  # build the email notification
  $siteTitle = get_bloginfo('name');
  $siteUrl = get_bloginfo('url');
  $domain = parse_url($siteUrl)['host'];
  $subject = 'A new resource is pending';
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";    
  $headers .= 'From: '. $siteTitle .' <noreply@' . $domain . ">\r\n";
  $message = '<p>A new resource is pending on ' . $siteTitle . ': ';
  $message .= '<a href="' . get_edit_post_link( $newResource->ID ) . '">'. $newResource->post_title .'</a></p>';
  # send to email to each user
  foreach ($users as $user) {
    wp_mail( $user->user_email, $subject, $message, $headers );  
  }

}
add_action( 'pending_' . $resourcePostType, 'rg_new_resource_notify', 10, 2 );

