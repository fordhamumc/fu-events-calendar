<?php


class FU__Events__Asset__Ajax_Tag extends Tribe__Events__Asset__Abstract_Asset {

  public function handle() {
    $deps        = array_merge( $this->deps, array( 'jquery', $this->prefix . '-list' ) );
    $tribe_paged = ( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
    $ajax_data   = array(
      'ajaxurl'     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
      'tribe_paged' => $tribe_paged,
    );
    $path        = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'events-tag.js' ), true );

    $handle = 'fu-events-tag';
    wp_enqueue_script( $handle, $path, $deps, $this->filter_js_version(), true );
    wp_localize_script( $handle, 'TribeList', $ajax_data );
  }
}
