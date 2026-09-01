<?php
namespace CPTBuilder\ImportExport;

use CPTBuilder\Core\Plugin;
use CPTBuilder\Fields\FieldRepository;
use CPTBuilder\PostTypes\PostTypeRepository;
use CPTBuilder\Relationships\RelationshipRepository;
use CPTBuilder\Taxonomies\TaxonomyRepository;

defined( 'ABSPATH' ) || exit;

class ExportManager {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function export_data(): array {
		return array(
			'plugin'        => 'cpt-builder',
			'version'       => CPTB_VERSION,
			'generated'     => gmdate( 'c' ),
			'post_types'    => array_values( $this->plugin->get( PostTypeRepository::class )->all() ),
			'taxonomies'    => array_values( $this->plugin->get( TaxonomyRepository::class )->all() ),
			'field_groups'  => array_values( $this->plugin->get( FieldRepository::class )->all() ),
			'relationships' => array_values( $this->plugin->get( RelationshipRepository::class )->all() ),
		);
	}

	public function export_json(): string {
		return wp_json_encode( $this->export_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}
}
