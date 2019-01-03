<?php
/**
 * Create a custom status for events that are waiting outside approval
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__Limbo' ) ) {
  class FU__Events__Limbo {
      /**
       * Slug of the post type used for events
       * @since 2.0.5
       *
       * @var string
       */
      public static $post_type = TribeEvents::POSTTYPE;

      /**
       * Status slug
       * @since 2.0.5
       *
       * @var string
       */
      public static $status = 'fu-limbo';


      /**
       * Class constructor
       * @since 2.0.5
       */
      public function __construct() {
          add_action( 'init', array($this, 'register_status') );
          add_action( 'admin_footer-post.php', array($this, 'append_post_status') );
          add_action( 'admin_footer-post-new.php', array($this, 'append_post_status') );
          add_action( 'admin_footer-edit.php', array($this, 'append_post_status_inline') );
      }


      /**
       * Add a 'limbo' post status
       * @since 2.0.5
       */
      public function register_status() {
          register_post_status( self::$status, array(
              'label'                       => __( 'Limbo', 'fu-events-calendar' ),
              'label_count'                 => _nx_noop( 'Limbo <span class="count">(%s)</span>', 'Limbo <span class="count">(%s)</span>', 'event status', 'fu-events-calendar' ),
              'show_in_admin_all_list'      => false,
              'show_in_admin_status_list'   => true,
              'public'                      => false,
              'internal'                    => false,
          ) );
      }


      /**
       * Add post status to the edit and new post page for the tribe events post type
       * @since 2.0.5
       */
      public function append_post_status() {
          $post = get_post();
          $selected = '';
          $label = '';
          if($post->post_type !== self::$post_type) return;

          if($post->post_status === self::$status){
              $selected = 'selected=\"selected\"';
              $label = __( 'Limbo', 'fu-events-calendar' );
          }

          ?>
          <script type="text/javascript">
            (function ($) {
                $('select#post_status').append('<option value="<?php echo self::$status ?>" <?php echo $selected ?>><?php _e( 'Limbo', 'fu-events-calendar' ) ?></option>');
                <?php if ($label !== ''): ?>
                    $('#post-status-display').text('<?php echo $label ?>');
                <?php endif; ?>
            })(jQuery);
          </script>
          <?php
      }


      /**
       * Add post status to the edit and new post page for the tribe events post type
       * @since 2.0.5
       */
      public function append_post_status_inline() {
        $screen = get_current_screen();
        if ($screen->post_type !== self::$post_type) return;
        ?>
        <script type="text/javascript">
            (function ($) {
                $('select[name="_status"]').append('<option value="<?php echo self::$status ?>"><?php _e('Limbo', 'fu-events-calendar' ) ?></option>');
            })(jQuery);
        </script>
        <?php
      }
  }
}