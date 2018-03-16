<?php
/**
 * Customize the RSS feed
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__Feed' ) ) {
  class FU__Events__Feed {


    /**
     * Class constructor
     * @since 2.0.3
     *
     */

    public function __construct() {
      add_filter( 'the_excerpt_rss', array($this, 'custom_event_feed') );
    }



    /**
     * Customize the description in the RSS feed for
     * iModules pages
     * @since 1.0
     *
     * @param string   $excerpt
     *
     * @return string
     *
     */

    public function custom_event_feed($excerpt){
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
  }
}