<?php
namespace CPTBuilder\Admin;

use CPTBuilder\CodeGenerator\CodeGenerator;
use CPTBuilder\Core\Plugin;
use CPTBuilder\Core\SettingsManager;
use CPTBuilder\Fields\FieldManager;
use CPTBuilder\Fields\FieldRepository;
use CPTBuilder\PostTypes\PostTypeManager;
use CPTBuilder\PostTypes\PostTypeRepository;
use CPTBuilder\Relationships\RelationshipRepository;
use CPTBuilder\Taxonomies\TaxonomyRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Admin menu, pages, notices and asset loading.
 */
class AdminManager {

	const CAPABILITY = 'manage_options';

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'render_notices' ) );
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'CPT Builder', 'cpt-builder' ),
			__( 'CPT Builder', 'cpt-builder' ),
			self::CAPABILITY,
			'cptb-dashboard',
			array( $this, 'page_dashboard' ),
			'dashicons-database-add',
			26
		);

		$submenus = array(
			array( __( 'Dashboard', 'cpt-builder' ), 'cptb-dashboard', 'page_dashboard' ),
			array( __( 'Post Types', 'cpt-builder' ), 'cptb-post-types', 'page_post_types' ),
			array( __( 'Taxonomies', 'cpt-builder' ), 'cptb-taxonomies', 'page_taxonomies' ),
			array( __( 'Fields', 'cpt-builder' ), 'cptb-fields', 'page_fields' ),
			array( __( 'Relationships', 'cpt-builder' ), 'cptb-relationships', 'page_relationships' ),
			array( __( 'Tools', 'cpt-builder' ), 'cptb-tools', 'page_tools' ),
			array( __( 'Settings', 'cpt-builder' ), 'cptb-settings', 'page_settings' ),
			array( __( 'Support Development', 'cpt-builder' ), 'cptb-support', 'page_support' ),
		);

		foreach ( $submenus as $submenu ) {
			add_submenu_page(
				'cptb-dashboard',
				$submenu[0],
				$submenu[0],
				self::CAPABILITY,
				$submenu[1],
				array( $this, $submenu[2] )
			);
		}
	}

	public function enqueue_assets( string $hook ): void {
		// Builder admin pages.
		if ( false !== strpos( (string) filter_input( INPUT_GET, 'page' ), 'cptb-' ) ) {
			wp_enqueue_style( 'cptb-admin', CPTB_URL . 'assets/css/admin.css', array(), CPTB_VERSION );
			wp_enqueue_script( 'cptb-admin', CPTB_URL . 'assets/js/admin.js', array( 'jquery' ), CPTB_VERSION, true );
			wp_localize_script(
				'cptb-admin',
				'cptbAdmin',
				array(
					'confirmDelete' => __( 'This will remove the configuration only. Existing WordPress content will NOT be deleted. Continue?', 'cpt-builder' ),
				)
			);
		}

		// Post edit screens: field meta boxes.
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_media();
			wp_enqueue_style( 'cptb-fields', CPTB_URL . 'assets/css/fields.css', array(), CPTB_VERSION );
			wp_enqueue_script( 'cptb-fields', CPTB_URL . 'assets/js/fields.js', array( 'jquery' ), CPTB_VERSION, true );
		}
	}

	public function render_notices(): void {
		$msg = filter_input( INPUT_GET, 'cptb_msg' );
		if ( ! $msg ) {
			return;
		}

		$messages = array(
			'saved'          => array( 'success', __( 'Configuration saved.', 'cpt-builder' ) ),
			'deleted'        => array( 'success', __( 'Configuration deleted. Existing content was not removed.', 'cpt-builder' ) ),
			'imported'       => array( 'success', __( 'Import completed.', 'cpt-builder' ) ),
			'settings_saved' => array( 'success', __( 'Settings saved.', 'cpt-builder' ) ),
			'invalid_key'    => array( 'error', __( 'Invalid or reserved key. Choose another key.', 'cpt-builder' ) ),
			'exists'         => array( 'error', __( 'An item with this key already exists.', 'cpt-builder' ) ),
			'invalid_file'   => array( 'error', __( 'The uploaded file is not a valid CPT Builder JSON export.', 'cpt-builder' ) ),
			'error'          => array( 'error', __( 'Something went wrong. Please try again.', 'cpt-builder' ) ),
		);

		if ( ! isset( $messages[ $msg ] ) ) {
			return;
		}

		list( $type, $text ) = $messages[ $msg ];

		$detail = filter_input( INPUT_GET, 'cptb_detail' );
		if ( $detail ) {
			$text .= ' ' . sanitize_text_field( $detail );
		}

		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			esc_attr( $type ),
			esc_html( $text )
		);
	}

	/* ---------------------------------------------------------------------
	 * Page callbacks
	 * ------------------------------------------------------------------- */

	public function page_dashboard(): void {
		$post_types = $this->plugin->get( PostTypeRepository::class );
		$fields     = $this->plugin->get( FieldRepository::class );

		$recent = array();
		foreach ( array_slice( $post_types->all(), -5, 5, true ) as $key => $config ) {
			$recent[] = array(
				'label'  => $config['plural'],
				'key'    => $key,
				'fields' => $fields->count_fields( $key ),
			);
		}

		$this->view(
			'dashboard',
			array(
				'post_type_count'    => $post_types->count(),
				'taxonomy_count'     => $this->plugin->get( TaxonomyRepository::class )->count(),
				'field_count'        => $fields->count_fields(),
				'relationship_count' => $this->plugin->get( RelationshipRepository::class )->count(),
				'recent'             => $recent,
				'settings'           => $this->plugin->get( SettingsManager::class )->all(),
			)
		);
	}

	public function page_post_types(): void {
		$repo   = $this->plugin->get( PostTypeRepository::class );
		$action = (string) filter_input( INPUT_GET, 'action' );

		if ( 'add' === $action || 'edit' === $action ) {
			$key  = sanitize_key( (string) filter_input( INPUT_GET, 'key' ) );
			$item = 'edit' === $action ? $repo->find( $key ) : null;
			$this->view(
				'post-type-edit',
				array(
					'item'             => wp_parse_args( (array) $item, PostTypeManager::defaults() ),
					'is_new'           => null === $item,
					'supports_options' => PostTypeManager::supports_options(),
				)
			);
			return;
		}

		$this->view(
			'post-types-list',
			array(
				'items'      => $repo->all(),
				'field_repo' => $this->plugin->get( FieldRepository::class ),
			)
		);
	}

	public function page_taxonomies(): void {
		$repo   = $this->plugin->get( TaxonomyRepository::class );
		$action = (string) filter_input( INPUT_GET, 'action' );

		if ( 'add' === $action || 'edit' === $action ) {
			$key  = sanitize_key( (string) filter_input( INPUT_GET, 'key' ) );
			$item = 'edit' === $action ? $repo->find( $key ) : null;
			$this->view(
				'taxonomy-edit',
				array(
					'item'       => wp_parse_args( (array) $item, \CPTBuilder\Taxonomies\TaxonomyManager::defaults() ),
					'is_new'     => null === $item,
					'post_types' => $this->plugin->get( PostTypeRepository::class )->all(),
				)
			);
			return;
		}

		$this->view( 'taxonomies-list', array( 'items' => $repo->all() ) );
	}

	public function page_fields(): void {
		$repo   = $this->plugin->get( FieldRepository::class );
		$action = (string) filter_input( INPUT_GET, 'action' );

		if ( 'add' === $action || 'edit' === $action ) {
			$key  = sanitize_key( (string) filter_input( INPUT_GET, 'key' ) );
			$item = 'edit' === $action ? $repo->find( $key ) : null;
			$this->view(
				'field-group-edit',
				array(
					'item'           => wp_parse_args(
						(array) $item,
						array(
							'key'        => '',
							'title'      => '',
							'post_types' => array(),
							'fields'     => array(),
						)
					),
					'is_new'         => null === $item,
					'post_types'     => $this->plugin->get( PostTypeRepository::class )->all(),
					'field_types'    => FieldRepository::field_types(),
					'field_defaults' => FieldManager::field_defaults(),
				)
			);
			return;
		}

		$this->view( 'field-groups-list', array( 'items' => $repo->all() ) );
	}

	public function page_relationships(): void {
		$repo   = $this->plugin->get( RelationshipRepository::class );
		$action = (string) filter_input( INPUT_GET, 'action' );

		if ( 'add' === $action || 'edit' === $action ) {
			$key  = sanitize_key( (string) filter_input( INPUT_GET, 'key' ) );
			$item = 'edit' === $action ? $repo->find( $key ) : null;
			$this->view(
				'relationship-edit',
				array(
					'item'       => wp_parse_args(
						(array) $item,
						array(
							'key'   => '',
							'label' => '',
							'from'  => '',
							'to'    => '',
							'type'  => 'many_to_one',
						)
					),
					'is_new'     => null === $item,
					'post_types' => $this->plugin->get( PostTypeRepository::class )->all(),
					'rel_types'  => RelationshipRepository::relationship_types(),
				)
			);
			return;
		}

		$this->view(
			'relationships-list',
			array(
				'items'     => $repo->all(),
				'rel_types' => RelationshipRepository::relationship_types(),
			)
		);
	}

	public function page_tools(): void {
		$generated = '';
		if ( filter_input( INPUT_GET, 'cptb_generate' ) && check_admin_referer( 'cptb_generate_code' ) ) {
			$generated = $this->plugin->get( CodeGenerator::class )->generate_all();
		}

		$this->view(
			'tools',
			array(
				'generated'      => $generated,
				'developer_mode' => (bool) $this->plugin->get( SettingsManager::class )->get( 'developer_mode' ),
			)
		);
	}

	public function page_settings(): void {
		$this->view( 'settings', array( 'settings' => $this->plugin->get( SettingsManager::class )->all() ) );
	}

	public function page_support(): void {
		$this->view( 'donations', array( 'settings' => $this->plugin->get( SettingsManager::class )->all() ) );
	}

	/**
	 * Render a view file with local variables.
	 */
	private function view( string $name, array $vars = array() ): void {
		$path = CPTB_DIR . 'src/Admin/views/' . $name . '.php';
		if ( ! file_exists( $path ) ) {
			return;
		}
		extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
		include $path;
	}
}
