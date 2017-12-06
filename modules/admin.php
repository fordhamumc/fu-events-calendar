<?php

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
 * @var string    $event_id       the venue id
 *
 */

function fu_venue_columns_content($column_name, $event_id) {

  if ($column_name == 'fu_events') {
    $args = array(
      'posts_per_page'    => -1,
      'post_type'         => TribeEvents::POSTTYPE,
      'post_status'       => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit'),
      'meta_key'          => '_EventVenueID',
      'meta_value'        => $event_id
    );
    $q = new WP_Query($args);
    echo $q->post_count;
  }
}
add_filter('manage_tribe_venue_posts_columns', 'fu_venue_columns_head');
add_action('manage_tribe_venue_posts_custom_column', 'fu_venue_columns_content', 10, 2);