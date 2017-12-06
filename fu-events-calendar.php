<?php
/*
Plugin Name: Fordham Events Calendar Edits
Plugin URI: http://news.fordham.edu
Description: Customizations for Modern Tribe's The Events Calendar
Version: 0.1.0
Author: Michael Foley
Author URI: http://michaeldfoley.com
License: MIT
Text Domain: fu-events-calendar
*/

if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

/**
 * Override Template Path
 * Adds plugin views folder to list of template paths
 *
 * @author Michael Foley
 *
 * @var string $file
 * @var string $template
 *
 * @return string
 *
 */

function fu_filter_template_paths ( $file, $template ) {
  $custom_file_path = plugin_dir_path( __FILE__ ) . 'views/' . $template;
  if ( !file_exists($custom_file_path) ) return $file;
  return $custom_file_path;
}
add_filter( 'tribe_events_template', 'fu_filter_template_paths', 10, 2 );


/**
 * Add structured data to list view
 *
 * @author Michael Foley
 *
 */

function fu_list_structured_data() {
  if ( !tribe_is_list_view() ) return;

  global $wp_query;
  Tribe__Events__JSON_LD__Event::instance()->markup( $wp_query->posts );
}
add_action( 'wp_head', 'fu_list_structured_data');


include( plugin_dir_path( __FILE__ ) . 'modules/custom-fields.php');
include( plugin_dir_path( __FILE__ ) . 'modules/feed.php');
include( plugin_dir_path( __FILE__ ) . 'modules/admin.php');
include( plugin_dir_path( __FILE__ ) . 'modules/display.php');
include( plugin_dir_path( __FILE__ ) . 'modules/search.php');
