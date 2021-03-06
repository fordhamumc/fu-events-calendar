<?php
/**
 * Customizations to the admin pages
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__Admin' ) ) {
  class FU__Events__Admin {


    /**
     * Class constructor
     * @since 2.0.3
     *
     */

    public function __construct() {
      add_action( 'init', array( $this, 'events_sidebar_menu') );
      add_filter('manage_tribe_venue_posts_columns', array($this, 'venue_columns_head') );
      add_action('manage_tribe_venue_posts_custom_column', array($this, 'venue_columns_content'), 10, 2);
    }



    /**
     * Add event counts header to event venue admin page
     * @since 1.0
     *
     * @param array     $columns  the columns in the venue table
     *
     * @return array
     *
     */

    public function venue_columns_head($columns) {
      $columns['FU_Timeout']  = 'Events';
      return $columns;
    }


    /**
     * Add event counts to event venue admin page
     * @since 1.0
     *
     * @param string    $column_name    name of the column
     * @param string    $event_id       the venue id
     *
     */

    public function venue_columns_content($column_name, $event_id) {
      if ($column_name == 'FU_Timeout') {
        $args = array(
          'posts_per_page'    => -1,
          'post_type'         => Tribe__Events__Main::POSTTYPE,
          'post_status'       => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit'),
          'meta_key'          => '_EventVenueID',
          'meta_value'        => $event_id
        );
        $q = new WP_Query($args);
        echo $q->post_count;
      }
    }


      /**
       * Add events sidebar menu area
       * @since 2.0.6
       *
       */

      public function events_sidebar_menu() {
          register_nav_menu( 'fu-events-sidebar', __('The Events Calendar Sidebar Menu', 'fu-events-calendar') );
      }
  }
}