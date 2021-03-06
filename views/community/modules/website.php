<?php
// Don't load directly
defined( 'WPINC' ) or die;

/**
 * Event Submission Form Website Block
 * Renders the website fields in the submission form.
 *
 * Override this template in your own theme by creating a file at
 * [your-theme]/tribe-events/community/modules/website.php
 *
 * @since  3.1
 * @version 4.5
 */

// If posting back, then use $POST values
if ( ! $_POST ) {
	$event_url = function_exists( 'tribe_get_event_website_url' ) ? tribe_get_event_website_url() : tribe_community_get_event_website_url();
} else {
	$event_url = isset( $_POST['EventURL'] ) ? esc_attr( $_POST['EventURL'] ) : '';
}

?>

<div class="tribe-section tribe-section-website">
	<div class="tribe-section-header">
		<h3><?php printf( __( '%s Website', 'tribe-events-community' ), tribe_get_event_label_singular() ); ?></h3>
	</div>

	<?php
	/**
	 * Allow developers to hook and add content to the beginning of this section
	 */
	do_action( 'tribe_events_community_section_before_website' );
	?>

	<table class="tribe-section-content">
		<colgroup>
			<col class="tribe-colgroup tribe-colgroup-label">
			<col class="tribe-colgroup tribe-colgroup-field">
		</colgroup>

		<tr class="tribe-section-content-row">
			<td class="tribe-section-content-label">
				<?php tribe_community_events_field_label( 'EventURL', __( 'Event Website:', 'tribe-events-community' ) ); ?>
			</td>
			<td class="tribe-section-content-field">
				<input type="text" id="EventURL" name="EventURL" size="25" value="<?php echo esc_url( $event_url ); ?>" placeholder="<?php esc_attr_e( 'Enter URL for more event information', 'tribe-events-community' ); ?>" />
			</td>
		</tr>
		<?php do_action( 'tribe_events_community_section_after_website_row' ); ?>
	</table>

	<?php
	/**
	 * Allow developers to hook and add content to the end of this section
	 */
	do_action( 'tribe_events_community_section_after_website' );
	?>
</div>
