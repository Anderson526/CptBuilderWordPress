<?php
/**
 * Settings view.
 *
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cptb-wrap">
	<h1><?php esc_html_e( 'Settings', 'cpt-builder' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cptb-form">
		<?php wp_nonce_field( 'cptb_save_settings' ); ?>
		<input type="hidden" name="action" value="cptb_save_settings" />

		<div class="cptb-card">
			<h2><?php esc_html_e( 'General', 'cpt-builder' ); ?></h2>
			<label class="cptb-check">
				<input type="checkbox" name="developer_mode" value="1" <?php checked( ! empty( $settings['developer_mode'] ) ); ?> />
				<?php esc_html_e( 'Developer Mode (show debug information in Tools)', 'cpt-builder' ); ?>
			</label>
			<label class="cptb-check">
				<input type="checkbox" name="delete_data_on_uninstall" value="1" <?php checked( ! empty( $settings['delete_data_on_uninstall'] ) ); ?> />
				<?php esc_html_e( 'Delete all plugin configuration when the plugin is uninstalled', 'cpt-builder' ); ?>
			</label>
		</div>

		<div class="cptb-card">
			<h2><?php esc_html_e( 'Donations', 'cpt-builder' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cptb-donation-url"><?php esc_html_e( 'Donation URL', 'cpt-builder' ); ?></label></th>
					<td><input type="url" id="cptb-donation-url" name="donation_url" class="regular-text" value="<?php echo esc_attr( $settings['donation_url'] ); ?>" placeholder="https://buymeacoffee.com/..." /></td>
				</tr>
				<tr>
					<th scope="row"><label for="cptb-support-url"><?php esc_html_e( 'Support URL', 'cpt-builder' ); ?></label></th>
					<td><input type="url" id="cptb-support-url" name="support_url" class="regular-text" value="<?php echo esc_attr( $settings['support_url'] ); ?>" placeholder="https://github.com/sponsors/..." /></td>
				</tr>
			</table>
		</div>

		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'cpt-builder' ); ?></button>
		</p>
	</form>
</div>
