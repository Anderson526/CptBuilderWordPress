<?php
/**
 * Field group add/edit form with dynamic field rows.
 *
 * @var array $item
 * @var bool  $is_new
 * @var array $post_types
 * @var array $field_types
 * @var array $field_defaults
 */

defined( 'ABSPATH' ) || exit;

$cptb_render_row = static function ( $index, array $field, array $field_types, array $post_types ) use ( $field_defaults ) {
	$field = wp_parse_args( $field, $field_defaults );
	$n     = static function ( $prop ) use ( $index ) {
		return 'fields[' . $index . '][' . $prop . ']';
	};
	?>
	<div class="cptb-field-row">
		<div class="cptb-field-row-head">
			<span class="cptb-field-row-title"><?php echo esc_html( $field['label'] ? $field['label'] : __( 'New Field', 'cpt-builder' ) ); ?></span>
			<button type="button" class="button-link cptb-remove-field"><?php esc_html_e( 'Remove', 'cpt-builder' ); ?></button>
		</div>
		<div class="cptb-field-row-body">
			<p>
				<label><?php esc_html_e( 'Label', 'cpt-builder' ); ?><br />
				<input type="text" name="<?php echo esc_attr( $n( 'label' ) ); ?>" value="<?php echo esc_attr( $field['label'] ); ?>" class="regular-text cptb-field-label" /></label>
			</p>
			<p>
				<label><?php esc_html_e( 'Field Key (meta key)', 'cpt-builder' ); ?><br />
				<input type="text" name="<?php echo esc_attr( $n( 'key' ) ); ?>" value="<?php echo esc_attr( $field['key'] ); ?>" class="regular-text" pattern="[a-z0-9_\-]+" /></label>
			</p>
			<p>
				<label><?php esc_html_e( 'Type', 'cpt-builder' ); ?><br />
				<select name="<?php echo esc_attr( $n( 'type' ) ); ?>" class="cptb-field-type">
					<?php foreach ( $field_types as $type => $type_label ) : ?>
						<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $field['type'], $type ); ?>><?php echo esc_html( $type_label ); ?></option>
					<?php endforeach; ?>
				</select></label>
			</p>
			<p>
				<label><?php esc_html_e( 'Description', 'cpt-builder' ); ?><br />
				<input type="text" name="<?php echo esc_attr( $n( 'description' ) ); ?>" value="<?php echo esc_attr( $field['description'] ); ?>" class="regular-text" /></label>
			</p>
			<p>
				<label><?php esc_html_e( 'Placeholder', 'cpt-builder' ); ?><br />
				<input type="text" name="<?php echo esc_attr( $n( 'placeholder' ) ); ?>" value="<?php echo esc_attr( $field['placeholder'] ); ?>" class="regular-text" /></label>
			</p>
			<p>
				<label><?php esc_html_e( 'Default Value', 'cpt-builder' ); ?><br />
				<input type="text" name="<?php echo esc_attr( $n( 'default' ) ); ?>" value="<?php echo esc_attr( $field['default'] ); ?>" class="regular-text" /></label>
			</p>
			<p class="cptb-opt cptb-opt-select">
				<label><?php esc_html_e( 'Options (one per line: value : Label)', 'cpt-builder' ); ?><br />
				<textarea name="<?php echo esc_attr( $n( 'options' ) ); ?>" rows="3" class="regular-text"><?php echo esc_textarea( $field['options'] ); ?></textarea></label>
			</p>
			<p class="cptb-opt cptb-opt-number">
				<label><?php esc_html_e( 'Min', 'cpt-builder' ); ?>
				<input type="number" step="any" name="<?php echo esc_attr( $n( 'min' ) ); ?>" value="<?php echo esc_attr( $field['min'] ); ?>" class="small-text" /></label>
				<label><?php esc_html_e( 'Max', 'cpt-builder' ); ?>
				<input type="number" step="any" name="<?php echo esc_attr( $n( 'max' ) ); ?>" value="<?php echo esc_attr( $field['max'] ); ?>" class="small-text" /></label>
			</p>
			<p class="cptb-opt cptb-opt-relationship">
				<label><?php esc_html_e( 'Related Post Type', 'cpt-builder' ); ?><br />
				<select name="<?php echo esc_attr( $n( 'rel_post_type' ) ); ?>">
					<option value=""><?php esc_html_e( '— Select —', 'cpt-builder' ); ?></option>
					<?php foreach ( $post_types as $pt_key => $pt ) : ?>
						<option value="<?php echo esc_attr( $pt_key ); ?>" <?php selected( $field['rel_post_type'], $pt_key ); ?>><?php echo esc_html( $pt['plural'] ); ?></option>
					<?php endforeach; ?>
					<option value="post" <?php selected( $field['rel_post_type'], 'post' ); ?>><?php esc_html_e( 'Posts', 'cpt-builder' ); ?></option>
					<option value="page" <?php selected( $field['rel_post_type'], 'page' ); ?>><?php esc_html_e( 'Pages', 'cpt-builder' ); ?></option>
				</select></label>
				<label class="cptb-check">
					<input type="checkbox" name="<?php echo esc_attr( $n( 'multiple' ) ); ?>" value="1" <?php checked( ! empty( $field['multiple'] ) ); ?> />
					<?php esc_html_e( 'Allow multiple', 'cpt-builder' ); ?>
				</label>
			</p>
			<p>
				<label class="cptb-check">
					<input type="checkbox" name="<?php echo esc_attr( $n( 'required' ) ); ?>" value="1" <?php checked( ! empty( $field['required'] ) ); ?> />
					<?php esc_html_e( 'Required', 'cpt-builder' ); ?>
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'REST Visibility', 'cpt-builder' ); ?><br />
				<select name="<?php echo esc_attr( $n( 'rest' ) ); ?>">
					<option value="hidden" <?php selected( $field['rest'], 'hidden' ); ?>><?php esc_html_e( 'Hidden', 'cpt-builder' ); ?></option>
					<option value="read" <?php selected( $field['rest'], 'read' ); ?>><?php esc_html_e( 'Read', 'cpt-builder' ); ?></option>
					<option value="write" <?php selected( $field['rest'], 'write' ); ?>><?php esc_html_e( 'Read / Write', 'cpt-builder' ); ?></option>
				</select></label>
			</p>
		</div>
	</div>
	<?php
};
?>
<div class="wrap cptb-wrap">
	<h1><?php $is_new ? esc_html_e( 'Create Field Group', 'cpt-builder' ) : esc_html_e( 'Edit Field Group', 'cpt-builder' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cptb-form">
		<?php wp_nonce_field( 'cptb_save_field_group' ); ?>
		<input type="hidden" name="action" value="cptb_save_field_group" />
		<input type="hidden" name="is_new" value="<?php echo $is_new ? '1' : '0'; ?>" />

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Group', 'cpt-builder' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cptb-group-title"><?php esc_html_e( 'Title', 'cpt-builder' ); ?></label></th>
					<td><input required type="text" id="cptb-group-title" name="title" class="regular-text" data-cptb-slug-source value="<?php echo esc_attr( $item['title'] ); ?>" placeholder="<?php esc_attr_e( 'Course Information', 'cpt-builder' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-group-key"><?php esc_html_e( 'Group Key', 'cpt-builder' ); ?></label></th>
					<td><input required type="text" id="cptb-group-key" name="key" class="regular-text" data-cptb-slug-target pattern="[a-z0-9_\-]+" value="<?php echo esc_attr( $item['key'] ); ?>" <?php echo $is_new ? '' : 'readonly'; ?> /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Attach to Post Types', 'cpt-builder' ); ?></th>
					<td>
						<?php foreach ( $post_types as $pt_key => $pt ) : ?>
							<label class="cptb-check">
								<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $pt_key ); ?>" <?php checked( in_array( $pt_key, (array) $item['post_types'], true ) ); ?> />
								<?php echo esc_html( $pt['plural'] ); ?>
							</label>
						<?php endforeach; ?>
						<label class="cptb-check">
							<input type="checkbox" name="post_types[]" value="post" <?php checked( in_array( 'post', (array) $item['post_types'], true ) ); ?> />
							<?php esc_html_e( 'Posts', 'cpt-builder' ); ?>
						</label>
						<label class="cptb-check">
							<input type="checkbox" name="post_types[]" value="page" <?php checked( in_array( 'page', (array) $item['post_types'], true ) ); ?> />
							<?php esc_html_e( 'Pages', 'cpt-builder' ); ?>
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Fields', 'cpt-builder' ); ?></h2>

			<div id="cptb-field-rows" data-next-index="<?php echo count( (array) $item['fields'] ); ?>">
				<?php
				foreach ( array_values( (array) $item['fields'] ) as $i => $field ) {
					$cptb_render_row( $i, $field, $field_types, $post_types );
				}
				?>
			</div>

			<template id="cptb-field-row-template">
				<?php $cptb_render_row( '__INDEX__', array(), $field_types, $post_types ); ?>
			</template>

			<p><button type="button" class="button" id="cptb-add-field"><?php esc_html_e( '+ Add Field', 'cpt-builder' ); ?></button></p>
		</div>

		<p class="submit">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-fields' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'cpt-builder' ); ?></a>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Field Group', 'cpt-builder' ); ?></button>
		</p>
	</form>
</div>
