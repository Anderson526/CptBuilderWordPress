<?php
/**
 * Developer API facade.
 *
 * Usage:
 *   CPTBuilder::registerPostType( [ 'name' => 'course', 'label' => 'Courses' ] );
 *   CPTBuilder::registerField( [ 'name' => 'price', 'type' => 'number', 'post_type' => 'course' ] );
 */

defined( 'ABSPATH' ) || exit;

use CPTBuilder\Core\Plugin;
use CPTBuilder\Fields\FieldManager;
use CPTBuilder\PostTypes\PostTypeManager;

final class CPTBuilder {

	/** @var array[] Runtime post type configs registered via code. */
	private static $post_types = array();

	/** @var array[] Runtime field configs registered via code. */
	private static $fields = array();

	/** @var bool */
	private static $hooked = false;

	/**
	 * Register a post type from code (not persisted to the database).
	 *
	 * Accepts the same keys as the builder config, plus 'name' (alias of key)
	 * and 'label' (alias of plural).
	 */
	public static function registerPostType( array $config ): void {
		if ( isset( $config['name'] ) && empty( $config['key'] ) ) {
			$config['key'] = $config['name'];
		}
		if ( isset( $config['label'] ) && empty( $config['plural'] ) ) {
			$config['plural'] = $config['label'];
		}
		if ( empty( $config['singular'] ) && ! empty( $config['plural'] ) ) {
			$config['singular'] = $config['plural'];
		}

		self::$post_types[] = $config;
		self::hook();
	}

	/**
	 * Register a single field from code (not persisted to the database).
	 *
	 * Required: 'name' (meta key), 'post_type'. Optional: 'type', 'label', etc.
	 */
	public static function registerField( array $config ): void {
		if ( isset( $config['name'] ) && empty( $config['key'] ) ) {
			$config['key'] = $config['name'];
		}
		if ( empty( $config['key'] ) || empty( $config['post_type'] ) ) {
			return;
		}
		if ( empty( $config['label'] ) ) {
			$config['label'] = ucwords( str_replace( array( '_', '-' ), ' ', $config['key'] ) );
		}

		self::$fields[] = $config;
		self::hook();
	}

	private static function hook(): void {
		if ( self::$hooked ) {
			return;
		}
		self::$hooked = true;

		add_action(
			'init',
			static function () {
				$plugin = Plugin::instance();

				foreach ( self::$post_types as $config ) {
					$plugin->get( PostTypeManager::class )->register( $config );
				}

				if ( ! empty( self::$fields ) ) {
					$groups = array();
					foreach ( self::$fields as $field ) {
						$post_type = $field['post_type'];
						unset( $field['post_type'] );
						$groups[ $post_type ][] = wp_parse_args( $field, FieldManager::field_defaults() );
					}

					foreach ( $groups as $post_type => $fields ) {
						$group = array(
							'key'        => 'cptb_code_' . $post_type,
							'title'      => __( 'Custom Fields', 'cpt-builder' ),
							'post_types' => array( $post_type ),
							'fields'     => $fields,
						);
						add_filter(
							'cpt_builder_runtime_field_groups',
							static function ( $runtime ) use ( $group ) {
								$runtime[ $group['key'] ] = $group;
								return $runtime;
							}
						);
					}
				}
			},
			12
		);
	}
}
