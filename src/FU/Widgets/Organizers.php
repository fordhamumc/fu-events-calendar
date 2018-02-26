<?php
/**
 * Organizers Widget
 *
 * Creates a widget that displays the organizer(s) for a single event
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}



class FU__Events__Widgets__Organizers extends WP_Widget {

  /**
   * Adds classname and description
   */

  function __construct() {
    $widget_options = array(
      'classname' => 'tribe-events-organizers-widget',
      'description' => 'Adds the event organizers to a single event page.'
    );
    parent::__construct(
      'fu-tec-organizers-widget',
      'Events Calendar Organizers',
      $widget_options
    );
  }



  /**
   * Creates the widget output
   */

  function widget( $args, $instance ) {
    if ( !is_single() || !tribe_is_event() ) return;

    $organizer_ids = tribe_get_organizer_ids();
    $multiple = count( $organizer_ids ) > 1;
    $title = ($multiple) ? $instance[ 'title' ] : $instance[ 'titlesingle' ];

    echo $args['before_widget'];
    if ( !empty($title) ) echo $args['before_title'] . apply_filters( 'widget_title', $title ) . $args['after_title']; ?>

    <dl>
      <dt style="display:none;">
      <?php
      do_action( 'tribe_events_single_meta_organizer_section_start' );

      foreach ( $organizer_ids as $organizer ) {
        if ( !$organizer ) {
          continue;
        }

        $phone = tribe_get_organizer_phone($organizer);
        $email = tribe_get_organizer_email($organizer);
        $website = tribe_get_organizer_website_link($organizer);

        printf('<dd class="tribe-organizer">%s</dd>', tribe_get_organizer_link($organizer));
        if ( !empty( $phone ) ) {
          printf('<dt class="visuallyhidden">%s</dt>', esc_html__('Phone: ', 'the-events-calendar'));
          printf('<dd class="tribe-organizer-tel">%s</dd>', esc_html($phone));
        }
        if ( !empty( $email ) ) {
          printf('<dt class="visuallyhidden">%s</dt>', esc_html__('Email: ', 'the-events-calendar'));
          printf('<dd class="tribe-organizer-email"><a href="mailto:%1$s">%1$s</a></dd>', esc_html($email));
        }
      }

      do_action( 'tribe_events_single_meta_organizer_section_end' );
      ?>
    </dl>

    <?php
    echo $args['after_widget'];
  }



  /**
   * Creates the settings form
   */

  function form( $instance ) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
    $titlesingle = ! empty( $instance['titlesingle'] ) ? $instance['titlesingle'] : ''; ?>
    <p>
      <?php
      printf('<label for="%s">%s</label>', $this->get_field_id( 'title' ), esc_html__( 'Title: ', 'fu-events-calendar' ));
      printf('<input type="text" id="%s" name="%s" value="%s" class="widefat title" />', $this->get_field_id( 'title' ), $this->get_field_name( 'title' ), esc_attr( $title ));
      ?>
    </p>
    <p>
      <?php
      printf('<label for="%s">%s</label>', $this->get_field_id( 'titlesingle' ), esc_html__( 'Title (Single): ', 'fu-events-calendar' ));
      printf('<input type="text" id="%s" name="%s" value="%s" class="widefat title" />', $this->get_field_id( 'titlesingle' ), $this->get_field_name( 'titlesingle' ), esc_attr( $titlesingle ));
      ?>
    </p>
    <p>This will only be visible on single event pages.</p>
  <?php }



  /**
   * Updates the widget settings
   */

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    $instance[ 'titlesingle' ] = strip_tags( $new_instance[ 'titlesingle' ] );
    return $instance;
  }
}