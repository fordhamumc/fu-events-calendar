<?php
/**
 * Create an event tag archive REST endpoint
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__REST__V1__Endpoints__Archive_Event_Tag' ) ) {

  class FU__Events__REST__V1__Endpoints__Archive_Tag
    extends Tribe__Events__REST__V1__Endpoints__Archive_Tag {

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
     * Returns the archive base REST URL
     * @since 1.0
     *
     * @return string
     */

    protected function get_base_rest_url() {
      return tribe_events_rest_url($this->slug . '/');
    }




    /**
     * Returns the data key that will be used to store terms data in the response.
     * @since 1.0
     *
     * @return string
     */

    protected function get_data_key() {
      return $this->slug;
    }
  }
}