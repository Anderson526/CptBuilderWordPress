<?php
namespace CPTBuilder\Admin;

use CPTBuilder\Core\Plugin;
use CPTBuilder\Core\SettingsManager;
use CPTBuilder\Fields\FieldManager;
use CPTBuilder\Fields\FieldRepository;
use CPTBuilder\ImportExport\ExportManager;
use CPTBuilder\ImportExport\ImportManager;
use CPTBuilder\PostTypes\PostTypeRepository;
use CPTBuilder\Relationships\RelationshipRepository;
use CPTBuilder\Taxonomies\TaxonomyRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Handles all admin-post.php form submissions with nonce + capability checks.
 */
class FormHandler {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		$actions = array(
			'cptb_save_post_type',
			'cptb_delete_post_type',
			'cptb_save_taxonomy',
			'cptb_delete_taxonomy',
			'cptb_save_field_group',
			'cptb_delete_field_group',
			'cptb_save_relationship',
			'cptb_delete_relationship',
			'cptb_export',
			'cptb_import',
			'cptb_save_settings',
		);

		foreach ( $actions as $action ) {
			add_action( 'admin_post_' . $action, array( $this, substr( $action, 5 ) ) );
		}
	}

	/**
	 * Authenticated? → Valid nonce? → Required capability? → continue.
	 */
	private function guard( string $nonce_action ): void {
		if ( ! current_user_can( AdminManager::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'cpt-builder' ) );
		}
		check_admin_referer( $nonce_action );
	}

	private function redirect( string $page, string $msg, array $extra = array() ): void {
		$args = array_merge(
			array(
				'page'     => $page,
				'cptb_msg' => $msg,
			),
			$extra
		);
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	private function post( string $key, $default = '' ) {
		return isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : $default; // phpcs:ignore WordPress.Security -- sanitized by callers.
	}

	/* ------------------------------------------------------------------ */

	public function save_post_type(): void {
		$this->guard( 'cptb_save_post_type' );

		$repo   = $this->plugin->get( PostTypeRepository::class );
		$key    = sanitize_key( (string) $this->post( 'key' ) );
		$is_new = '1' === $this->post( 'is_new' );

		if ( '' === $key || strlen( $key ) > 20 || in_array( $key, PostTypeRepository::reserved_keys(), true ) ) {
			$this->redirect( 'cptb-post-types', 'invalid_key' );
		}
		if ( $is_new && ( $repo->exists( $key ) || post_type_exists( $key ) ) ) {
			$this->redirect( 'cptb-post-types', 'exists' );
		}

		$supports = array_map( 'sanitize_key', (array) $this->post( 'supports', array() ) );

		$repo->save(
			array(
				'key'                 => $key,
				'singular'            => sanitize_text_field( (string) $this->post( 'singular' ) ),
				'plural'              => sanitize_text_field( (string) $this->post( 'plural' ) ),
				'description'         => sanitize_textarea_field( (string) $this->post( 'description' ) ),
				'menu_icon'           => sanitize_text_field( (string) $this->post( 'menu_icon' ) ),
				'menu_position'       => '' !== $this->post( 'menu_position' ) ? absint( $this->post( 'menu_position' ) ) : '',
				'public'              => (bool) $this->post( 'public' ),
				'publicly_queryable'  => (bool) $this->post( 'publicly_queryable' ),
				'show_ui'             => (bool) $this->post( 'show_ui' ),
				'show_in_menu'        => (bool) $this->post( 'show_in_menu' ),
				'show_in_rest'        => (bool) $this->post( 'show_in_rest' ),
				'rest_base'           => sanitize_key( (string) $this->post( 'rest_base' ) ),
				'has_archive'         => (bool) $this->post( 'has_archive' ),
				'hierarchical'        => (bool) $this->post( 'hierarchical' ),
				'exclude_from_search' => (bool) $this->post( 'exclude_from_search' ),
				'rewrite_slug'        => sanitize_title( (string) $this->post( 'rewrite_slug' ) ),
				'supports'            => $supports,
			)
		);

		$this->redirect( 'cptb-post-types', 'saved' );
	}

	public function delete_post_type(): void {
		$this->guard( 'cptb_delete_post_type' );
		$key = sanitize_key( (string) filter_input( INPUT_GET, 'key' ) );
		$this->plugin->get( PostTypeRepository::class )->delete( $key );
		$this->redirect( 'cptb-post-types', 'deleted' );
	}

	public function save_taxonomy(): void {
		$this->guard( 'cptb_save_taxonomy' );

		$repo   = $this->plugin->get( TaxonomyRepository::class );
		$key    = sanitize_key( (string) $this->post( 'key' ) );
		$is_new = '1' === $this->post( 'is_new' );

		if ( '' === $key || strlen( $key ) > 32 || in_array( $key, TaxonomyRepository::reserved_keys(), true ) ) {
			$this->redirect( 'cptb-taxonomies', 'invalid_key' );
		}
		if ( $is_new && ( $repo->exists( $key ) || taxonomy_exists( $key ) ) ) {
			$this->redirect( 'cptb-taxonomies', 'exists' );
		}

		$repo->save(
			array(
				'key'               => $key,
				'singular'          => sanitize_text_field( (string) $this->post( 'singular' ) ),
				'plural'            => sanitize_text_field( (string) $this->post( 'plural' ) ),
				'hierarchical'      => (bool) $this->post( 'hierarchical' ),
				'public'            => (bool) $this->post( 'public' ),
				'show_ui'           => (bool) $this->post( 'show_ui' ),
				'show_admin_column' => (bool) $this->post( 'show_admin_column' ),
				'show_in_rest'      => (bool) $this->post( 'show_in_rest' ),
				'rewrite_slug'      => sanitize_title( (string) $this->post( 'rewrite_slug' ) ),
				'post_types'        => array_map( 'sanitize_key', (array) $this->post( 'post_types', array() ) ),
			)
		);

		$this->redirect( 'cptb-taxonomies', 'saved' );
	}

	public function delete_taxonomy(): void {
		$this->guard( 'cptb_delete_taxonomy' );
		$key = sanitize_key( (string) filter_input( INPUT_GET, 'key' ) );
		$this->plugin->get( TaxonomyRepository::class )->delete( $key );
		$this->redirect( 'cptb-taxonomies', 'deleted' );
	}

	public function save_field_group(): void {
		$this->guard( 'cptb_save_field_group' );

		$repo   = $this->plugin->get( FieldRepository::class );
		$key    = sanitize_key( (string) $this->post( 'key' ) );
		$is_new = '1' === $this->post( 'is_new' );

		if ( '' === $key ) {
			$this->redirect( 'cptb-fields', 'invalid_key' );
		}
		if ( $is_new && $repo->exists( $key ) ) {
			$this->redirect( 'cptb-fields', 'exists' );
		}

		$valid_types = array_keys( FieldRepository::field_types() );
		$fields      = array();

		foreach ( (array) $this->post( 'fields', array() ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$field_key = sanitize_key( (string) ( $row['key'] ?? '' ) );
			if ( '' === $field_key ) {
				continue;
			}
			$type = sanitize_key( (string) ( $row['type'] ?? 'text' ) );

			$fields[] = array(
				'key'           => $field_key,
				'label'         => sanitize_text_field( (string) ( $row['label'] ?? '' ) ),
				'type'          => in_array( $type, $valid_types, true ) ? $type : 'text',
				'description'   => sanitize_text_field( (string) ( $row['description'] ?? '' ) ),
				'placeholder'   => sanitize_text_field( (string) ( $row['placeholder'] ?? '' ) ),
				'default'       => sanitize_text_field( (string) ( $row['default'] ?? '' ) ),
				'required'      => ! empty( $row['required'] ),
				'options'       => sanitize_textarea_field( (string) ( $row['options'] ?? '' ) ),
				'min'           => sanitize_text_field( (string) ( $row['min'] ?? '' ) ),
				'max'           => sanitize_text_field( (string) ( $row['max'] ?? '' ) ),
				'rel_post_type' => sanitize_key( (string) ( $row['rel_post_type'] ?? '' ) ),
				'multiple'      => ! empty( $row['multiple'] ),
				'rest'          => in_array( $row['rest'] ?? '', array( 'hidden', 'read', 'write' ), true ) ? $row['rest'] : 'read',
			);
		}

		$repo->save(
			array(
				'key'        => $key,
				'title'      => sanitize_text_field( (string) $this->post( 'title' ) ),
				'post_types' => array_map( 'sanitize_key', (array) $this->post( 'post_types', array() ) ),
				'fields'     => $fields,
			)
		);

		$this->redirect( 'cptb-fields', 'saved' );
	}

	public function delete_field_group(): void {
		$this->guard( 'cptb_delete_field_group' );
		$key = sanitize_key( (string) filter_input( INPUT_GET, 'key' ) );
		$this->plugin->get( FieldRepository::class )->delete( $key );
		$this->redirect( 'cptb-fields', 'deleted' );
	}

	public function save_relationship(): void {
		$this->guard( 'cptb_save_relationship' );

		$repo   = $this->plugin->get( RelationshipRepository::class );
		$key    = sanitize_key( (string) $this->post( 'key' ) );
		$is_new = '1' === $this->post( 'is_new' );

		if ( '' === $key ) {
			$this->redirect( 'cptb-relationships', 'invalid_key' );
		}
		if ( $is_new && $repo->exists( $key ) ) {
			$this->redirect( 'cptb-relationships', 'exists' );
		}

		$type = sanitize_key( (string) $this->post( 'type' ) );
		if ( ! array_key_exists( $type, RelationshipRepository::relationship_types() ) ) {
			$type = 'many_to_one';
		}

		$repo->save(
			array(
				'key'   => $key,
				'label' => sanitize_text_field( (string) $this->post( 'label' ) ),
				'from'  => sanitize_key( (string) $this->post( 'from' ) ),
				'to'    => sanitize_key( (string) $this->post( 'to' ) ),
				'type'  => $type,
			)
		);

		$this->redirect( 'cptb-relationships', 'saved' );
	}

	public function delete_relationship(): void {
		$this->guard( 'cptb_delete_relationship' );
		$key = sanitize_key( (string) filter_input( INPUT_GET, 'key' ) );
		$this->plugin->get( RelationshipRepository::class )->delete( $key );
		$this->redirect( 'cptb-relationships', 'deleted' );
	}

	public function export(): void {
		$this->guard( 'cptb_export' );

		$json = $this->plugin->get( ExportManager::class )->export_json();

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=cpt-builder-export-' . gmdate( 'Y-m-d' ) . '.json' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON file download.
		exit;
	}

	public function import(): void {
		$this->guard( 'cptb_import' );

		if ( empty( $_FILES['cptb_import_file']['tmp_name'] ) ) {
			$this->redirect( 'cptb-tools', 'invalid_file' );
		}

		$tmp_name = $_FILES['cptb_import_file']['tmp_name']; // phpcs:ignore WordPress.Security -- server-generated path.
		$raw      = file_get_contents( $tmp_name );
		$data     = json_decode( (string) $raw, true );

		if ( ! is_array( $data ) ) {
			$this->redirect( 'cptb-tools', 'invalid_file' );
		}

		$mode   = 'update' === $this->post( 'conflict_mode' ) ? 'update' : 'skip';
		$result = $this->plugin->get( ImportManager::class )->import( $data, $mode );

		if ( is_wp_error( $result ) ) {
			$this->redirect( 'cptb-tools', 'invalid_file' );
		}

		$totals = array( 'imported' => 0, 'updated' => 0, 'skipped' => 0 );
		foreach ( $result as $counts ) {
			foreach ( $totals as $k => $v ) {
				$totals[ $k ] += $counts[ $k ];
			}
		}

		$this->redirect(
			'cptb-tools',
			'imported',
			array(
				'cptb_detail' => rawurlencode(
					sprintf(
						/* translators: 1: imported count, 2: updated count, 3: skipped count. */
						__( 'Imported: %1$d, Updated: %2$d, Skipped: %3$d.', 'cpt-builder' ),
						$totals['imported'],
						$totals['updated'],
						$totals['skipped']
					)
				),
			)
		);
	}

	public function save_settings(): void {
		$this->guard( 'cptb_save_settings' );

		$this->plugin->get( SettingsManager::class )->save(
			array(
				'developer_mode'           => (bool) $this->post( 'developer_mode' ),
				'delete_data_on_uninstall' => (bool) $this->post( 'delete_data_on_uninstall' ),
				'donation_url'             => (string) $this->post( 'donation_url' ),
				'support_url'              => (string) $this->post( 'support_url' ),
			)
		);

		$this->redirect( 'cptb-settings', 'settings_saved' );
	}
}
