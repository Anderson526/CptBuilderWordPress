<?php
/**
 * Dashboard view.
 *
 * @var int   $post_type_count
 * @var int   $taxonomy_count
 * @var int   $field_count
 * @var int   $relationship_count
 * @var array $recent
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1><?php esc_html_e( 'CPT Builder', 'cpt-builder' ); ?></h1>

	<div class="cptb-cards">
		<div class="cptb-card cptb-stat">
			<span class="cptb-stat-number"><?php echo (int) $post_type_count; ?></span>
			<span class="cptb-stat-label"><?php esc_html_e( 'Custom Post Types', 'cpt-builder' ); ?></span>
		</div>
		<div class="cptb-card cptb-stat">
			<span class="cptb-stat-number"><?php echo (int) $taxonomy_count; ?></span>
			<span class="cptb-stat-label"><?php esc_html_e( 'Taxonomies', 'cpt-builder' ); ?></span>
		</div>
		<div class="cptb-card cptb-stat">
			<span class="cptb-stat-number"><?php echo (int) $field_count; ?></span>
			<span class="cptb-stat-label"><?php esc_html_e( 'Fields', 'cpt-builder' ); ?></span>
		</div>
		<div class="cptb-card cptb-stat">
			<span class="cptb-stat-number"><?php echo (int) $relationship_count; ?></span>
			<span class="cptb-stat-label"><?php esc_html_e( 'Relationships', 'cpt-builder' ); ?></span>
		</div>
	</div>

	<div class="cptb-card">
		<h2><?php esc_html_e( 'Quick Actions', 'cpt-builder' ); ?></h2>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-post-types&action=add' ) ); ?>" class="button button-primary"><?php esc_html_e( '+ Create Post Type', 'cpt-builder' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-taxonomies&action=add' ) ); ?>" class="button"><?php esc_html_e( '+ Create Taxonomy', 'cpt-builder' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-fields&action=add' ) ); ?>" class="button"><?php esc_html_e( '+ Create Field Group', 'cpt-builder' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-relationships&action=add' ) ); ?>" class="button"><?php esc_html_e( '+ Create Relationship', 'cpt-builder' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-tools' ) ); ?>" class="button"><?php esc_html_e( 'Import Configuration', 'cpt-builder' ); ?></a>
		</p>
	</div>

	<div class="cptb-card">
		<h2><?php esc_html_e( 'Recent Structures', 'cpt-builder' ); ?></h2>
		<?php if ( empty( $recent ) ) : ?>
			<p><?php esc_html_e( 'No structures yet. Create your first Custom Post Type to get started.', 'cpt-builder' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'cpt-builder' ); ?></th>
						<th><?php esc_html_e( 'Key', 'cpt-builder' ); ?></th>
						<th><?php esc_html_e( 'Fields', 'cpt-builder' ); ?></th>
						<th><?php esc_html_e( 'Status', 'cpt-builder' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent as $row ) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=cptb-post-types&action=edit&key=' . $row['key'] ) ); ?>">
									<strong><?php echo esc_html( $row['label'] ); ?></strong>
								</a>
							</td>
							<td><code><?php echo esc_html( $row['key'] ); ?></code></td>
							<td><?php echo (int) $row['fields']; ?></td>
							<td><span class="cptb-badge cptb-badge-active"><?php esc_html_e( 'Active', 'cpt-builder' ); ?></span></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $settings['donation_url'] ) ) : ?>
		<div class="cptb-card cptb-support-card">
			<h2>☕ <?php esc_html_e( 'Enjoying CPT Builder?', 'cpt-builder' ); ?></h2>
			<p><?php esc_html_e( 'CPT Builder is free and open source. If it saves you development time, consider supporting its development.', 'cpt-builder' ); ?></p>
			<a href="<?php echo esc_url( $settings['donation_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary"><?php esc_html_e( 'Support the Developer', 'cpt-builder' ); ?></a>
		</div>
	<?php endif; ?>
</div>
