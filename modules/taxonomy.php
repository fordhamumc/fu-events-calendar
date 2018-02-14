<?php

/**
 * Add a custom tag for the events calendar
 *
 * @author Michael Foley
 *
 */

function fu_events_custom_taxonomy() {
  $tec = tribe('tec.main');
  $labels = array(
    'name'                       => _x( 'Event Tags', 'Taxonomy General Name', 'fu-events-calendar' ),
    'singular_name'              => _x( 'Event Tag', 'Taxonomy Singular Name', 'fu-events-calendar' ),
    'menu_name'                  => __( 'Event Tags', 'fu-events-calendar' )
  );
  $args = array(
    'labels'                     => $labels,
    'hierarchical'               => false,
    'public'                     => true,
    'show_ui'                    => true,
    'show_in_rest'               => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => true,
    'update_count_callback'      => '_update_post_term_count',
    'rewrite'                    => array( 'slug' => $tec->getRewriteSlug() . '/event_tag' )
  );
  register_taxonomy( 'tribe_events_tag', array( Tribe__Events__Main::POSTTYPE ), $args );

}
add_action( 'init', 'fu_events_custom_taxonomy', 0 );




/**
 * Add rewrite rule to create a permalink to custom tag
 */

function custom_rewrite_basic() {
  add_rewrite_rule('^' . tribe('tec.main')->getRewriteSlug() . '/event_tag/([^/]*)/?', 'index.php?post_type=' . Tribe__Events__Main::POSTTYPE . '&tribe_events_tag=$matches[1]', 'top');
}
add_action('init', 'custom_rewrite_basic');




/**
 * Remove default tag from events calendar
 */

function fu_events_remove_tags() {
  unregister_taxonomy_for_object_type( 'post_tag', 'tribe_events' );
}

add_action('init','fu_events_remove_tags', 100);




/**
 * Replace tags with event tags in the REST event api
 *
 * @param array   $data  The data that will be filtered
 * @param WP_Post $event The requested event
 *
 * @return array
 *
 */

function fu_events_rest_get_tags($data, $event) {
  $data = tribe('tec.rest-v1.repository')->get_terms( $event->ID, 'tribe_events_tag' );
  return $data;
}

add_filter('tribe_rest_event_tags_data', 'fu_events_rest_get_tags', 10, 2);




/**
 * Replace tag url with event tag url
 *
 * @param array   $term_data The data that will be filtered
 * @param string  $taxonomy  The term taxonomy
 *
 * @return array
 *
 */

function fu_events_rest_taxonomy_term_data($term_data, $taxonomy) {
  if ( $taxonomy === 'tribe_events_tag' ) {
    $term_data['urls'] = array(
      'self'       => tribe_events_rest_url( "event_tags/{$term_data['id']}" ),
      'collection' => tribe_events_rest_url( 'event_tags' ),
    );

    if ( ! empty( $term_data['parent'] ) ) {
      $term_data['urls']['up'] = tribe_events_rest_url( "event_tags/{$term_data['parent']}" );
    }
  }
  return $term_data;
}

add_filter('tribe_rest_taxonomy_term_data', 'fu_events_rest_taxonomy_term_data', 10, 2);




/**
 * Builds and hooks the event tags archives endpoint
 *
 * @since 4.6
 *
 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
 */
function fu_events_register_event_tags_endpoint( $register_routes = true ) {
  global $wp_version;
  $system           = tribe( 'tec.rest-v1.system' );

  if ( ! $system->supports_tec_rest_api() || ! $system->tec_rest_api_is_enabled() || version_compare( $wp_version, '4.7', '<' ) ) {
    return;
  }

  $main             = tribe( 'tec.rest-v1.main' );
  $messages         = tribe( 'tec.rest-v1.messages' );
  $post_repository  = tribe( 'tec.rest-v1.repository' );
  $validator        = tribe( 'tec.rest-v1.validator' );
  $terms_controller = new WP_REST_Terms_Controller( 'tribe_events_tag' );
  $archive_endpoint = new Tribe__Events__REST__V1__Endpoints__Archive_EventTag( $messages, $post_repository, $validator, $terms_controller );
  $single_endpoint  = new Tribe__Events__REST__V1__Endpoints__Single_EventTag( $messages, $post_repository, $validator, $terms_controller );

  if ( $register_routes ) {
    $namespace = $main->get_namespace() . '/events/' . $main->get_version();

    register_rest_route(
      $namespace,
      '/event_tags',
      array(
        array(
          'methods'  => WP_REST_Server::READABLE,
          'callback' => array( $archive_endpoint, 'get' ),
          'args'     => $archive_endpoint->READ_args(),
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
      '/event_tags/(?P<id>\\d+)',
      array(
        array(
          'methods'  => WP_REST_Server::READABLE,
          'callback' => array( $single_endpoint, 'get' ),
          'args'     => $single_endpoint->READ_args(),
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
  $documentation_endpoint->register_documentation_provider( '/event_tags', $archive_endpoint );
  $documentation_endpoint->register_documentation_provider( '/event_tags/{id}', $single_endpoint );
}


add_action( 'rest_api_init', 'fu_events_register_event_tags_endpoint' );


class Tribe__Events__REST__V1__Endpoints__Archive_EventTag
  extends Tribe__Events__REST__V1__Endpoints__Archive_Tag {
    /**
     * Returns the taxonomy of the terms handled by the endpoint.
     *
     * @since 4.6
     *
     * @return string
     */
    public function get_taxonomy() {
      return 'tribe_events_tag';
    }

    /**
     * Returns the archive base REST URL
     *
     * @since 4.6
     *
     * @return string
     */
    protected function get_base_rest_url() {
      return tribe_events_rest_url( 'event_tags/' );
    }

    /**
     * Returns the data key that will be used to store terms data in the response.
     *
     * @since 4.6
     *
     * @return string
     */
    protected function get_data_key() {
      return 'event_tags';
    }
  }

class Tribe__Events__REST__V1__Endpoints__Single_EventTag
  extends Tribe__Events__REST__V1__Endpoints__Single_Tag {
    /**
     * Returns the taxonomy of the terms handled by the endpoint.
     *
     * @since 4.6
     *
     * @return string
     */
    public function get_taxonomy() {
      return 'tribe_events_tag';
    }

    /**
     * Returns the term namespace used by the endpoint.
     *
     * @since 4.6
     *
     * @return string
     */
    protected function get_term_namespace() {
      return 'event_tags';
    }

    /**
     * Whether the value(s) all map to existing post tags.
     *
     * @param mixed $tag
     *
     * @return bool
     */
    public function is_post_event_tag( $tag ) {
      return tribe('tec.rest-v1.validator')->is_term_of_taxonomy( $tag, 'tribe_events_tag' );
    }

    /**
     * Returns the content of the `args` array that should be used to register the endpoint
     * with the `register_rest_route` function.
     *
     * @since 4.6
     *
     * @return array
     */
    public function READ_args() {
      return array(
        'id' => array(
          'in'                => 'path',
          'type'              => 'integer',
          'description'       => __( 'the event tag term ID', 'the-events-calendar' ),
          'required'          => true,
          'validate_callback' => array( $this, 'is_post_event_tag' ),
        ),
      );
    }
  }