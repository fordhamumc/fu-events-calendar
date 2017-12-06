<?php

include(plugin_dir_path(__FILE__) . 'ical.php');
include(plugin_dir_path(__FILE__) . 'organizers.php');
include(plugin_dir_path(__FILE__) . 'recurring.php');
include(plugin_dir_path(__FILE__) . 'related.php');


function fu_register_widgets() {
  register_widget( 'Fu_Tec_iCal_Widget' );
  register_widget( 'Fu_Tec_Organizers_Widget' );
  register_widget( 'Fu_Tec_Recurring_Widget' );
  register_widget( 'Fu_Tec_Related_Widget' );
}
add_action( 'widgets_init', 'fu_register_widgets' );