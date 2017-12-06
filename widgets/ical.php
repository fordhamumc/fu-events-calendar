<?php

class Fu_Tec_iCal_Widget extends WP_Widget {
  // php classnames and widget name/description added
  function __construct() {
    $widget_options = array(
      'classname' => 'tribe-events-ical-widget',
      'description' => 'Adds link to download ical and google calendar event'
    );
    parent::__construct(
      'fu-tec-ical-widget',
      'Events Calendar iCal',
      $widget_options
    );
  }
  // create the widget output
  function widget( $args, $instance ) {
    if( !is_singular( Tribe__Events__Main::POSTTYPE ) ) return;

    $title = $instance[ 'title' ];

    echo $args['before_widget'];
    if ( !empty($title) ) echo $args['before_title'] . apply_filters( 'widget_title', $title ) . $args['after_title'];

    if ( is_single() ) {
      Tribe__Events__iCal::single_event_links();
    } else {
      $text  = apply_filters( 'tribe_events_ical_export_text', esc_html__( 'Export Events', 'the-events-calendar' ) );
      $title = esc_html__( 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps', 'the-events-calendar' );

      printf(
        '<a class="tribe-events-ical tribe-events-button" title="%1$s" href="%2$s">+ %3$s</a>',
        $title,
        esc_url( tribe_get_ical_link() ),
        $text
      );
    }

    echo $args['after_widget'];
  }
  function form( $instance ) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : ''; ?>
    <p>
      <?php
      printf('<label for="%s">%s</label>', $this->get_field_id( 'title' ), esc_html__( 'Title: ', 'fu-events-calendar' ));
      printf('<input type="text" id="%s" name="%s" value="%s" class="widefat title" />', $this->get_field_id( 'title' ), $this->get_field_name( 'title' ), esc_attr( $title ));
      ?>
    </p>
    <p>Google Calendar link will only be shown on single event pages.</p>
  <?php }
  // Update database with new info
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    return $instance;
  }
}