<?php

/**
 * Display a message if directed to the community form by the old one
 *
 * @author Michael Foley
 *
 */

function fu_custom_message(){
  if ( filter_input( INPUT_GET, 'acal', FILTER_VALIDATE_BOOLEAN ) ) {
    tribe( 'community.main' )->enqueueOutputMessage( __( 'We have upgraded to a new calendar. Please use the form below to submit your events.', 'fu-events-calendar' ) );
  }
}
add_action( 'tribe_events_community_template', 'fu_custom_message', 100 );
