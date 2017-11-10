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


/**
 * Remove Country from Venue if United States
 *
 * @author Michael Foley
 *
 * @var array   $venue_details
 *
 * @return array
 *
 */

function fu_remove_united_states_venue($venue_details){
  $venue_details["address"] = str_replace("United States", "", $venue_details["address"]);
  return $venue_details;
}
add_filter( 'tribe_get_venue_details', 'fu_remove_united_states_venue' );


/**
 * Customize the description in the RSS feed for
 * iModules pages
 *
 * @author Michael Foley
 *
 * @var string   $excerpt
 *
 * @return string
 *
 */

function fu_custom_imods_event_feed($excerpt){
  global $post;

  $format = filter_input( INPUT_GET, 'imods', FILTER_SANITIZE_STRING );

  if ( is_object( $post ) && $post->post_type == TribeEvents::POSTTYPE && is_feed() && $format ) {
    $month = tribe_get_start_date($post->ID, false, 'M');
    $day = tribe_get_start_date($post->ID, false, 'j');
    $time = tribe_get_start_time();
    $time = str_replace( array(":00", "am", "pm"), array("", "a.m.", "p.m."), $time );
    $date = $start_date = tribe_get_start_date($post->ID, false, 'l, F j, Y');
    $end_date = tribe_get_end_date($post->ID, false, 'l, F j, Y');
    $title = tribe_get_event_link($post->ID, true);
    $location = tribe_get_venue();

    if ( !empty(tribe_get_address()) ) {
      $location .=  ', ' . tribe_get_venue_details()['address'];
    }

    if ($start_date !== $end_date) {
      $date = (substr($start_date, -4) === substr($end_date, -4)) ? substr($start_date, 0, -6) : $start_date;
      $date .= " &ndash; " . $end_date;
    }

    if ( $format === "2" || $format === "long" ){
      return <<<RSS
<h3>{$title}</h3>
<p>{$date}<br />{$location}</p>
RSS;

    } else {
      return <<<RSS
<div class="date-block">{$month} <span>{$day}</span></div>
<div class="event-block">
  <h3>{$title}</h3>
  <div class="event-time">{$time}</div>
</div>
RSS;
    }
  }

  return $excerpt;
}
add_filter( 'the_excerpt_rss', 'fu_custom_imods_event_feed' );


/**
 * Add event counts to event venue admin page
 *
 * @author Michael Foley
 *
 * @var array     $columns  the columns in the venue table
 *
 * @return array
 *
 */

function fu_venue_columns_head($columns) {
  $columns['fu_events']  = 'Events';
  return $columns;
}


/**
 * Add event counts to event venue admin page
 *
 * @author Michael Foley
 *
 * @var string    $columns_name   name of the column
 * @var string    $post_ID        the venue id
 *
 */

function fu_venue_columns_content($column_name, $post_ID) {

  if ($column_name == 'fu_events') {
    $args = array(
      'posts_per_page'    => -1,
      'post_type'         => TribeEvents::POSTTYPE,
      'post_status'       => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit'),
      'meta_key'          => '_EventVenueID',
      'meta_value'        => $post_ID
    );
    $q = new WP_Query($args);
    echo $q->post_count;
  }
}
add_filter('manage_tribe_venue_posts_columns', 'fu_venue_columns_head');
add_action('manage_tribe_venue_posts_custom_column', 'fu_venue_columns_content', 10, 2);