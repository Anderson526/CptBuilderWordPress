<?php
/**
 * Relationship add/edit form.
 *
 * @var array $item
 * @var bool  $is_new
 * @var array $post_types
 * @var array $rel_types
 */

defined( 'ABSPATH' ) || exit;

$cptb_pt_options = static function ( $selected ) use ( $post_types ) {
	echo '<option value="">' . esc_html__( '— Select —', 'cpt-builder' ) . '</option>';
	foreach ( $post_types as $pt_key => $pt ) {
		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $pt_key ),
			selected( $selected, $pt_key, false ),
			esc_html( $pt['plural'] )
		);
	}
	printf( '<option value="post" %s>%s</option>', selected( $selected, 'post', false ), esc_html__( 'Posts', 'cpt-builder' ) );
	printf( '<option value="page" %s>%s</option>', selected( $selected, 'page', false ), esc_html__( 'Pages', 'cpt-builder' ) );
};
?>
<div class="wrap cptb-wrap">
	<h1><?php $is_new ? esc_html_e( 'Create Relationship', 'cpt-builder' ) : esc_html_e( 'Edit Relationship', 'cpt-builder' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cptb-form">
		<?php wp_nonce_field( 'cptb_save_relationship' ); ?>
		<input type="hidden" name="action" value="cptb_save_relationship" />
		<input type="hidden" name="is_new" value="<?php echo $is_new ? '1' : '0'; ?>" />

		<div class="cptb-card">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cptb-rel-label"><?php esc_html_e( 'Label', 'cpt-builder' ); ?></label></th>
					<td><input required type="text" id="cptb-rel-label" name="label" class="regular-text" data-cptb-slug-source value="<?php echo esc_attr( $item['label'] ); ?>" placeholder="<?php esc_attr_e( 'Instructor', 'cpt-builder' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-rel-key"><?php esc_html_e( 'Key', 'cpt-builder' ); ?></label></th>
					<td>
						<input required type="text" id="cptb-rel-key" name="key" class="regular-text" data-cptb-slug-target pattern="[a-z0-9_\-]+" value="<?php echo esc_attr( $item['key'] ); ?>" <?php echo $is_new ? '' : 'readonly'; ?> />
						<p class="description"><?php esc_html_e( 'Stored as post meta key: cptb_rel_{key}', 'cpt-builder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-rel-from"><?php esc_html_e( 'From (post type that stores the relation)', 'cpt-builder' ); ?></label></th>
					<td><select required id="cptb-rel-from" name="from"><?php $cptb_pt_options( $item['from'] ); ?></select></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-rel-to"><?php esc_html_e( 'To (related post type)', 'cpt-builder' ); ?></label></th>
					<td><select required id="cptb-rel-to" name="to"><?php $cptb_pt_options( $item['to'] ); ?></select></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-rel-type"><?php esc_html_e( 'Type', 'cpt-builder' ); ?></label></th>
					<td>
						<select id="cptb-rel-type" name="type">
							<?php foreach ( $rel_types as $type => $type_label ) : ?>
								<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $item['type'], $type ); ?>><?php echo esc_html( $type_label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( '"One to Many" and "Many to Many" allow selecting multiple related items.', 'cpt-builder' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<p class="submit">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-relationships' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'cpt-builder' ); ?></a>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Relationship', 'cpt-builder' ); ?></button>
		</p>
	</form>
</div>
