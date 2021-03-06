<?php
/**
 * Related Events Widget
 *
 * Creates a widget that displays related events
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}



class FU__Events__Widgets__Related extends WP_Widget {

  /**
   * Adds classname and description
   */

  function __construct() {
    $widget_options = array(
      'classname' => 'tribe-events-related-widget',
      'description' => 'Adds a list of related events to a single event page.'
    );
    parent::__construct(
      'fu-tec-related-widget',
      'Events Calendar Related Events',
      $widget_options
    );
  }



  /**
   * Creates the widget output
   */

  function widget( $args, $instance ) {
    if ( !is_single() || !tribe_is_event() ) return;

    $title = $instance[ 'title' ];
    $count = intval($instance[ 'count' ]);
    $posts = tribe_get_related_posts( $count );

    if ( !is_array( $posts ) || empty( $posts ) ) return;

    echo $args['before_widget'];
    if ( !empty($title) ) echo $args['before_title'] . apply_filters( 'widget_title', $title ) . $args['after_title']; ?>

    <ul class="tab-related-events posts-list">
      <?php foreach ( $posts as $post ) : ?>
      <li class="<?php tribe_events_event_classes() ?>">
        <?php $thumb = ( has_post_thumbnail( $post->ID ) ) ? get_the_post_thumbnail( $post->ID, 'post-thumbnail' ) : '<img src="' . esc_url( trailingslashit( tribe('fu.main')->plugin_url ) . 'resources/images/tribe-related-events-placeholder.png' ) . '" alt="' . esc_attr( get_the_title( $post->ID ) ) . '" />'; ?>
        <a href="<?php echo esc_url( tribe_get_event_link( $post ) ); ?>" class="url" rel="bookmark"><?php echo $thumb ?></a>
        <div class="content">
          <?php
          if ( $post->post_type == Tribe__Events__Main::POSTTYPE ) {
            echo tribe_events_event_schedule_details( $post, '<div class="tribe-events-widget-date">', '</div>' );
          }
          ?>
          <h4 class="tribe-events-title">
            <a href="<?php echo tribe_get_event_link( $post ); ?>" class="tribe-event-url" rel="bookmark"><?php echo get_the_title( $post->ID ); ?></a>
          </h4>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>

    <?php
    echo $args['after_widget'];
  }



  /**
   * Creates the settings form
   */

  function form( $instance ) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
    $count = ! empty( $instance['count'] ) ? $instance['count'] : '3'; ?>
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



  /**
   * Updates the widget settings
   */

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    $instance[ 'count' ] = strip_tags( $new_instance[ 'count' ] );
    return $instance;
  }
}