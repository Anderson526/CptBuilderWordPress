<?php
/**
 * Support / Donations view.
 *
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1><?php esc_html_e( 'Support CPT Builder', 'cpt-builder' ); ?></h1>

	<div class="cptb-card cptb-support-card cptb-support-page">
		<h2>❤️ <?php esc_html_e( 'Support CPT Builder', 'cpt-builder' ); ?></h2>
		<p><?php esc_html_e( 'CPT Builder is free and open source. If it saves you development time, consider supporting its development.', 'cpt-builder' ); ?></p>
		<p><?php esc_html_e( 'Donations help cover hosting, development, maintenance, documentation, testing, support and future features.', 'cpt-builder' ); ?></p>

		<p>
			<?php if ( ! empty( $settings['donation_url'] ) ) : ?>
				<a href="<?php echo esc_url( $settings['donation_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero">☕ <?php esc_html_e( 'Buy me a coffee', 'cpt-builder' ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $settings['support_url'] ) ) : ?>
				<a href="<?php echo esc_url( $settings['support_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="button button-hero"><?php esc_html_e( 'Support Development', 'cpt-builder' ); ?></a>
			<?php endif; ?>
		</p>

		<?php if ( empty( $settings['donation_url'] ) && empty( $settings['support_url'] ) ) : ?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: settings page link. */
					esc_html__( 'Configure your donation links in %s.', 'cpt-builder' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=cptb-settings' ) ) . '">' . esc_html__( 'Settings', 'cpt-builder' ) . '</a>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</div>
