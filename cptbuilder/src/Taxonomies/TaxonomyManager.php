<?php
namespace CPTBuilder\Taxonomies;

use CPTBuilder\Core\Plugin;

defined( 'ABSPATH' ) || exit;

class TaxonomyManager {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function repository(): TaxonomyRepository {
		return $this->plugin->get( TaxonomyRepository::class );
	}

	public function init(): void {
		add_action( 'init', array( $this, 'register_all' ), 11 );
	}

	public function register_all(): void {
		foreach ( $this->repository()->all() as $config ) {
			$this->register( $config );
		}
	}

	public function register( array $config ): void {
		$config = apply_filters( 'cpt_builder_taxonomy_config', $config );

		if ( empty( $config['key'] ) || taxonomy_exists( $config['key'] ) ) {
			return;
		}

		do_action( 'cpt_builder_before_register_taxonomy', $config );

		$args = $this->build_args( $config );
		register_taxonomy( $config['key'], (array) ( $config['post_types'] ?? array() ), $args );

		do_action( 'cpt_builder_after_register_taxonomy', $config['key'], $args );
	}

	public static function defaults(): array {
		return array(
			'key'               => '',
			'singular'          => '',
			'plural'            => '',
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite_slug'      => '',
			'post_types'        => array(),
		);
	}

	public function build_args( array $config ): array {
		$config = wp_parse_args( $config, self::defaults() );

		return array(
			'labels'            => self::build_labels( $config['singular'], $config['plural'] ),
			'hierarchical'      => (bool) $config['hierarchical'],
			'public'            => (bool) $config['public'],
			'show_ui'           => (bool) $config['show_ui'],
			'show_admin_column' => (bool) $config['show_admin_column'],
			'show_in_rest'      => (bool) $config['show_in_rest'],
			'rewrite'           => array(
				'slug' => $config['rewrite_slug'] ? $config['rewrite_slug'] : $config['key'],
			),
		);
	}

	public static function build_labels( string $singular, string $plural ): array {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			'search_items'  => sprintf( __( 'Search %s', 'cpt-builder' ), $plural ),
			'all_items'     => sprintf( __( 'All %s', 'cpt-builder' ), $plural ),
			'edit_item'     => sprintf( __( 'Edit %s', 'cpt-builder' ), $singular ),
			'update_item'   => sprintf( __( 'Update %s', 'cpt-builder' ), $singular ),
			'add_new_item'  => sprintf( __( 'Add New %s', 'cpt-builder' ), $singular ),
			'new_item_name' => sprintf( __( 'New %s Name', 'cpt-builder' ), $singular ),
			'menu_name'     => $plural,
		);
	}
}
