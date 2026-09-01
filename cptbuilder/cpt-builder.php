<?php
/**
 * Plugin Name:       CPT Builder
 * Description:       Visual Custom Post Type, Taxonomy, Fields & Relationships Builder for WordPress.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Anderson D Chila P
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cpt-builder
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'CPTB_VERSION', '0.1.0' );
define( 'CPTB_FILE', __FILE__ );
define( 'CPTB_DIR', plugin_dir_path( __FILE__ ) );
define( 'CPTB_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(
	static function ( $class ) {
		if ( 0 !== strpos( $class, 'CPTBuilder\\' ) ) {
			return;
		}
		$relative = substr( $class, strlen( 'CPTBuilder\\' ) );
		$path     = CPTB_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
		if ( file_exists( $path ) ) {
			require $path;
		}
	}
);

require_once CPTB_DIR . 'api.php';

register_activation_hook(
	__FILE__,
	static function () {
		update_option( 'cptb_flush_needed', 1 );
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

add_action(
	'plugins_loaded',
	static function () {
		\CPTBuilder\Core\Plugin::instance()->boot();
	}
);
