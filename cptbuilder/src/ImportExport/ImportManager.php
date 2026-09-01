<?php
namespace CPTBuilder\ImportExport;

use CPTBuilder\Core\Plugin;
use CPTBuilder\Fields\FieldRepository;
use CPTBuilder\PostTypes\PostTypeRepository;
use CPTBuilder\Relationships\RelationshipRepository;
use CPTBuilder\Taxonomies\TaxonomyRepository;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class ImportManager {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Validate and import a decoded configuration.
	 *
	 * @param array  $data          Decoded JSON data.
	 * @param string $conflict_mode 'skip' or 'update' for existing keys.
	 * @return array|WP_Error Report with counts per collection.
	 */
	public function import( array $data, string $conflict_mode = 'skip' ) {
		$validation = $this->validate( $data );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$conflict_mode = in_array( $conflict_mode, array( 'skip', 'update' ), true ) ? $conflict_mode : 'skip';

		$map = array(
			'post_types'    => PostTypeRepository::class,
			'taxonomies'    => TaxonomyRepository::class,
			'field_groups'  => FieldRepository::class,
			'relationships' => RelationshipRepository::class,
		);

		$report = array();

		foreach ( $map as $collection => $repo_class ) {
			$repo     = $this->plugin->get( $repo_class );
			$imported = 0;
			$updated  = 0;
			$skipped  = 0;

			foreach ( (array) ( $data[ $collection ] ?? array() ) as $item ) {
				if ( ! is_array( $item ) || empty( $item['key'] ) ) {
					continue;
				}
				$item['key'] = sanitize_key( $item['key'] );

				if ( $repo->exists( $item['key'] ) ) {
					if ( 'update' === $conflict_mode ) {
						$repo->save( $item );
						$updated++;
					} else {
						$skipped++;
					}
				} else {
					$repo->save( $item );
					$imported++;
				}
			}

			$report[ $collection ] = compact( 'imported', 'updated', 'skipped' );
		}

		do_action( 'cpt_builder_after_import', $report, $data );

		return $report;
	}

	/**
	 * @param array $data Decoded JSON.
	 * @return true|WP_Error
	 */
	public function validate( array $data ) {
		$collections = array( 'post_types', 'taxonomies', 'field_groups', 'relationships' );
		$has_any     = false;

		foreach ( $collections as $collection ) {
			if ( ! isset( $data[ $collection ] ) ) {
				continue;
			}
			if ( ! is_array( $data[ $collection ] ) ) {
				return new WP_Error(
					'cptb_invalid_schema',
					sprintf( __( 'Invalid import file: "%s" must be a list.', 'cpt-builder' ), $collection )
				);
			}
			$has_any = true;
		}

		if ( ! $has_any ) {
			return new WP_Error( 'cptb_empty_import', __( 'The file does not contain any CPT Builder structures.', 'cpt-builder' ) );
		}

		return true;
	}
}
