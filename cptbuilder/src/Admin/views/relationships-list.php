<?php
/**
 * Relationships list view.
 *
 * @var array $items
 * @var array $rel_types
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Relationships', 'cpt-builder' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-relationships&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'cpt-builder' ); ?></a>
	<hr class="wp-header-end" />

	<?php if ( empty( $items ) ) : ?>
		<div class="cptb-card"><p><?php esc_html_e( 'No relationships configured yet.', 'cpt-builder' ); ?></p></div>
	<?php else : ?>
		<table class="widefat striped cptb-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Label', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'From', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'To', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Type', 'cpt-builder' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'cpt-builder' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $key => $item ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $item['label'] ); ?></strong></td>
						<td><code><?php echo esc_html( $item['from'] ); ?></code></td>
						<td><code><?php echo esc_html( $item['to'] ); ?></code></td>
						<td><?php echo esc_html( $rel_types[ $item['type'] ] ?? $item['type'] ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-relationships&action=edit&key=' . $key ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'cpt-builder' ); ?></a>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cptb_delete_relationship&key=' . $key ), 'cptb_delete_relationship' ) ); ?>" class="button button-small cptb-delete"><?php esc_html_e( 'Delete', 'cpt-builder' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
