<?php
// Don't load directly
defined( 'WPINC' ) or die;

/**
 * Event Submission Form Metabox For Custom Fields
 * This is used to add a metabox to the event submission form to allow for custom
 * field input for user submitted events.
 *
 * Override this template in your own theme by creating a file at
 * [your-theme]/tribe-events/community/modules/custom.php
 *
 * @since  2.1
 * @version 4.5
 */
// Makes sure we dont even try when Pro is inactive
if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
	return;
}

$fields = apply_filters( 'tribe_events_community_custom_fields', tribe_get_option( 'custom-fields' ) );

if ( empty( $fields ) || ! is_array( $fields ) ) {
	return;
}

$post_id = get_the_ID();
?>

<div class="tribe-section tribe-section-custom-fields">
	<div class="tribe-section-header">
		<h3><?php esc_html_e( 'Additional Fields', 'tribe-events-community' ); ?></h3>
	</div>

	<?php
		/**
		 * Allow developers to hook and add content to the begining of this section
		 */
		do_action( 'tribe_events_community_section_before_custom_fields', $post_id );
	?>

	<table class="tribe-section-content">
		<colgroup>
			<col class="tribe-colgroup tribe-colgroup-label">
			<col class="tribe-colgroup tribe-colgroup-field">
		</colgroup>

		<?php foreach ( $fields as $field ) : ?>
			<?php tribe_get_template_part( 'community/modules/custom-item', null, $field ); ?>
		<?php endforeach; ?>
	</table>

	<?php
		/**
		 * Allow developers to hook and add content to the end of this section
		 */
		do_action( 'tribe_events_community_section_after_custom_fields', $post_id );
	?>
</div>