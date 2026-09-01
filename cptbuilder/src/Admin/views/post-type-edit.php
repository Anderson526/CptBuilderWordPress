<?php
/**
 * Post type add/edit form.
 *
 * @var array $item
 * @var bool  $is_new
 * @var array $supports_options
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1><?php $is_new ? esc_html_e( 'Create Post Type', 'cpt-builder' ) : esc_html_e( 'Edit Post Type', 'cpt-builder' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cptb-form">
		<?php wp_nonce_field( 'cptb_save_post_type' ); ?>
		<input type="hidden" name="action" value="cptb_save_post_type" />
		<input type="hidden" name="is_new" value="<?php echo $is_new ? '1' : '0'; ?>" />

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Basic Information', 'cpt-builder' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cptb-plural"><?php esc_html_e( 'Plural Name', 'cpt-builder' ); ?></label></th>
					<td><input required type="text" id="cptb-plural" name="plural" class="regular-text" data-cptb-slug-source value="<?php echo esc_attr( $item['plural'] ); ?>" placeholder="<?php esc_attr_e( 'Courses', 'cpt-builder' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-singular"><?php esc_html_e( 'Singular Name', 'cpt-builder' ); ?></label></th>
					<td><input required type="text" id="cptb-singular" name="singular" class="regular-text" value="<?php echo esc_attr( $item['singular'] ); ?>" placeholder="<?php esc_attr_e( 'Course', 'cpt-builder' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-key"><?php esc_html_e( 'Post Type Key', 'cpt-builder' ); ?></label></th>
					<td>
						<input required type="text" id="cptb-key" name="key" class="regular-text" data-cptb-slug-target maxlength="20" pattern="[a-z0-9_\-]+" value="<?php echo esc_attr( $item['key'] ); ?>" <?php echo $is_new ? '' : 'readonly'; ?> />
						<p class="description"><?php esc_html_e( 'Lowercase letters, numbers, underscores. Max 20 characters.', 'cpt-builder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-description"><?php esc_html_e( 'Description', 'cpt-builder' ); ?></label></th>
					<td><textarea id="cptb-description" name="description" class="large-text" rows="2"><?php echo esc_textarea( $item['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-menu-icon"><?php esc_html_e( 'Menu Icon', 'cpt-builder' ); ?></label></th>
					<td>
						<input type="text" id="cptb-menu-icon" name="menu_icon" class="regular-text" value="<?php echo esc_attr( $item['menu_icon'] ); ?>" placeholder="dashicons-admin-post" />
						<p class="description"><?php esc_html_e( 'A Dashicons class or a full icon URL.', 'cpt-builder' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-menu-position"><?php esc_html_e( 'Menu Position', 'cpt-builder' ); ?></label></th>
					<td><input type="number" id="cptb-menu-position" name="menu_position" class="small-text" value="<?php echo esc_attr( $item['menu_position'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-rewrite-slug"><?php esc_html_e( 'Rewrite Slug', 'cpt-builder' ); ?></label></th>
					<td><input type="text" id="cptb-rewrite-slug" name="rewrite_slug" class="regular-text" value="<?php echo esc_attr( $item['rewrite_slug'] ); ?>" placeholder="<?php echo esc_attr( $item['key'] ? $item['key'] : 'course' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-rest-base"><?php esc_html_e( 'REST Base', 'cpt-builder' ); ?></label></th>
					<td><input type="text" id="cptb-rest-base" name="rest_base" class="regular-text" value="<?php echo esc_attr( $item['rest_base'] ); ?>" placeholder="<?php echo esc_attr( $item['key'] ? $item['key'] : 'courses' ); ?>" /></td>
				</tr>
			</table>
		</div>

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Visibility', 'cpt-builder' ); ?></h2>
			<?php
			$visibility = array(
				'public'              => __( 'Public', 'cpt-builder' ),
				'publicly_queryable'  => __( 'Publicly Queryable', 'cpt-builder' ),
				'show_ui'             => __( 'Show UI', 'cpt-builder' ),
				'show_in_menu'        => __( 'Show in Admin Menu', 'cpt-builder' ),
				'show_in_rest'        => __( 'Show in REST API', 'cpt-builder' ),
				'has_archive'         => __( 'Has Archive', 'cpt-builder' ),
				'hierarchical'        => __( 'Hierarchical', 'cpt-builder' ),
				'exclude_from_search' => __( 'Exclude From Search', 'cpt-builder' ),
			);
			foreach ( $visibility as $field => $label ) :
				?>
				<label class="cptb-check">
					<input type="checkbox" name="<?php echo esc_attr( $field ); ?>" value="1" <?php checked( ! empty( $item[ $field ] ) ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Supports', 'cpt-builder' ); ?></h2>
			<?php foreach ( $supports_options as $support => $label ) : ?>
				<label class="cptb-check">
					<input type="checkbox" name="supports[]" value="<?php echo esc_attr( $support ); ?>" <?php checked( in_array( $support, (array) $item['supports'], true ) ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<p class="submit">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-post-types' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'cpt-builder' ); ?></a>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Post Type', 'cpt-builder' ); ?></button>
		</p>
	</form>
</div>
