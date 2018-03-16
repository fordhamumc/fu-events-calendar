<?php
/**
 * Customize the List view
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__List' ) ) {
  class FU__Events__List {


    /**
     * Class constructor
     * @since 2.0.3
     *
     */

    public function __construct() {
      add_filter( 'tribe_events_after_the_title', array($this, 'add_submit_button') );
      add_filter( 'tribe-events-bar-filters', array($this, 'search_bar_order') );
      add_filter( 'tribe_events_recurrence_tooltip', array($this, 'remove_recurring_event_label') );
    }



    /**
     * Add community submit button to list view
     * @since 1.0
     *
     * @return string
     *
     */

    public function add_submit_button() {
      if ( class_exists('Tribe__Events__Community__Main') ) {
        $tec = tribe( 'community.main' );
        printf('<div class="tribe-bar-community-submit"><a href="/%s" class="button">%s</a></div>', $tec->getCommunityRewriteSlug() . '/' . $tec->rewriteSlugs['add'], esc_html__('Submit an Event', 'fu-events-calendar'));
      }
    }



    /**
     * Reorder search bar
     * @since 1.0
     *
     * @param array   $filters      the filters in the searchbar
     *
     * @return array
     *
     */

    function search_bar_order($filters) {
      $key = "tribe-bar-search";
      if ( array_key_exists($key, $filters) ) {
        $filters = array($key => $filters[$key]) + $filters;
      }
      return $filters;
    }



    /**
     * Reorder search bar
     * @since 1.0
     *
     * @param array   $html         the recurring event link
     *
     * @return array
     *
     */

    function remove_recurring_event_label( $html ) {
      return str_replace('Recurring ' . tribe_get_event_label_singular(), '', $html);
    }
  }
}