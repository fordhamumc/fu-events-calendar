<?php

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
 * Updates time to Fordham's style
 *
 * @author Michael Foley
 *
 * @var string    $inner      the inner html date/time content
 * @var string    $event_id   the id of the current event in the loop
 * @var boolean   $time_only  is the input just the time
 *
 * @return string
 *
 */

function fu_update_time($inner, $event_id) {
  $inner = str_replace( array(':00', 'am', 'pm'), array('', 'a.m.', 'p.m.'), $inner );
  if ( tribe_get_start_date( $event_id, false, 'mdya' ) === tribe_get_end_date( $event_id, false, 'mdya' ) &&
    preg_match_all('# [ap]\.m\.#', $inner) > 1 ) {
    $inner = preg_replace('# [ap]\.m\.#', '', $inner, 1);
  }
  return $inner;
}
add_filter( 'tribe_events_event_schedule_details_inner', 'fu_update_time', 10, 2);
add_filter( 'fu_events_single_event_time_formatted', 'fu_update_time', 10, 2);