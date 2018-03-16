<?php
/**
 * Main class to customize the events calendar.
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__Main' ) ) {
  class FU__Events__Main {

    /**
     * The current version of Community Events
     */

    const VERSION = '2.0.0';

    /**
     * Required The Events Calendar Version
     */

    const REQUIRED_TEC_VERSION = '4.6.6';

    /**
     * option name to save all plugin options under
     * as a serialized array
     */
    const OPTIONNAME = 'fu_events_options';

    /**
     * plugin options
     * @var array
     */

    protected static $options;

    /**
     * Path to the main plugin file
     * @var string
     */

    public $plugin_file;

    /**
     * Path to the plugin folder
     * @var string
     */

    public $plugin_dir;

    /**
     * Plugin directory name
     * @var string
     */

    public $plugin_path;

    /**
     * Url of the plugin
     * @var string
     */

    public $plugin_url;

    /**
     * Should the permalinks be flushed upon plugin load?
     * @var bool
     */

    public $maybeFlushRewrite;


    /**
     * Class constructor
     * @since 2.0
     *
     */

    public function __construct() {

      add_action( 'admin_init', array( $this, 'maybeFlushRewriteRules' ) );
      add_action( 'wp_head', array($this, 'list_structured_data') );
      add_action( 'widgets_init', array($this, 'register_widgets'), 90 );
      add_action( 'rest_api_init', array( $this, 'register_event_archives_endpoint' ) );
      add_filter( 'tribe_events_admin_show_cost_field', '__return_true', 100 );
      add_filter( 'tribe_events_template', array($this, 'filter_template_paths'), 10, 2 );
      add_filter( 'tribe_get_venue_details', array($this, 'remove_united_states') );
      add_filter( 'tribe_get_full_address', array($this, 'remove_united_states') );
      add_filter( 'tribe_events_event_schedule_details_inner', array($this, 'update_time'), 10, 2);
      add_filter( 'fu_events_single_event_time_formatted', array($this, 'update_time'), 10, 2);


      $this->plugin_file = FU_EVENTS_FILE;
      $this->plugin_path = trailingslashit( dirname( $this->plugin_file ) );
      $this->plugin_dir = trailingslashit( basename( $this->plugin_path ) );
      $this->plugin_url = plugins_url( $this->plugin_dir );
      $this->maybeFlushRewrite   = $this->get_option( 'maybeFlushRewrite' );

      $this->bind_implementations();
      $this->setup_custom_taxonomies();
    }



    /**
     * Sets the setting variable that says the rewrite rules should be flushed upon plugin load.
     * @since 2.0
     *
     */

    public static function activateFlushRewrite() {
      $options = self::get_options();
      $options['maybeFlushRewrite'] = true;
      update_option( self::OPTIONNAME, $options );
    }



    /**
     * Checks if it should flush rewrite rules (after plugin is loaded).
     * @since 1.0.1
     *
     */

    public function maybeFlushRewriteRules() {
      $options = self::get_options();
      if ( $this->maybeFlushRewrite == true ) {
        Tribe__Events__Main::flushRewriteRules();
        $options['maybeFlushRewrite'] = false;
        $options['lastFlushRewrite'] = date('Y-m-d H:i:s');
      }
      update_option( self::OPTIONNAME, $options );
    }



    /**
     * Get all options for the plugin.
     * @since 2.0
     *
     * @param bool $force
     *
     * @return array The current settings for the plugin.
     *
     */

    public static function get_options($force = false ) {
      if ( ! isset( self::$options ) || $force ) {
        $options       = get_option( self::OPTIONNAME, array() );
        self::$options = apply_filters( 'fu_events_get_options', $options );
      }
      return self::$options;
    }

    /**
     * Get value for a specific option.
     * @since 2.0
     *
     * @param string $optionName Name of option.
     * @param mixed $default Default value.
     * @param bool $force
     *
     * @return mixed Results of option query.
     *
     */

    public function get_option( $optionName, $default = '', $force = false ) {
      if ( ! $optionName ) {
        return;
      }

      if ( ! isset( self::$options ) || $force ) {
        self::get_options( $force );
      }

      $option = $default;
      if ( isset( self::$options[ $optionName ] ) ) {
        $option = self::$options[ $optionName ];
      }

      return apply_filters( 'fu_get_single_option', $option, $default, $optionName );
    }



    /**
     * Adds plugin views folder to list of template paths
     * @since 1.0
     *
     * @param string $file
     * @param string $template
     *
     * @return string
     *
     */

    public function filter_template_paths ( $file, $template ) {
      $custom_file_path = $this->plugin_path . 'views/' . $template;
      if ( !file_exists($custom_file_path) ) return $file;
      return $custom_file_path;
    }



    /**
     * Remove Country from Venue if United States
     * @since 1.0
     *
     * @param array   $venue_details
     *
     * @return array
     *
     */

    public function remove_united_states( $venue_details ) {
      if ( is_array( $venue_details ) ) {
        $address =& $venue_details["address"];
      } else {
        $address =& $venue_details;
      }
      $address = str_replace("United States", "", $address);
      return $venue_details;
    }



    /**
     * Updates time to Fordham's style
     * @since 1.0
     *
     * @param string    $inner      the inner html date/time content
     * @param string    $event_id   the id of the current event in the loop
     *
     * @return string
     *
     */

    public function update_time($inner, $event_id) {
      $inner = str_replace( array(':00', 'am', 'pm'), array('', 'a.m.', 'p.m.'), $inner );
      if ( tribe_get_start_date( $event_id, false, 'mdya' ) === tribe_get_end_date( $event_id, false, 'mdya' ) &&
        preg_match_all('# [ap]\.m\.#', $inner) > 1 ) {
        $inner = preg_replace('# [ap]\.m\.#', '', $inner, 1);
      }
      return $inner;
    }



    /**
     * Add structured data to list view
     * @since 1.0
     *
     */

    public function list_structured_data() {
      if ( !tribe_is_list_view() ) return;

      global $wp_query;
      Tribe__Events__JSON_LD__Event::instance()->markup( $wp_query->posts );
    }



    /**
     * Add custom taxonomies to REST api
     * @since 2.0
     *
     */

    public function setup_custom_taxonomies() {
      $tec = tribe('tec.main');
      $tag_labels = array(
        'name'                  => _x( 'Event Tags', 'Taxonomy General Name', 'fu-events-calendar' ),
        'singular_name'         => _x( 'Event Tag', 'Taxonomy Singular Name', 'fu-events-calendar' ),
        'menu_name'             => __( 'Event Tags', 'fu-events-calendar' )
      );

      $audience_labels = array(
        'name'                  => _x( 'Audiences', 'Taxonomy General Name', 'fu-events-calendar' ),
        'singular_name'         => _x( 'Audience', 'Taxonomy Singular Name', 'fu-events-calendar' ),
        'menu_name'             => __( 'Audience', 'fu-events-calendar' )
      );

      new FU__Events__Taxonomy(
        'tribe_events_tag',
        array( Tribe__Events__Main::POSTTYPE => $tec->getRewriteSlug() ),
        array( 'labels' => $tag_labels, 'rewrite' => array( 'slug' => 'event_tag' ) ),
        array( Tribe__Events__Main::POSTTYPE => 'post_tag' )
      );

      new FU__Events__Taxonomy(
        'tribe_audience',
        array( Tribe__Events__Main::POSTTYPE => $tec->getRewriteSlug() ),
        array( 'labels' => $audience_labels, 'rewrite' => array( 'slug' => 'audience' ) )
      );
    }



    /**
     * Override event archive api
     * @since 2.0.1
     *
     * @param bool    $register_routes    Whether routes for the endpoint should be registered or not.
     *
     */

    public function register_event_archives_endpoint( $register_routes = true ) {
      $tec              = tribe( 'tec.main' );
      $main             = tribe( 'tec.rest-v1.main' );
      $messages = tribe( 'tec.rest-v1.messages' );
      $post_repository = tribe( 'tec.rest-v1.repository' );
      $validator = tribe( 'tec.rest-v1.validator' );
      $endpoint = new FU__Events__REST__V1__Endpoints__Archive_Event( $messages, $post_repository, $validator );
      $namespace = $main->get_namespace() . '/' . $tec->getRewriteSlug() . '/' . $main->get_version();

      if ( $register_routes ) {
        register_rest_route( $namespace, '/events', array(
          'methods'  => WP_REST_Server::READABLE,
          'callback' => array( $endpoint, 'get' ),
          'args'     => $endpoint->READ_args(),
        ), true );
      }

      tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/events', $endpoint );
    }



    /**
     * Register the widgets
     * @since 1.0
     *
     */

    public function register_widgets() {
      register_widget( 'FU__Events__Widgets__iCal' );
      register_widget( 'FU__Events__Widgets__Organizers' );
      register_widget( 'FU__Events__Widgets__Recurring' );
      register_widget( 'FU__Events__Widgets__Related' );
    }



    /**
     * Registers the slug bound to the implementations in the container.
     * @since 1.0
     *
     */

    public function bind_implementations() {
      tribe_singleton('fu.admin', new FU__Events__Admin);
      tribe_singleton('fu.community', new FU__Events__Community);
      tribe_singleton('fu.custom-fields', new FU__Events__Custom_Fields);
      tribe_singleton('fu.feed', new FU__Events__Feed);
      tribe_singleton('fu.list', new FU__Events__List);
      tribe_singleton('fu.register', new FU__Events__Register);
    }
  }
}