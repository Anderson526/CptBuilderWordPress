<?php
namespace CPTBuilder\PostTypes;

use CPTBuilder\Core\Repository;

defined( 'ABSPATH' ) || exit;

class PostTypeRepository extends Repository {

	protected $option_name = 'cptb_post_types';

	/**
	 * Post type keys that must never be overridden.
	 *
	 * @return string[]
	 */
	public static function reserved_keys(): array {
		return array(
			'post',
			'page',
			'attachment',
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'user_request',
			'wp_block',
			'wp_template',
			'wp_template_part',
			'wp_global_styles',
			'wp_navigation',
			'wp_font_family',
			'wp_font_face',
			'action',
			'author',
			'order',
			'theme',
		);
	}
}
