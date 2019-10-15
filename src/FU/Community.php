<?php
/**
 * Customizations to the community page
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__Community' ) ) {
  class FU__Events__Community {


    /**
     * Class constructor
     * @since 2.0.3
     *
     */

    public function __construct() {
      add_action( 'tribe_community_before_event_page', array($this, 'custom_message'), 100 );
      add_filter( 'tribe_events_community_required_field', array($this, 'add_required_fields'), 10 );
      add_filter( 'tribe_community_form_field_label',  array($this, 'customize_audience_name'), 100, 2);
    }



    /**
     * Adds a custom message to the community form if referrer
     * is the old calendar.
     *
     */

    public function custom_message() {
      $tce = tribe( 'community.main' );
      if ( filter_input( INPUT_GET, 'acal', FILTER_VALIDATE_BOOLEAN ) ) {
        $tce->enqueueOutputMessage( __( 'We have upgraded to a new calendar. Please use the form below to submit your events.', 'fu-events-calendar' ), 'warn' );
      }

      if ( filter_input( INPUT_GET, 'success', FILTER_VALIDATE_BOOLEAN ) && !is_user_logged_in() ) {
        $tce->enqueueOutputMessage( __( 'Success! Please login to add an event.', 'fu-events-calendar' ), 'success' );
      }
    }



    /**
     * Adds Audience as a required field
     *
     * @since 2.0.6
     *
     */

    public function add_required_fields( $fields ) {
      if ( ! is_array( $fields ) ) {
          return $fields;
      }

      $fields[] = 'tribe_audience';
      return $fields;
    }



    /**
     * Renames required message to for Audience
     *
     * @since 2.0.6
     */

    public function customize_audience_name( $label, $field ) {
      if ($field === 'tribe_audience') {
        $label = __("Audience", 'fu-events-calendar');
      }

      return $label;
    }

  }
}