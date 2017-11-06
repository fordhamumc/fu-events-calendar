<?php
// Don't load directly
defined( 'WPINC' ) or die;

/**
 * Event Submission Form Custom Field
 * This is used to add a custom input to the event submission form
 * custom fields metabox for user submitted events.
 *
 * @author Michael Foley
 *
 */

	if ( ! isset( $data ) || empty( $data ) || ! is_array( $data ) ) {
		return;
	}
	$field = $data;

	$value = get_post_meta( get_the_ID(), $field['name'], true );
	$value = apply_filters( 'tribe_events_community_custom_field_value', $value, $field['name'], get_the_ID() );
	if ( 'checkbox' === $field['type'] && ! is_array( $value ) ) {
		$value = array_map(	'trim', explode( '|', $value ) );
		$value = array_filter( $value );
		$value = array_map(	'esc_attr', $value );
	} else {
		if ( 'textarea' !== $field['type'] ) {
			$value = esc_attr( trim( $value ) );
		} else {
			$value = esc_textarea( trim( stripslashes( $value ) ) );
		}
	}
	$field_id = sanitize_html_class( 'tribe_custom_' . $field['label'] );
	if ( in_array( $field['type'], array( 'radio', 'dropdown', 'checkbox' ) ) ) {
		$field['name'] = stripslashes( $field['name'] );
	}
	$field['name'] = esc_attr( $field['name'] );

	// Configure options
	$options = array();

	// Add Blank None option for Radio and Dropdown
	if ( in_array( $field['type'], array( 'radio', 'dropdown' ) ) ) {
		$options[''] = __( 'None', 'tribe-events-community' );
	}

	// Options defined in the panel
	$options = array_merge( $options, explode( "\n", $field['values'] ) );
	$options = array_map( 'trim', $options );
	$options = array_map( 'esc_attr', $options );

	$field_classes[] = 'tribe-section-content-row';
	$field_classes[] = sanitize_html_class( 'tribe-field-type-' . $field['type'] );
?>
<tr class="<?php echo implode( ' ', $field_classes ); ?>">
	<td class="tribe-section-content-label">
		<?php tribe_community_events_field_label( $field['name'], sprintf( _x( '%s:', 'custom field label', 'tribe-events-community' ), $field['label'] ) ); ?>
	</td>
	<td class="tribe-section-content-field">
		<?php if ( 'text' === $field['type'] ) : ?>
			<input
				type="text"
				id="<?php echo $field_id; ?>"
				name="<?php echo $field['name']; ?>"
				value="<?php echo $value; ?>"
			>
		<?php elseif ( 'url' === $field['type'] ) : ?>
			<input
				type="url"
				id="<?php echo $field_id; ?>"
				name="<?php echo $field['name']; ?>"
				value="<?php echo $value; ?>"
				placeholder="Enter URL for <?php echo str_replace('link','',strtolower($field['label'])); ?>"
			>
		<?php elseif ( 'radio' === $field['type'] ) : ?>
			<?php foreach ( $options as $option ) : ?>
				<label>
					<input
						type="radio"
						name="<?php echo $field['name']; ?>"
						value="<?php echo $option; ?>"
						<?php checked( $value, $option ); ?>
					>
					<?php echo stripslashes( $option ); ?>
				</label>
			<?php endforeach; ?>
		<?php elseif ( 'checkbox' === $field['type'] ) : ?>
			<?php foreach ( $options as $option ) : ?>
				<label>
					<input
						type="checkbox"
						value="<?php echo $option; ?>"
						<?php checked( in_array( $option, $value ) ) ?>
						name="<?php echo $field['name']; ?>[]"
					>
					<?php echo stripslashes( $option ); ?>
				</label>
			<?php endforeach; ?>
		<?php elseif ( 'dropdown' === $field['type'] ) : ?>
			<select name="<?php echo $field['name']; ?>">
				<?php foreach ( $options as $option ) : ?>
					<option
						value="<?php echo $option; ?>"
						<?php selected( $value, $option ); ?>
					>
						<?php echo stripslashes( $option ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( 'textarea' === $field['type'] ) : ?>
			<textarea
				id="<?php echo $field_id; ?>"
				name="<?php echo $field['name']; ?>"
			><?php echo $value; ?></textarea>
		<?php endif; ?>
	</td>
</tr>