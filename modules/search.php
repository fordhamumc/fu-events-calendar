<?php

/**
 * Reorder search bar
 *
 * @author Michael Foley
 *
 * @var array    $filters      the filters in the searchbar
 *
 * @return array
 *
 */

function fu_search_bar_order($filters) {
  $key = "tribe-bar-search";
  if ( array_key_exists($key, $filters) ) {
    $filters = array($key => $filters[$key]) + $filters;
  }
  return $filters;
}
add_filter( 'tribe-events-bar-filters', 'fu_search_bar_order' );


/**
 * Reorder search bar
 *
 * @author Michael Foley
 *
 * @var array    $html        the recurring event link
 *
 * @return array
 *
 */

function fu_remove_recurring_event_label( $html ) {
  return str_replace('Recurring ' . tribe_get_event_label_singular(), '', $html);
}
add_filter('tribe_events_recurrence_tooltip', 'fu_remove_recurring_event_label');
