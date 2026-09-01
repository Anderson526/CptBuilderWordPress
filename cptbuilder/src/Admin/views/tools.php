<?php
/**
 * Tools view: Export, Import, Code Generator, Debug.
 *
 * @var string $generated
 * @var bool   $developer_mode
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1><?php esc_html_e( 'Tools', 'cpt-builder' ); ?></h1>

	<div class="cptb-card">
		<h2><?php esc_html_e( 'Export Configuration', 'cpt-builder' ); ?></h2>
		<p><?php esc_html_e( 'Download all post types, taxonomies, field groups and relationships as a portable JSON file.', 'cpt-builder' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'cptb_export' ); ?>
			<input type="hidden" name="action" value="cptb_export" />
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Export JSON', 'cpt-builder' ); ?></button>
		</form>
	</div>

	<div class="cptb-card">
		<h2><?php esc_html_e( 'Import Configuration', 'cpt-builder' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'cptb_import' ); ?>
			<input type="hidden" name="action" value="cptb_import" />
			<p><input type="file" name="cptb_import_file" accept=".json,application/json" required /></p>
			<p>
				<strong><?php esc_html_e( 'If an item already exists:', 'cpt-builder' ); ?></strong><br />
				<label class="cptb-check"><input type="radio" name="conflict_mode" value="skip" checked /> <?php esc_html_e( 'Skip it', 'cpt-builder' ); ?></label>
				<label class="cptb-check"><input type="radio" name="conflict_mode" value="update" /> <?php esc_html_e( 'Update it', 'cpt-builder' ); ?></label>
			</p>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Import', 'cpt-builder' ); ?></button>
		</form>
	</div>

	<div class="cptb-card">
		<h2><?php esc_html_e( 'Code Generator', 'cpt-builder' ); ?></h2>
		<p><?php esc_html_e( 'Generate portable PHP code for all configured post types and taxonomies.', 'cpt-builder' ); ?></p>
		<p>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=cptb-tools&cptb_generate=1' ), 'cptb_generate_code' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Generate PHP', 'cpt-builder' ); ?></a>
		</p>
		<?php if ( $generated ) : ?>
			<p><button type="button" class="button" id="cptb-copy-code" data-copied="<?php esc_attr_e( 'Copied!', 'cpt-builder' ); ?>"><?php esc_html_e( 'Copy', 'cpt-builder' ); ?></button></p>
			<textarea id="cptb-generated-code" class="large-text code" rows="20" readonly><?php echo esc_textarea( $generated ); ?></textarea>
		<?php endif; ?>
	</div>

	<?php if ( $developer_mode ) : ?>
		<div class="cptb-card">
			<h2><?php esc_html_e( 'Debug (Developer Mode)', 'cpt-builder' ); ?></h2>

			<h3><?php esc_html_e( 'Registered Custom Post Types', 'cpt-builder' ); ?></h3>
			<ul class="cptb-debug-list">
				<?php foreach ( get_post_types( array( '_builtin' => false ), 'objects' ) as $pt ) : ?>
					<li>✓ <code><?php echo esc_html( $pt->name ); ?></code> — <?php echo esc_html( $pt->label ); ?></li>
				<?php endforeach; ?>
			</ul>

			<h3><?php esc_html_e( 'Registered Custom Taxonomies', 'cpt-builder' ); ?></h3>
			<ul class="cptb-debug-list">
				<?php foreach ( get_taxonomies( array( '_builtin' => false ), 'objects' ) as $tax ) : ?>
					<li>✓ <code><?php echo esc_html( $tax->name ); ?></code> — <?php echo esc_html( $tax->label ); ?></li>
				<?php endforeach; ?>
			</ul>

			<h3><?php esc_html_e( 'Internal REST API', 'cpt-builder' ); ?></h3>
			<ul class="cptb-debug-list">
				<li><code><?php echo esc_html( rest_url( 'cpt-builder/v1/post-types' ) ); ?></code></li>
				<li><code><?php echo esc_html( rest_url( 'cpt-builder/v1/taxonomies' ) ); ?></code></li>
				<li><code><?php echo esc_html( rest_url( 'cpt-builder/v1/field-groups' ) ); ?></code></li>
				<li><code><?php echo esc_html( rest_url( 'cpt-builder/v1/relationships' ) ); ?></code></li>
				<li><code><?php echo esc_html( rest_url( 'cpt-builder/v1/export' ) ); ?></code></li>
			</ul>
		</div>
	<?php endif; ?>
</div>
