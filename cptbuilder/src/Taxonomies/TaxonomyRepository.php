<?php
namespace CPTBuilder\Taxonomies;

use CPTBuilder\Core\Repository;

defined( 'ABSPATH' ) || exit;

class TaxonomyRepository extends Repository {

	protected $option_name = 'cptb_taxonomies';

	/**
	 * Taxonomy keys that must never be overridden.
	 *
	 * @return string[]
	 */
	public static function reserved_keys(): array {
		return array(
			'category',
			'post_tag',
			'nav_menu',
			'link_category',
			'post_format',
			'wp_theme',
			'wp_template_part_area',
			'wp_pattern_category',
		);
	}
}
