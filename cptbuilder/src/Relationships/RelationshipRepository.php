<?php
namespace CPTBuilder\Relationships;

use CPTBuilder\Core\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Stores relationships. Each item: key, label, from, to, type.
 * Storage strategy (MVP): post meta on the "from" post type.
 */
class RelationshipRepository extends Repository {

	protected $option_name = 'cptb_relationships';

	/**
	 * @return array<string,string> type => label
	 */
	public static function relationship_types(): array {
		return array(
			'one_to_one'   => __( 'One to One', 'cpt-builder' ),
			'many_to_one'  => __( 'Many to One', 'cpt-builder' ),
			'one_to_many'  => __( 'One to Many', 'cpt-builder' ),
			'many_to_many' => __( 'Many to Many', 'cpt-builder' ),
		);
	}

	public static function is_multiple( string $type ): bool {
		return in_array( $type, array( 'one_to_many', 'many_to_many' ), true );
	}

	public static function meta_key( string $key ): string {
		return 'cptb_rel_' . $key;
	}
}
