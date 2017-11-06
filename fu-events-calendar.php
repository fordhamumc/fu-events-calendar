<?php
/*
Plugin Name: Fordham Events Calendar Edits
Plugin URI: http://news.fordham.edu
Description: Customizations for Modern Tribe's The Events Calendar
Version: 0.0.1
Author: Michael Foley
Author URI: http://michaeldfoley.com
License: GPLv2
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
 * Remove Custom Fields
 * Removes fields from custom fields array using the field label
 *
 * @author Michael Foley
 *
 * @var array $fields
 * @var mixed $labels
 *
 * @return array
 *
 */

function fu_remove_fields_by_label( $fields, $labels ){
  foreach((array) $labels as $label) {
    foreach($fields as $subKey => $subArray){
      if($subArray['label'] == $label){
        unset($fields[$subKey]);
      }
    }
  }
  return $fields;
}

function fu_update_custom_fields($fields) {
  $labels = array('Redirect to External Link', 'Registration Link', 'Attendee List Link');
  return fu_remove_fields_by_label($fields, $labels);
}
add_filter( 'tribe_events_community_custom_fields', 'fu_update_custom_fields', 10, 2);