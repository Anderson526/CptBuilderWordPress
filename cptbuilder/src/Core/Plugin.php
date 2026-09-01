<?php
namespace CPTBuilder\Core;

use CPTBuilder\Admin\AdminManager;
use CPTBuilder\Admin\FormHandler;
use CPTBuilder\Fields\FieldManager;
use CPTBuilder\PostTypes\PostTypeManager;
use CPTBuilder\Relationships\RelationshipManager;
use CPTBuilder\REST\RestManager;
use CPTBuilder\Taxonomies\TaxonomyManager;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin bootstrap and lightweight service container.
 */
final class Plugin {

	/** @var Plugin|null */
	private static $instance = null;

	/** @var array<string,object> */
	private $services = array();

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Resolve (and memoize) a service by class name.
	 *
	 * @param string $class Fully qualified class name.
	 * @return object
	 */
	public function get( string $class ) {
		if ( ! isset( $this->services[ $class ] ) ) {
			$this->services[ $class ] = new $class( $this );
		}
		return $this->services[ $class ];
	}

	public function boot(): void {
		load_plugin_textdomain( 'cpt-builder', false, dirname( plugin_basename( CPTB_FILE ) ) . '/languages' );

		$this->get( TaxonomyManager::class )->init();
		$this->get( PostTypeManager::class )->init();
		$this->get( FieldManager::class )->init();
		$this->get( RelationshipManager::class )->init();
		$this->get( RestManager::class )->init();

		if ( is_admin() ) {
			$this->get( AdminManager::class )->init();
			$this->get( FormHandler::class )->init();
		}

		add_action( 'init', array( $this, 'maybe_flush_rewrite' ), 99 );
	}

	/**
	 * Flush rewrite rules once after structures changed.
	 */
	public function maybe_flush_rewrite(): void {
		if ( get_option( 'cptb_flush_needed' ) ) {
			flush_rewrite_rules();
			delete_option( 'cptb_flush_needed' );
		}
	}
}
