<?php
/**
 * Taxonomy add/edit form.
 *
 * @var array $item
 * @var bool  $is_new
 * @var array $post_types
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1><?php $is_new ? esc_html_e( 'Create Taxonomy', 'cpt-builder' ) : esc_html_e( 'Edit Taxonomy', 'cpt-builder' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cptb-form">
		<?php wp_nonce_field( 'cptb_save_taxonomy' ); ?>
		<input type="hidden" name="action" value="cptb_save_taxonomy" />
		<input type="hidden" name="is_new" value="<?php echo $is_new ? '1' : '0'; ?>" />

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Basic Information', 'cpt-builder' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cptb-tax-plural"><?php esc_html_e( 'Plural Name', 'cpt-builder' ); ?></label></th>
					<td><input required type="text" id="cptb-tax-plural" name="plural" class="regular-text" data-cptb-slug-source value="<?php echo esc_attr( $item['plural'] ); ?>" placeholder="<?php esc_attr_e( 'Course Categories', 'cpt-builder' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-tax-singular"><?php esc_html_e( 'Singular Name', 'cpt-builder' ); ?></label></th>
					<td><input required type="text" id="cptb-tax-singular" name="singular" class="regular-text" value="<?php echo esc_attr( $item['singular'] ); ?>" placeholder="<?php esc_attr_e( 'Course Category', 'cpt-builder' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-tax-key"><?php esc_html_e( 'Taxonomy Key', 'cpt-builder' ); ?></label></th>
					<td>
						<input required type="text" id="cptb-tax-key" name="key" class="regular-text" data-cptb-slug-target maxlength="32" pattern="[a-z0-9_\-]+" value="<?php echo esc_attr( $item['key'] ); ?>" <?php echo $is_new ? '' : 'readonly'; ?> />
						<p class="description"><?php esc_html_e( 'Lowercase letters, numbers, underscores. Max 32 characters.', 'cpt-builder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-tax-rewrite"><?php esc_html_e( 'Rewrite Slug', 'cpt-builder' ); ?></label></th>
					<td><input type="text" id="cptb-tax-rewrite" name="rewrite_slug" class="regular-text" value="<?php echo esc_attr( $item['rewrite_slug'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Type', 'cpt-builder' ); ?></th>
					<td>
						<label class="cptb-check"><input type="radio" name="hierarchical" value="1" <?php checked( ! empty( $item['hierarchical'] ) ); ?> /> <?php esc_html_e( 'Hierarchical (like categories)', 'cpt-builder' ); ?></label>
						<label class="cptb-check"><input type="radio" name="hierarchical" value="" <?php checked( empty( $item['hierarchical'] ) ); ?> /> <?php esc_html_e( 'Non-Hierarchical (like tags)', 'cpt-builder' ); ?></label>
					</td>
				</tr>
			</table>
		</div>

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Attach to', 'cpt-builder' ); ?></h2>
			<?php if ( empty( $post_types ) ) : ?>
				<p><?php esc_html_e( 'No custom post types available. Create a post type first.', 'cpt-builder' ); ?></p>
			<?php endif; ?>
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
		</div>

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Features', 'cpt-builder' ); ?></h2>
			<?php
			$features = array(
				'public'            => __( 'Public', 'cpt-builder' ),
				'show_ui'           => __( 'Show UI', 'cpt-builder' ),
				'show_admin_column' => __( 'Show Admin Column', 'cpt-builder' ),
				'show_in_rest'      => __( 'Show in REST API', 'cpt-builder' ),
			);
			foreach ( $features as $field => $label ) :
				?>
				<label class="cptb-check">
					<input type="checkbox" name="<?php echo esc_attr( $field ); ?>" value="1" <?php checked( ! empty( $item[ $field ] ) ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<p class="submit">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-taxonomies' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'cpt-builder' ); ?></a>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Taxonomy', 'cpt-builder' ); ?></button>
		</p>
	</form>
</div>
