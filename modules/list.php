<?php

/**
* Add community submit button to list view
*
* @author Michael Foley
*
*/

function fu_add_submit_button() {
  if ( class_exists('Tribe__Events__Community__Main') ) {
    $tec = Tribe__Events__Community__Main::instance();
    printf('<div class="tribe-bar-community-submit"><a href="/%s" class="button">%s</a></div>', $tec->getCommunityRewriteSlug() . '/' . $tec->rewriteSlugs['add'], esc_html__('Submit an Event', 'fu-events-calendar'));
  }
}
add_action('tribe_events_after_the_title', 'fu_add_submit_button');