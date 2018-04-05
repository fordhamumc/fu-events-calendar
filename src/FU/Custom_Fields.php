<?php
/**
 * Rearrange the custom fields from the community form
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__Custom_Fields' ) ) {
  class FU__Events__Custom_Fields {


    /**
     * Class constructor
     * @since 2.0.3
     *
     */

    public function __construct() {
      add_filter('tribe_events_community_custom_fields', array($this, 'update_custom_fields'), 10, 2);
      add_filter('tribe_get_event_link', array($this, 'redirect_link'), 10, 2);
      add_filter('tribe_events_community_custom_field_value', array($this, 'custom_field_defaults'), 10, 3);
      add_action('tribe_events_community_section_after_website_row', array($this, 'update_website_fields') );
      add_action('tribe_events_parse_query', array($this, 'meta_query') );
    }



    /**
     * Remove custom field(s) from the default array
     * @since 1.0
     *
     * @param array $fields
     * @param mixed $labels
     *
     * @return array
     *
     */

    private function remove_fields_by_label( $fields, $labels ){
      foreach((array) $labels as $label) {
        foreach($fields as $subKey => $subArray){
          if($subArray['label'] == $label){
            unset($fields[$subKey]);
          }
        }
      }
      return $fields;
    }



    /**
     * Remove custom website fields
     * @since 1.0
     *
     * @param array $fields
     * @param mixed $labels
     *
     * @return array
     *
     */

    public function update_custom_fields($fields) {
      $labels = array('Redirect to Event Website', 'Registration Link', 'Attendee List Link');
      return $this->remove_fields_by_label($fields, $labels);
    }



    /**
     * Grab custom field(s) to use elsewhere
     * @since 1.0
     *
     * @param mixed $labels   the labels to look up
     *
     * @return array
     *
     */

    private function get_fields_by_label( $labels ){
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



    /**
     * Move custom fields to the website block
     * @since 1.0
     *
     * @return array
     *
     */

    public function update_website_fields() {
      $labels = array('Redirect to Event Website', 'Registration Link', 'Attendee List Link');
      foreach($this->get_fields_by_label($labels) as $field) {
        tribe_get_template_part( 'community/modules/custom-item', null, $field );
      }
    }



    /**
     * Redirect events to website url
     * @since 1.0
     *
     * @param string  $link
     * @param int     $post_id
     *
     * @return string
     *
     */

    public function redirect_link($link, $post_id) {
      $event_url = tribe_get_event_website_url($post_id);
      $redirect = !!Tribe__Events__Pro__Custom_Meta::get_custom_field_by_label('Redirect to Event Website', $post_id);
      if ($redirect && $event_url) {
        return preg_replace('#(http|https)://([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:/~+\#-]*[\w@?^=%&/~+\#-])?#', $event_url, $link);
      }
      return $link;
    }



    /**
     * Custom field defaults
     * @since 1.0
     *
     * @param mixed   $value    the current value for custom items
     * @param string  $name     custom field name
     * @param int     $event_id the event id (if available)
     *
     * @return mixed
     *
     */

    public function custom_field_defaults( $value, $name, $event_id ) {
      if ( empty( $event_id ) ) {
        $custom_audience = reset($this->get_fields_by_label('Audience'));
        if ( $custom_audience['name'] == $name) {
          return str_replace("\n", "|", $custom_audience['values']);
        }
      }
      return $value;
    }


    /**
     * Filter events by audience
     * @since 1.0
     *
     * @param mixed $q
     *
     */

    public function meta_query( $q ) {
      if ( !is_admin() && $q->tribe_is_event_query ) {
        if( isset($q->query_vars['post_type']) && $q->query_vars['post_type'] == TribeEvents::POSTTYPE ) {
          $audience = filter_input( INPUT_GET, 'f_audience', FILTER_SANITIZE_STRING );
          $meta_query = [];

          if( $audience ) {
            $meta_name = '_' . reset($this->get_fields_by_label('Audience'))['name'];
            $meta_query[] = [
              'key'   => $meta_name,
              'value' => $audience
            ];
            $meta_query_combined = array_merge( (array) $meta_query, (array) $q->get( 'meta_query' ) );
            $q->set( 'meta_query', $meta_query_combined );
          }
        }
      }
    }
  }
}