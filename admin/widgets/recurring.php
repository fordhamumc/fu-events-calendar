<?php

class Fu_Tec_Recurring_Widget extends WP_Widget {
  // php classnames and widget name/description added
  function __construct() {
    $widget_options = array(
      'classname' => 'tribe-events-recurring-widget',
      'description' => 'Adds the upcoming dates for a recurring event to a single event page.'
    );
    parent::__construct(
      'fu-tec-recurring-widget',
      'Events Calendar Recurring Events',
      $widget_options
    );
  }
  // create the widget output
  function widget( $args, $instance ) {
    if ( !is_single() || !tribe_is_event() || !function_exists( 'tribe_is_recurring_event' ) ) return;

    $title = $instance[ 'title' ];
    $count = intval($instance[ 'count' ]);
    $event_id = get_the_ID();
    $recurrence_parent_id = wp_get_post_parent_id( $event_id );

    if ( empty( $recurrence_parent_id ) ) {
      $recurrence_parent_id = $event_id;
    }

    if ( ! tribe_is_recurring_event( $event_id ) ) {
      $recurrence_parent_id = 0;
    }

    if ( empty( $recurrence_parent_id ) ) return;

    $recurrence_args = array(
      'post_parent'    => $recurrence_parent_id,
      'meta_key'       => '_EventStartDate',
      'orderby'        => 'meta_value',
      'posts_per_page' => $count,
      'fields'         => 'ids'
    );

    $all_event_ids_in_recurrence_series = tribe_get_events( $recurrence_args );

    echo $args['before_widget'];
    if ( !empty($title) ) echo $args['before_title'] . apply_filters( 'widget_title', $title ) . $args['after_title']; ?>

    <ul>

    <?php
    if ( count( $all_event_ids_in_recurrence_series ) <= 1 ) return;
      foreach( $all_event_ids_in_recurrence_series as $key => $value ) {
        if ( $event_id !== $value ) {
          printf( '<li><a href="%s">%s</a></li>', esc_url( tribe_get_event_link( $value ) ), tribe_get_start_date( $value, false, tribe_get_date_format() ) );
        }
      }
      printf( '<li class="tribe-view-all-events"><a href="%s">%s</a></li>', esc_url( tribe_all_occurences_link( $event_id, false ) ), esc_html__( 'View All', 'the-events-calendar' ) );
    ?>

    </ul>

    <?php
    echo $args['after_widget'];
  }
  function form( $instance ) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
    $count = ! empty( $instance['count'] ) ? $instance['count'] : '5'; ?>
    <p>
      <?php
      printf('<label for="%s">%s</label>', $this->get_field_id( 'title' ), esc_html__( 'Title: ', 'fu-events-calendar' ));
      printf('<input type="text" id="%s" name="%s" value="%s" class="widefat title" />', $this->get_field_id( 'title' ), $this->get_field_name( 'title' ), esc_attr( $title ));
      ?>
    </p>
    <p>
      <?php
      printf('<label for="%s">%s</label>', $this->get_field_id( 'count' ), esc_html__( 'Posts: ', 'fu-events-calendar' ));
      printf('<input type="text" id="%s" name="%s" value="%s" size="3" />', $this->get_field_id( 'count' ), $this->get_field_name( 'count' ), esc_attr( $count ));
      ?>
    </p>
    <p>This will only be visible on single event pages.</p>
  <?php }
  // Update database with new info
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    $instance[ 'count' ] = strip_tags( $new_instance[ 'count' ] );
    return $instance;
  }
}