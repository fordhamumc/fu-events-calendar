<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Fordham Events Calendar Edits
 * Plugin URI:        http://news.fordham.edu
 * Description:       Customizations for Modern Tribe's The Events Calendar
 * Version:           2.0.6
 * Author:            Michael Foley
 * Author URI:        http://michaeldfoley.com
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       fu-events-calendar
 * Domain Path:       /languages
*/

define( 'FU_EVENTS_DIR', dirname( __FILE__ ) );
define( 'FU_EVENTS_FILE', __FILE__ );

/**
 * Instantiate class
 *
 */

function fu_events_load() {
  fu_events_autoloading();

  $classes_exist = class_exists( 'Tribe__Events__Main' ) && class_exists( 'FU__Events__Main' );
  $version_ok = $classes_exist && defined( 'Tribe__Events__Main::VERSION' ) && version_compare( Tribe__Events__Main::VERSION, FU__Events__Main::REQUIRED_TEC_VERSION, '>=' );

  if ( ! $version_ok ) {
    add_action( 'admin_notices', 'fu_show_fail_message' );
    return;
  }

  tribe_singleton( 'fu.main', new FU__Events__Main() );
}
add_action( 'plugins_loaded', 'fu_events_load', 2 );


function fu_events_autoloading() {
  if ( ! class_exists( 'Tribe__Autoloader' ) ) {
    return;
  }

  $autoloader = Tribe__Autoloader::instance();
  $autoloader->register_prefix( 'FU__Events__', dirname( __FILE__ ) . '/src/FU' );
  $autoloader->register_autoloader();
}

/**
 * Shows message if the plugin can't load due to TEC not being installed.
 *
 */

function fu_show_fail_message() {
  if ( current_user_can( 'activate_plugins' ) ) {
    $url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
    $title = __( 'The Events Calendar', 'fu-events-community' );
    echo '<div class="error"><p>' . sprintf( __( 'To begin using Fordham Events Calendar Edits, please install the latest version of <a href="%1$s" class="thickbox" title="%2$s">%2$s</a>.', 'fu-events-community' ), esc_url( $url ), $title ) . '</p></div>';
  }
}

register_activation_hook( FU_EVENTS_FILE, 'fu_activate' );
function fu_activate() {
  fu_events_autoloading();
  if ( ! class_exists( 'FU__Events__Main' ) ) {
    return;
  }
  fu_add_event_submitter_role();
  FU__Events__Main::activateFlushRewrite();
}


/**
 * Add an event submitter role
 * @since 2.0.3
 *
 */

function fu_add_event_submitter_role() {
  add_role(
    'fu_event_submitter',
    __('Event Submitter', 'fu-events-calendar'),
    array(
      'read' => true
    )
  );
}
