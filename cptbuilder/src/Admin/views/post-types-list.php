<?php
/**
 * Post types list view.
 *
 * @var array                                   $items
 * @var \CPTBuilder\Fields\FieldRepository      $field_repo
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Post Types', 'cpt-builder' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-post-types&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'cpt-builder' ); ?></a>
	<hr class="wp-header-end" />

	<?php if ( empty( $items ) ) : ?>
		<div class="cptb-card"><p><?php esc_html_e( 'No custom post types configured yet.', 'cpt-builder' ); ?></p></div>
	<?php else : ?>
		<table class="widefat striped cptb-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Key', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Fields', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'REST', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'cpt-builder' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $key => $item ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $item['plural'] ); ?></strong></td>
						<td><code><?php echo esc_html( $key ); ?></code></td>
						<td><?php echo (int) $field_repo->count_fields( $key ); ?></td>
						<td>
							<?php if ( ! empty( $item['show_in_rest'] ) ) : ?>
								<code>/wp-json/wp/v2/<?php echo esc_html( ! empty( $item['rest_base'] ) ? $item['rest_base'] : $key ); ?></code>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-post-types&action=edit&key=' . $key ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'cpt-builder' ); ?></a>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cptb_delete_post_type&key=' . $key ), 'cptb_delete_post_type' ) ); ?>" class="button button-small cptb-delete"><?php esc_html_e( 'Delete', 'cpt-builder' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
