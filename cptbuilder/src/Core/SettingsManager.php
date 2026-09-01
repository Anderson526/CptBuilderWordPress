<?php
namespace CPTBuilder\Core;

defined( 'ABSPATH' ) || exit;

class SettingsManager {

	const OPTION = 'cptb_settings';

	public static function defaults(): array {
		return array(
			'developer_mode'           => false,
			'delete_data_on_uninstall' => false,
			'donation_url'             => '',
			'support_url'              => '',
		);
	}

	public function all(): array {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	/**
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public function get( string $key ) {
		$all = $this->all();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	public function save( array $settings ): void {
		$defaults = self::defaults();
		$clean    = array(
			'developer_mode'           => ! empty( $settings['developer_mode'] ),
			'delete_data_on_uninstall' => ! empty( $settings['delete_data_on_uninstall'] ),
			'donation_url'             => isset( $settings['donation_url'] ) ? esc_url_raw( $settings['donation_url'] ) : $defaults['donation_url'],
			'support_url'              => isset( $settings['support_url'] ) ? esc_url_raw( $settings['support_url'] ) : $defaults['support_url'],
		);
		update_option( self::OPTION, $clean );
	}
}
