<?php
/**
 * Create an event tag REST endpoint
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__REST__V1__Endpoints__Single_Event_Tag' ) ) {

  class FU__Events__REST__V1__Endpoints__Single_Tag
    extends Tribe__Events__REST__V1__Endpoints__Single_Tag {

    private $taxonomy;
    private $slug;




    /**
     * Constructor
     * @since 1.0
     *
     * @param \Tribe__REST__Messages_Interface $messages
     * @param \Tribe__Events__REST__Interfaces__Post_Repository $repository
     * @param \Tribe__Events__Validator__Interface $validator
     * @param \WP_REST_Terms_Controller $terms_controller
     * @param string $taxonomy
     * @param string $slug
     */

    public function __construct(
      Tribe__REST__Messages_Interface $messages,
      Tribe__Events__REST__Interfaces__Post_Repository $repository,
      Tribe__Events__Validator__Interface $validator,
      WP_REST_Terms_Controller $terms_controller,
      $taxonomy,
      $slug
    ) {
      $this->taxonomy = $taxonomy;
      $this->slug = $slug;

      parent::__construct($messages, $repository, $validator, $terms_controller);
    }




    /**
     * Returns the taxonomy of the terms handled by the endpoint.
     * @since 1.0
     *
     * @return string
     */

    public function get_taxonomy() {
      return $this->taxonomy;
    }




    /**
     * Returns the term namespace used by the endpoint.
     * @since 1.0
     *
     * @return string
     */

    protected function get_term_namespace() {
      return $this->slug;
    }




    /**
     * Whether the value(s) all map to existing post tags.
     * @since 1.0
     *
     * @param mixed $tag
     *
     * @return bool
     */

    public function is_post_event_tag( $tag ) {
      return tribe('tec.rest-v1.validator')->is_term_of_taxonomy( $tag, $this->taxonomy );
    }




    /**
     * Returns the content of the `args` array that should be used to register the endpoint
     * with the `register_rest_route` function.
     * @since 1.0
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
}