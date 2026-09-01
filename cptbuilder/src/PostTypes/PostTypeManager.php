<?php
namespace CPTBuilder\PostTypes;

use CPTBuilder\Core\Plugin;

defined( 'ABSPATH' ) || exit;

class PostTypeManager {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function repository(): PostTypeRepository {
		return $this->plugin->get( PostTypeRepository::class );
	}

	public function init(): void {
		add_action( 'init', array( $this, 'register_all' ), 10 );
	}

	public function register_all(): void {
		foreach ( $this->repository()->all() as $config ) {
			$this->register( $config );
		}
	}

	public function register( array $config ): void {
		$config = apply_filters( 'cpt_builder_post_type_config', $config );

		if ( empty( $config['key'] ) || post_type_exists( $config['key'] ) ) {
			return;
		}

		do_action( 'cpt_builder_before_register_post_type', $config );

		$args = $this->build_args( $config );
		register_post_type( $config['key'], $args );

		do_action( 'cpt_builder_after_register_post_type', $config['key'], $args );
	}

	public static function defaults(): array {
		return array(
			'key'                 => '',
			'singular'            => '',
			'plural'              => '',
			'description'         => '',
			'menu_icon'           => 'dashicons-admin-post',
			'menu_position'       => '',
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'rest_base'           => '',
			'has_archive'         => true,
			'hierarchical'        => false,
			'exclude_from_search' => false,
			'rewrite_slug'        => '',
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
		);
	}

	/**
	 * Build register_post_type() args from a stored config.
	 */
	public function build_args( array $config ): array {
		$config = wp_parse_args( $config, self::defaults() );

		$args = array(
			'labels'              => self::build_labels( $config['singular'], $config['plural'] ),
			'description'         => $config['description'],
			'public'              => (bool) $config['public'],
			'publicly_queryable'  => (bool) $config['publicly_queryable'],
			'show_ui'             => (bool) $config['show_ui'],
			'show_in_menu'        => (bool) $config['show_in_menu'],
			'show_in_rest'        => (bool) $config['show_in_rest'],
			'has_archive'         => (bool) $config['has_archive'],
			'hierarchical'        => (bool) $config['hierarchical'],
			'exclude_from_search' => (bool) $config['exclude_from_search'],
			'menu_icon'           => $config['menu_icon'] ? $config['menu_icon'] : null,
			'supports'            => array_values( (array) $config['supports'] ),
			'rewrite'             => array(
				'slug' => $config['rewrite_slug'] ? $config['rewrite_slug'] : $config['key'],
			),
		);

		if ( '' !== $config['menu_position'] && null !== $config['menu_position'] ) {
			$args['menu_position'] = (int) $config['menu_position'];
		}

		if ( ! empty( $config['rest_base'] ) ) {
			$args['rest_base'] = $config['rest_base'];
		}

		return $args;
	}

	public static function build_labels( string $singular, string $plural ): array {
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			/* translators: %s: singular post type name. */
			'add_new'            => __( 'Add New', 'cpt-builder' ),
			'add_new_item'       => sprintf( __( 'Add New %s', 'cpt-builder' ), $singular ),
			'edit_item'          => sprintf( __( 'Edit %s', 'cpt-builder' ), $singular ),
			'new_item'           => sprintf( __( 'New %s', 'cpt-builder' ), $singular ),
			'view_item'          => sprintf( __( 'View %s', 'cpt-builder' ), $singular ),
			'view_items'         => sprintf( __( 'View %s', 'cpt-builder' ), $plural ),
			'search_items'       => sprintf( __( 'Search %s', 'cpt-builder' ), $plural ),
			'not_found'          => sprintf( __( 'No %s found', 'cpt-builder' ), strtolower( $plural ) ),
			'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'cpt-builder' ), strtolower( $plural ) ),
			'all_items'          => sprintf( __( 'All %s', 'cpt-builder' ), $plural ),
			'archives'           => sprintf( __( '%s Archives', 'cpt-builder' ), $singular ),
			'menu_name'          => $plural,
		);
	}

	/**
	 * Supported "supports" options for the UI.
	 *
	 * @return array<string,string>
	 */
	public static function supports_options(): array {
		return array(
			'title'           => __( 'Title', 'cpt-builder' ),
			'editor'          => __( 'Editor', 'cpt-builder' ),
			'thumbnail'       => __( 'Featured Image', 'cpt-builder' ),
			'excerpt'         => __( 'Excerpt', 'cpt-builder' ),
			'author'          => __( 'Author', 'cpt-builder' ),
			'revisions'       => __( 'Revisions', 'cpt-builder' ),
			'comments'        => __( 'Comments', 'cpt-builder' ),
			'page-attributes' => __( 'Page Attributes', 'cpt-builder' ),
			'custom-fields'   => __( 'Custom Fields', 'cpt-builder' ),
		);
	}
}
