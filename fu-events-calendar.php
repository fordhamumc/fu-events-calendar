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



/**
 * Extract Custom Fields
 * Grab certain custom fields to use elsewhere
 *
 * @author Michael Foley
 *
 * @var mixed $labels   the labels to look up
 *
 * @return array
 *
 */

function fu_get_fields_by_label( $labels ){
  $fields = tribe_get_option( 'custom-fields' );
  if ( empty( $fields ) || ! is_array( $fields ) ) {
    return array();
  }
  $captured_fields = array();

  foreach((array) $labels as $label) {
    foreach($fields as $subKey => $subArray){
      if($subArray['label'] == $label){
        $captured_fields[$subKey] = $fields[$subKey];
      }
    }
  }
  return $captured_fields;
}

function fu_update_website_fields() {
  $labels = array('Redirect to External Link', 'Registration Link', 'Attendee List Link');
  foreach(fu_get_fields_by_label($labels) as $field) {
    tribe_get_template_part( 'community/modules/custom-item', null, $field );
  }
}
add_action( 'tribe_events_community_section_after_website_row', 'fu_update_website_fields');

function fu_redirect_link($link, $post_id) {
  $event_url = function_exists( 'tribe_get_event_website_url' ) ? tribe_get_event_website_url() : tribe_community_get_event_website_url();
  $redirect = !!Tribe__Events__Pro__Custom_Meta::get_custom_field_by_label('Redirect to External Link', $post_id);
  if ($redirect && $event_url) {
    return preg_replace('#(http|https)://([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:/~+\#-]*[\w@?^=%&/~+\#-])?#', $event_url, $link);
  }
  return $link;
}
add_filter('tribe_get_event_link', 'fu_redirect_link', 10, 2);



/**
 * Custom field defaults
 *
 * @author Michael Foley
 *
 * @var mixed   $value    the current value for custom items
 * @var string  $name     custom field name
 * @var int     $event_id the event id (if available)
 *
 * @return mixed
 *
 */

function fu_custom_field_defaults( $value, $name, $event_id ) {
  if ( empty( $event_id ) ) {
    $custom_audience = reset(fu_get_fields_by_label('Audience'));
    if ( $custom_audience['name'] == $name) {
      return str_replace("\n", "|", $custom_audience['values']);
    }
  }
  return $value;
}
add_filter('tribe_events_community_custom_field_value', 'fu_custom_field_defaults', 10, 3);


/**
 * Filter events by audience
 *
 * @author Michael Foley
 *
 * @var mixed $q
 *
 */

function fu_meta_query( $q ) {
  if ( !is_admin() && $q->tribe_is_event_query ) {
    if( isset($q->query_vars['post_type']) && $q->query_vars['post_type'] == TribeEvents::POSTTYPE ) {
      $audience = filter_input( INPUT_GET, 'f_audience', FILTER_SANITIZE_STRING );
      $meta_query = [];

      if( $audience ) {
        $meta_name = '_' . reset(fu_get_fields_by_label('Audience'))['name'];
        $meta_query[] = [
          'key'   => $meta_name,
          'value' => $audience
        ];
        $meta_query_combined = array_merge( (array) $meta_query, (array) $q->get( 'meta_query' ) );
        $q->set( 'meta_query', $meta_query_combined );
      }
    }
  }
};
add_action('tribe_events_parse_query', 'fu_meta_query');