<?php


class FU__Events__REST__V1__Endpoints__Archive_Event
	extends Tribe__Events__REST__V1__Endpoints__Archive_Event
{


  /**
   * Handles GET requests on the endpoint.
   *
   * @param WP_REST_Request $request
   *
   * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
   */
  public function get(WP_REST_Request $request)
  {
    $args = array();
    $date_format = Tribe__Date_Utils::DBDATETIMEFORMAT;

    $args['paged'] = $request['page'];
    $args['posts_per_page'] = $request['per_page'];
    $args['start_date'] = isset($request['start_date']) ?
      Tribe__Timezones::localize_date($date_format, $request['start_date'])
      : false;
    $args['end_date'] = isset($request['end_date']) ?
      Tribe__Timezones::localize_date($date_format, $request['end_date'])
      : false;
    $args['s'] = $request['search'];

    /**
     * Allows users to override "inclusive" start and end dates and  make the REST API use a
     * timezone-adjusted date range.
     *
     * Example: wp-json/tribe/events/v1/events?start_date=2017-12-21&end_date=2017-12-22
     *
     * - The "inclusive" behavior, which is the default here, would set start_date to
     *   2017-12-21 00:00:00 and end_date to 2017-12-22 23:59:59. Events within this range will
     *   be retrieved.
     *
     * - If you set this filter to false on a site whose timezone is America/New_York, then the
     *   REST API would set start_date to 2017-12-20 19:00:00 and end_date to
     *   2017-12-21 19:00:00. A different range of events to draw from.
     *
     * @since 4.6.8
     *
     * @param bool $use_inclusive Defaults to true. Whether to use "inclusive" start and end dates.
     */
    if (apply_filters('tribe_events_rest_use_inclusive_start_end_dates', true)) {

      if ($args['start_date']) {
        $args['start_date'] = tribe_beginning_of_day($request['start_date']);
      }

      if ($args['end_date']) {
        $args['end_date'] = tribe_end_of_day($request['end_date']);
      }
    }

    $args['meta_query'] = array_filter(array(
      $this->parse_meta_query_entry($request['venue'], '_EventVenueID', '=', 'NUMERIC'),
      $this->parse_meta_query_entry($request['organizer'], '_EventOrganizerID', '=', 'NUMERIC'),
      $this->parse_featured_meta_query_entry($request['featured']),
    ));

    $args['tax_query'] = array_filter(array(
      $this->parse_terms_query($request['categories'], Tribe__Events__Main::TAXONOMY),
      $this->parse_terms_query($request['tags'], 'tribe_events_tag'),
      $this->parse_terms_query($request['audience'], 'tribe_audience'),
    ));

    $extra_rest_args = array(
      'venue' => Tribe__Utils__Array::to_list($request['venue']),
      'organizer' => Tribe__Utils__Array::to_list($request['organizer']),
      'featured' => $request['featured'],
    );
    $extra_rest_args = array_diff_key($extra_rest_args, array_filter($extra_rest_args, 'is_null'));

    // Filter by geoloc
    if (!empty($request['geoloc'])) {
      $args['tribe_geoloc'] = 1;
      $args['tribe_geoloc_lat'] = isset($request['geoloc_lat']) ? $request['geoloc_lat'] : '';
      $args['tribe_geoloc_lng'] = isset($request['geoloc_lng']) ? $request['geoloc_lng'] : '';
    }

    $args = $this->parse_args($args, $request->get_default_params());
    $data = array('events' => array());

    $data['rest_url'] = $this->get_current_rest_url($args, $extra_rest_args);

    if (null === $request['status']) {
      $cap = get_post_type_object(Tribe__Events__Main::POSTTYPE)->cap->edit_posts;
      $args['post_status'] = current_user_can($cap) ? 'any' : 'publish';
    } else {
      $args['post_status'] = $this->filter_post_status_list($request['status']);
    }

    // Due to an incompatibility between date based queries and 'ids' fields we cannot do this, see `wp_list_pluck` use down
    // $args['fields'] = 'ids';

    if (empty($args['posts_per_page'])) {
      $args['posts_per_page'] = $this->get_default_posts_per_page();
    }

    $events = tribe_get_events($args);

    $page = $this->parse_page($request) ? $this->parse_page($request) : 1;

    if (empty($events)) {
      $message = $this->messages->get_message('event-archive-page-not-found');

      return new WP_Error('event-archive-page-not-found', $message, array('status' => 404));
    }

    $events = wp_list_pluck($events, 'ID');

    unset($args['fields']);

    if ($this->has_next($args, $page)) {
      $data['next_rest_url'] = $this->get_next_rest_url($data['rest_url'], $page);
    }

    if ($this->has_previous($page, $args)) {
      $data['previous_rest_url'] = $this->get_previous_rest_url($data['rest_url'], $page);;
    }

    foreach ($events as $event_id) {
      $data['events'][] = $this->repository->get_event_data($event_id);
    }

    $data['total'] = $total = $this->get_total($args);
    $data['total_pages'] = $this->get_total_pages($total, $args['posts_per_page']);

    /**
     * Filters the data that will be returned for an events archive request.
     *
     * @param array $data The retrieved data.
     * @param WP_REST_Request $request The original request.
     */
    $data = apply_filters('tribe_rest_events_archive_data', $data, $request);

    $response = new WP_REST_Response($data);

    if (isset($data['total']) && isset($data['total_pages'])) {
      $response->header('X-TEC-Total', $data['total'], true);
      $response->header('X-TEC-TotalPages', $data['total_pages'], true);
    }

    return $response;
  }
}