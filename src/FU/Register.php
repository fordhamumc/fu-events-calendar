<?php
/**
 * Create a custom registration page for community events
 *
 * @author Michael Foley
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( ! class_exists( 'FU__Events__Register' ) ) {
  class FU__Events__Register {

    /**
     * the main rewrite slug to use
     * @var string
     */

    public $rewriteSlugs;




    /**
     * form field attributes
     * @var array
     */

    protected $formFields = array();




    /**
     * Class constructor
     * @since 2.0.3
     *
     */

    public function __construct() {
      add_action( 'init', array( $this, 'init' ) );
      add_action( 'wp_router_generate_routes', array( $this, 'addRoutes' ) );
      //add_action( 'wp_enqueue_scripts', array( 'Tribe__Events__Community__Main', 'enqueue_assets' ), 20 );
      add_action( 'tribe_community_before_login_form', array( $this, 'redirect' ) );
      add_action( 'tribe_community_before_login_form', array( $this, 'add_header' ) );
      add_action( 'tribe_community_after_login_form', array( $this, 'add_login_footer' ) );
      add_action( 'tribe_ce_before_event_registration_page', array( $this, 'add_header' ) );
      add_action( 'tribe_ce_after_event_registration_page', array( $this, 'add_footer' ) );

      $this->set_field('nickname',   array('name' => 'fullname',  'label' => 'Full Name', 'callback' => 'validate_text'));
      $this->set_field('user_login', array('name' => 'user',  'label' => 'Username', 'callback' => 'validate_user'));
      $this->set_field('user_email', array('name' => 'email', 'label' => 'Email',    'type' => 'email', 'callback' => 'validate_email'));
      $this->set_field('user_pass',  array('name' => 'pass',  'label' => 'Password', 'type' => 'password', 'callback' => 'validate_password', 'help' => esc_html__( 'Minimum: 8 characters, 1 letter, and 1 number.', 'fu-events-calendar' )));
    }




    /**
     * Init the plugin.
     * @since 2.0.3
     *
     */

    public function init() {
      $this->rewriteSlugs['register']   = sanitize_title( __( 'register', 'fu-events-calendar' ) );
    }




    /**
     * Singleton instance method.
     * @since 2.0.3
     *
     * @return Tribe__Events__Community__Main
     *
     */

    public static function instance() {
      return tribe( 'fu.register' );
    }




    /**
     * Add wprouter and callbacks.
     * @since 2.0.3
     *
     * @param object    $router       The router object.
     *
     */

    public function addRoutes($router) {
      if ( ! class_exists( 'Tribe__Events__Community__Main' ) ) {
        return;
      }

      $tec_template = tribe_get_option('tribeEventsTemplate');

      switch ($tec_template) {
        case '' :
          $template_name = Tribe__Events__Templates::getTemplateHierarchy('default-template');
          break;
        case 'default' :
          $template_name = 'page.php';
          break;
        default :
          $template_name = $tec_template;
      }

      $template_name = apply_filters('tribe_events_community_template', $template_name);

      // add event
      $router->add_route('fu-register-route', array(
        'path' => '^' . $this->get_url('register', false) . '$',
        'query_vars' => array(),
        'page_callback' => array(get_class(), 'registrationCallback'),
        'page_arguments' => array(),
        'access_callback' => true,
        'title' => apply_filters('fu_ce_register_page_title', __('Create an Account', 'fu-events-calendar')),
        'template' => $template_name,
      ));
    }



    /**
     * Display registration form
     * @since 2.0.3
     *
     */

    public static function registrationCallback() {
      $fur = self::instance();
      $tce = tribe( 'community.main' );

      add_filter( 'edit_post_link', array( $tce, 'removeEditPostLink' ) );
      $tce->removeFilters();
      $fur->template_compatibility();


      do_action( 'tribe_ce_before_event_registration_page' );
      if (isset($_POST['submit'])) {
        echo $fur->submit();
      } else {
        echo $fur->registration_form();
      }
      do_action( 'tribe_ce_after_event_registration_page' );
    }




    /**
     * Returns the url for the given slug
     * @since 2.0.3
     *
     * @param string    $slug       where we are going
     * @param bool      $home_url   include home url
     *
     * @return string;
     *
     */

    public function get_url($slug, $home_url = true) {
      $tce = tribe( 'community.main' );
      $rewrite_slugs = ( array_key_exists($slug, $this->rewriteSlugs) ) ? $this->rewriteSlugs : $tce->rewriteSlugs;
      $path = $tce->getCommunityRewriteSlug() . '/' . $rewrite_slugs[$slug];

      return ( $home_url ) ? home_url( $path ) : $path;
    }




    /**
     * Redirect to login page
     * @since 2.0.5
     *
     */
    public function redirect() {
      auth_redirect();
    }




    /**
     * Add community form header
     * @since 2.0.3
     *
     */

    public function add_header() {
      echo '<div id="tribe-community-events" class="tribe-community-events form login-form">';
      tribe_get_template_part( 'community/modules/header-links' );
    }




    /**
     * Add community form footer
     * @since 2.0.3
     *
     */

    public function add_footer() {
      echo '</div>';
    }




    /**
     * Add community login form footer
     * @since 2.0.3
     *
     */

    public function add_login_footer() {
      printf("<p class='register-link'>%s <a rel='nofollow' href='%s'>%s</a></p>",
        esc_html__("Don't have an account?",'fu-events-calendar'),
        $this->get_url('register'),
        esc_html__('Sign Up', 'fu-events-calendar')
      );
      printf("<p class='forgot-link'><a href='%s'>%s</a></p>",
        wp_lostpassword_url( $this->get_url('add') ),
        esc_html__("Forgot password?",'fu-events-calendar')
      );
      $this->add_footer();
    }




    /**
     * Print the registration form
     * @since 2.0.3
     *
     * @return string
     *
     */

    protected function registration_form() {
      do_action( 'tribe_community_before_registration_form' );
      ob_start();
      echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
      foreach ($this->formFields as $field) {
        echo $this->form_input($field);
      }
      echo '<input type="submit" name="submit" class="button" value="Register"/>';
      echo '</form>';
      do_action( 'tribe_community_after_registration_form' );

      return ob_get_clean();
    }




    /**
     * Ensure that view functions when the Default Events Template is in use.
     * @since 2.0.3
     *
     */

    protected function template_compatibility() {
      add_filter( 'tribe_events_current_view_template', array( tribe( 'community.main' ), 'default_template_placeholder' ) );
      Tribe__Events__Template_Factory::asset_package( 'events-css' );
    }




    /**
     * Add form field
     * @since 2.0.3
     *
     * @param string   $field      fields from the wp_insert_user function
     * @param array     $args {
     *      the input values
     *      @option string      'name'        name of the input field
     *      @option string      'label'       displayed label for the input field
     *      @option string      'value'       the value of the input field
     *      @option string      'type'        the type of input field
     *      @option bool        'required'    is the field required
     *      @option string|null 'callback'    sanitize and validation callback function
     * }
     *
     */

    protected function set_field($field, $args) {
      $args += array(
        'value'     => '',
        'type'      => 'text',
        'required'  => true,
        'callback'  => 'validate_default'
      );
      if (isset($args['name'], $args['label']) ) {
        $this->formFields[$field] = $args;
      }
    }




    /**
     * Create a form input
     * @since 2.0.3
     *
     * @param array     $args     the form inputs
     *
     * @return string
     *
     */

    protected function form_input( $args ) {

      $required = '';

      $output = "<label for='{$args['name']}'>{$args['label']}: ";
      if ($args['required']) {
        $required = 'required';
        $output .= "<span class='req'>(required)</span>";
      }
      $output .= "</label>";
      $output .= "<input type='{$args['type']}' id='{$args['name']}' name='{$args['name']}' value='{$args['value']}' {$required} />";
      if ( !empty($args['help']) ) {
        $output .= "<small>{$args['help']}</small>";
      }

      return '<div class="tribe-login-field">' . $output . '</div>';
    }




    /**
     * Sanitize and validate input
     * @since 2.0.3
     *
     * @return array
     *
     */

    protected function submit() {
      $tce = tribe( 'community.main' );
      $errors = new WP_Error;
      $user_data = array();

      foreach ($this->formFields as $key => $field) {
        if ( !empty($field['callback']) && method_exists($this, $field['callback']) ) {
          $user_data[$key] = $this->formFields[$key]['value'] = $this->{$field['callback']}($field, $errors);
        }
      }

      $this->enqueue_errors($errors);

      if ( !count($errors->get_error_messages()) ) {
        $user_data['role'] = 'fu_event_submitter';
        if ( !wp_roles()->is_role( $user_data['role'] ) ) {
          $user_data['role'] = 'subscriber';
        }
        $user_data['display_name'] = $user_data['nickname'];
        $user_id = wp_insert_user( $user_data ) ;
        if ( is_wp_error( $user_id ) ) {
          $this->enqueue_errors($user_id);
        } else {
          $this->clear_form();
          wp_redirect( $this->get_url('add') . '?success=1', 302 );
          exit;
        }
      }
      $tce->outputMessage();

      if ( count($errors->get_error_messages()) ) {
        echo $this->registration_form();
      }

    }



    /**
     * Clear form
     * @since 2.0.3
     *
     */

    protected function clear_form() {
      foreach ($this->formFields as &$field) {
        unset($field['value']);
      }
    }




    /**
     * Enqueue error messages
     * @since 2.0.3
     *
     * @param WP_Error  $errors    WP Error object
     *
     */

    protected function enqueue_errors($errors) {
      $tce = tribe( 'community.main' );
      foreach ($errors->get_error_messages() as $error_message) {
        $tce->enqueueOutputMessage($error_message, 'error');
      }
    }




    /**
     * Minimum validation. Checks for required field and trims input
     * @since 2.0.3
     *
     * @param array     $field     field properties
     * @param WP_Error  $errors    WP Error object
     *
     * @return string   sanitized input value
     *
     */

    protected function validate_default($field, &$errors) {
      $raw_value = $_POST[$field['name']];

      if ( empty($raw_value) && $field['required'] ) {
        $errors->add('field', __( 'Required form field is missing.', 'fu-events-calendar' ));
      }

      return trim($raw_value);
    }




    /**
     * Validate text
     * @since 2.0.3
     *
     * @param array     $field     field properties
     * @param WP_Error  $errors    WP Error object
     *
     * @return string   sanitized input value
     *
     */

    protected function validate_text($field, &$errors) {
      $value = $this->validate_default($field, $errors);
      return sanitize_text_field($value);
    }




    /**
     * Validate email
     * @since 2.0.3
     *
     * @param array     $field     field properties
     * @param WP_Error  $errors    WP Error object
     *
     * @return string   sanitized input value
     *
     */

    protected function validate_email($field, &$errors) {
      $value = $this->validate_default($field, $errors);

      if ( !empty($value) ) {
        if ( !is_email($value) ) {
          $errors->add('email_invalid', sprintf( __( 'The %s you entered is not valid.', 'fu-events-calendar' ), strtolower($field['label'])));
        }

        if ( email_exists($value) ) {
          $errors->add('email', sprintf( __( 'That %s is already in use.', 'fu-events-calendar' ), strtolower($field['label'])));
        }
      }
      return $value;
    }




    /**
     * Validate username
     * @since 2.0.3
     *
     * @param array     $field     field properties
     * @param WP_Error  $errors    WP Error object
     *
     * @return string   sanitized input value
     *
     */

    protected function validate_user($field, &$errors) {
      $value = $this->validate_default($field, $errors);

      if (!empty($value)) {
        if ( 4 > strlen($value) ) {
          $errors->add('username_length', sprintf( __( '%s is too short. At least 4 characters are required.', 'fu-events-calendar' ), $field['label']));
        }

        if ( username_exists($value) ) {
          $errors->add('user_name', sprintf( __( 'That %s already exists.', 'fu-events-calendar' ), strtolower($field['label'])));
        }

        if ( !validate_username($value) ) {
          $errors->add('username_invalid', sprintf( __( 'The %s you entered is not valid.', 'fu-events-calendar' ), strtolower($field['label'])));
        }
      }

      return $value;
    }




    /**
     * Validate password
     * @since 2.0.3
     *
     * @param array     $field     field properties
     * @param WP_Error  $errors    WP Error object
     *
     * @return string   sanitized input value
     *
     */

    protected function validate_password($field, &$errors) {
      $value = $this->validate_default($field, $errors);

      if (!empty($value)) {
        if ( 8 > strlen($value) ) {
          $errors->add('password_length', sprintf( __( '%s is too short. At least 8 characters are required.', 'fu-events-calendar' ), $field['label']));
        }

        if ( !preg_match("#[0-9]+#", $value) ) {
          $errors->add('password_number', sprintf( __( '%s must include at least one number.', 'fu-events-calendar' ), $field['label']));
        }

        if ( !preg_match("#[a-zA-Z]+#", $value) ) {
          $errors->add('password_number', sprintf( __( '%s must include at least one letter.', 'fu-events-calendar' ), $field['label']));
        }
      }

      return $value;
    }
  }
}