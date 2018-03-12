<?php
/**
 * Create a custom taxonomy
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__Taxonomy' ) ) {
  class FU__Events__Taxonomy {

    private $object_type = array();
    private $args = array(
      'hierarchical'               => false,
      'public'                     => true,
      'show_ui'                    => true,
      'show_in_rest'               => true,
      'show_admin_column'          => true,
      'show_in_nav_menus'          => true,
      'update_count_callback'      => '_update_post_term_count'
    );
    private $taxonomy;
    private $remove_taxonomy = array();
    private $slug;



    /**
     * Construct the class instance
     * @since 2.0
     *
     * @param string        $taxonomy           The name of the taxonomy
     * @param array         $object_type        The object types associated with the taxonomy.
     *                                            Pattern: [post_type] => dir
     * @param array         $args               The register_taxonomy arguments
     * @param array         $remove_taxonomy    Taxonomies to remove in the following pattern
     *                                            Pattern: [post_type] => taxonomy
     *
     */

    public function __construct($taxonomy, $object_type, $args = array(), $remove_taxonomy = array()) {
      $this->taxonomy = $taxonomy;
      $this->object_type = $object_type;
      $this->args = array_merge($this->args, $args);
      $this->remove_taxonomy = $remove_taxonomy;

      if ( array_key_exists('rewrite', $this->args) && array_key_exists('slug', $this->args['rewrite']) ){
        $this->slug = $this->args['rewrite']['slug'];
      }

      add_action( 'init', array($this, 'create_custom_taxonomy'), 0);
      add_action( 'init', array($this, 'custom_rewrite') );
      add_action( 'init', array($this, 'remove_tags'), 100 );
      add_filter( 'tribe_events_listview_ajax_get_event_args', array($this, 'listview_ajax_tag') );
      add_filter( 'tribe_rest_event_data', array($this, 'rest_event_data'), 10, 2);
      add_filter( 'tribe_rest_taxonomy_term_data', array($this, 'rest_taxonomy_term_data'), 10, 2);
      add_action( 'rest_api_init', array($this, 'rest_register_event_tags_endpoint') );
    }



    /**
     * Add a custom tag for the events calendar
     * @since 1.0
     *
     */

    public function create_custom_taxonomy() {
      register_taxonomy( $this->taxonomy, array_keys($this->object_type), $this->args );
    }




    /**
     * Add rewrite rule to create a permalink to custom tag
     * @since 1.0
     *
     */

    public function custom_rewrite() {
      if (!$this->slug) return;

      foreach( $this->object_type as $post_type => $slug_dir ) {
        add_rewrite_rule('^' . $slug_dir . '/' . $this->slug . '/([^/]+)/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$', 'index.php?post_type=' . $post_type . '&' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top');
        add_rewrite_rule('^' . $slug_dir . '/' . $this->slug . '/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?post_type=' . $post_type . '&' . $this->taxonomy . '=$matches[1]&paged=$matches[2]', 'top');
        add_rewrite_rule('^' . $slug_dir . '/' . $this->slug . '/([^/]+)/?$', 'index.php?post_type=' . $post_type . '&' . $this->taxonomy . '=$matches[1]', 'top');
      }
    }




    /**
     * Remove default tag from events calendar
     * @since 1.0
     *
     */

    public function remove_tags() {
      foreach( $this->remove_taxonomy as $post_type => $taxonomy ) {
        unregister_taxonomy_for_object_type( $taxonomy, $post_type );
      }
    }




    /**
     * Make the custom tag to work with Ajax
     * @since 2.0
     *
     * @param array   $args   The arguments that will be passed to the query
     *
     * @return array
     */

    public function listview_ajax_tag($args) {
      if (!$this->slug) return $args;

      $referrer = parse_url(wp_get_referer());

      if ( !empty($referrer['path']) ) {
        preg_match('@/' . $this->slug . '/([^/]+)@', $referrer['path'], $matches);

        if ( !empty($matches) ) {
          $args[$this->taxonomy] = $matches[1];
        }

        if ( array_key_exists('query', $referrer) ) {
          parse_str($referrer['query'], $query);

          if (array_key_exists($this->taxonomy, $query)) {
            $args[$this->taxonomy] = $query[$this->taxonomy];
          }
        }
      }
      return $args;
    }


    /**
     * Adds the taxonomy to the REST event api
     * @since 2.0
     *
     * @param array     $data     The data that will be filtered
     * @param WP_Post   $event    The requested event
     *
     * @return array
     *
     */

    public function rest_event_data($data, $event) {
      $data[$this->slug] = tribe('tec.rest-v1.repository')->get_terms( $event->ID, $this->taxonomy );

      if ( array_key_exists(Tribe__Events__Main::POSTTYPE, $this->remove_taxonomy) ) {
        $key = $this->remove_taxonomy[Tribe__Events__Main::POSTTYPE];
        if ($key === 'post_tag') $key = 'tags';
        unset($data[$key]);
      }

      return $data;
    }




    /**
     * Replace tag url with event tag url
     * @since 1.0
     *
     * @param array   $term_data    The data that will be filtered
     * @param string  $taxonomy     The term taxonomy
     *
     * @return array
     *
     */

    public function rest_taxonomy_term_data($term_data, $taxonomy) {
      if ( $taxonomy === $this->taxonomy ) {
        $term_data['urls'] = array(
          'self'       => tribe_events_rest_url( "{$this->slug}/{$term_data['id']}" ),
          'collection' => tribe_events_rest_url( $this->slug ),
        );

        if ( ! empty( $term_data['parent'] ) ) {
          $term_data['urls']['up'] = tribe_events_rest_url( "{$this->slug}/{$term_data['parent']}" );
        }
      }
      return $term_data;
    }




    /**
     * Builds and hooks the event tags archives endpoint
     * @since 1.0
     *
     * @param bool $register_routes Whether routes for the endpoint should be registered or not.
     */

    public function rest_register_event_tags_endpoint( $register_routes = true ) {
      global $wp_version;
      $system           = tribe( 'tec.rest-v1.system' );

      if ( ! $this->args['show_in_rest'] || ! $system->supports_tec_rest_api() || ! $system->tec_rest_api_is_enabled() || version_compare( $wp_version, '4.7', '<' ) ) {
        return;
      }

      $tec              = tribe( 'tec.main' );
      $main             = tribe( 'tec.rest-v1.main' );
      $messages         = tribe( 'tec.rest-v1.messages' );
      $post_repository  = tribe( 'tec.rest-v1.repository' );
      $validator        = tribe( 'tec.rest-v1.validator' );
      $terms_controller = new WP_REST_Terms_Controller( $this->taxonomy );
      $archive_endpoint = new FU__Events__REST__V1__Endpoints__Archive_Tag( $messages, $post_repository, $validator, $terms_controller, $this->taxonomy, $this->slug );
      $single_endpoint  = new FU__Events__REST__V1__Endpoints__Single_Tag( $messages, $post_repository, $validator, $terms_controller, $this->taxonomy, $this->slug );


      if ( $register_routes ) {
        $namespace = $main->get_namespace() . '/' . $tec->getRewriteSlug() . '/' . $main->get_version();

        register_rest_route(
          $namespace,
          '/' . $this->slug,
          array(
            array(
              'methods'             => WP_REST_Server::READABLE,
              'callback'            => array( $archive_endpoint, 'get' ),
              'args'                => $archive_endpoint->READ_args(),
            ),
            array(
              'methods'             => WP_REST_Server::CREATABLE,
              'args'                => $single_endpoint->CREATE_args(),
              'permission_callback' => array( $single_endpoint, 'can_create' ),
              'callback'            => array( $single_endpoint, 'create' ),
            ),
          )
        );

        register_rest_route(
          $namespace,
          '/' . $this->slug . '/(?P<id>\\d+)',
          array(
            array(
              'methods'             => WP_REST_Server::READABLE,
              'callback'            => array( $single_endpoint, 'get' ),
              'args'                => $single_endpoint->READ_args(),
            ),
            array(
              'methods'             => WP_REST_Server::EDITABLE,
              'args'                => $single_endpoint->EDIT_args(),
              'permission_callback' => array( $single_endpoint, 'can_edit' ),
              'callback'            => array( $single_endpoint, 'update' ),
            ),
            array(
              'methods'             => WP_REST_Server::DELETABLE,
              'args'                => $single_endpoint->DELETE_args(),
              'permission_callback' => array( $single_endpoint, 'can_delete' ),
              'callback'            => array( $single_endpoint, 'delete' ),
            ),
          )
        );
      }

      $documentation_endpoint = tribe( 'tec.rest-v1.endpoints.documentation' );
      $documentation_endpoint->register_documentation_provider( '/' . $this->slug, $archive_endpoint );
      $documentation_endpoint->register_documentation_provider( '/' . $this->slug . '/{id}', $single_endpoint );
    }
  }
}