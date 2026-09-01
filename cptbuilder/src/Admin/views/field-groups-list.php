<?php
/**
 * Field groups list view.
 *
 * @var array $items
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Field Groups', 'cpt-builder' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-fields&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'cpt-builder' ); ?></a>
	<hr class="wp-header-end" />

	<?php if ( empty( $items ) ) : ?>
		<div class="cptb-card"><p><?php esc_html_e( 'No field groups configured yet.', 'cpt-builder' ); ?></p></div>
	<?php else : ?>
		<table class="widefat striped cptb-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Title', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Key', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Fields', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Post Types', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'cpt-builder' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $key => $item ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $item['title'] ); ?></strong></td>
						<td><code><?php echo esc_html( $key ); ?></code></td>
						<td><?php echo count( (array) ( $item['fields'] ?? array() ) ); ?></td>
						<td><?php echo esc_html( implode( ', ', (array) ( $item['post_types'] ?? array() ) ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-fields&action=edit&key=' . $key ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'cpt-builder' ); ?></a>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cptb_delete_field_group&key=' . $key ), 'cptb_delete_field_group' ) ); ?>" class="button button-small cptb-delete"><?php esc_html_e( 'Delete', 'cpt-builder' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
