<?php
namespace CPTBuilder\Fields;

use CPTBuilder\Core\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Stores field groups. Each group: key, title, post_types[], fields[].
 */
class FieldRepository extends Repository {

	protected $option_name = 'cptb_field_groups';

	/**
	 * Saved groups merged with groups registered at runtime via the Developer API.
	 *
	 * @return array<string,array>
	 */
	public function with_runtime(): array {
		return apply_filters( 'cpt_builder_runtime_field_groups', $this->all() );
	}

	/**
	 * Groups attached to a given post type.
	 *
	 * @return array<string,array>
	 */
	public function for_post_type( string $post_type ): array {
		$out = array();
		foreach ( $this->with_runtime() as $key => $group ) {
			if ( in_array( $post_type, (array) ( $group['post_types'] ?? array() ), true ) ) {
				$out[ $key ] = $group;
			}
		}
		return $out;
	}

	/**
	 * Total number of individual fields across all groups.
	 */
	public function count_fields( ?string $post_type = null ): int {
		$groups = null === $post_type ? $this->all() : $this->for_post_type( $post_type );
		$total  = 0;
		foreach ( $groups as $group ) {
			$total += count( (array) ( $group['fields'] ?? array() ) );
		}
		return $total;
	}

	/**
	 * Supported field types (MVP).
	 *
	 * @return array<string,string> type => label
	 */
	public static function field_types(): array {
		return array(
			'text'         => __( 'Text', 'cpt-builder' ),
			'textarea'     => __( 'Textarea', 'cpt-builder' ),
			'number'       => __( 'Number', 'cpt-builder' ),
			'email'        => __( 'Email', 'cpt-builder' ),
			'url'          => __( 'URL', 'cpt-builder' ),
			'date'         => __( 'Date', 'cpt-builder' ),
			'color'        => __( 'Color', 'cpt-builder' ),
			'checkbox'     => __( 'Checkbox', 'cpt-builder' ),
			'select'       => __( 'Select', 'cpt-builder' ),
			'image'        => __( 'Image', 'cpt-builder' ),
			'relationship' => __( 'Relationship', 'cpt-builder' ),
		);
	}
}
