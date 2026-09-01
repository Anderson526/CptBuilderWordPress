<?php
namespace CPTBuilder\REST;

use CPTBuilder\Core\Plugin;
use CPTBuilder\Fields\FieldRepository;
use CPTBuilder\ImportExport\ExportManager;
use CPTBuilder\PostTypes\PostTypeRepository;
use CPTBuilder\Relationships\RelationshipRepository;
use CPTBuilder\Taxonomies\TaxonomyRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * Internal REST API: /wp-json/cpt-builder/v1/
 */
class RestManager {

	const NAMESPACE = 'cpt-builder/v1';

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * @return array<string,string> route base => repository class.
	 */
	private function collections(): array {
		return array(
			'post-types'    => PostTypeRepository::class,
			'taxonomies'    => TaxonomyRepository::class,
			'field-groups'  => FieldRepository::class,
			'relationships' => RelationshipRepository::class,
		);
	}

	public function permission_check(): bool {
		return current_user_can( 'manage_options' );
	}

	public function register_routes(): void {
		foreach ( $this->collections() as $base => $repo_class ) {
			register_rest_route(
				self::NAMESPACE,
				'/' . $base,
				array(
					array(
						'methods'             => 'GET',
						'permission_callback' => array( $this, 'permission_check' ),
						'callback'            => function () use ( $repo_class ) {
							return rest_ensure_response( array_values( $this->plugin->get( $repo_class )->all() ) );
						},
					),
					array(
						'methods'             => 'POST',
						'permission_callback' => array( $this, 'permission_check' ),
						'callback'            => function ( WP_REST_Request $request ) use ( $repo_class ) {
							return $this->upsert( $repo_class, $request, true );
						},
					),
				)
			);

			register_rest_route(
				self::NAMESPACE,
				'/' . $base . '/(?P<key>[a-z0-9_\-]+)',
				array(
					array(
						'methods'             => 'GET',
						'permission_callback' => array( $this, 'permission_check' ),
						'callback'            => function ( WP_REST_Request $request ) use ( $repo_class ) {
							$item = $this->plugin->get( $repo_class )->find( $request['key'] );
							return $item
								? rest_ensure_response( $item )
								: new WP_Error( 'cptb_not_found', __( 'Item not found.', 'cpt-builder' ), array( 'status' => 404 ) );
						},
					),
					array(
						'methods'             => 'PUT,PATCH',
						'permission_callback' => array( $this, 'permission_check' ),
						'callback'            => function ( WP_REST_Request $request ) use ( $repo_class ) {
							return $this->upsert( $repo_class, $request, false );
						},
					),
					array(
						'methods'             => 'DELETE',
						'permission_callback' => array( $this, 'permission_check' ),
						'callback'            => function ( WP_REST_Request $request ) use ( $repo_class ) {
							$repo = $this->plugin->get( $repo_class );
							if ( ! $repo->exists( $request['key'] ) ) {
								return new WP_Error( 'cptb_not_found', __( 'Item not found.', 'cpt-builder' ), array( 'status' => 404 ) );
							}
							$repo->delete( $request['key'] );
							return rest_ensure_response( array( 'deleted' => true ) );
						},
					),
				)
			);
		}

		register_rest_route(
			self::NAMESPACE,
			'/export',
			array(
				'methods'             => 'GET',
				'permission_callback' => array( $this, 'permission_check' ),
				'callback'            => function () {
					return rest_ensure_response( $this->plugin->get( ExportManager::class )->export_data() );
				},
			)
		);
	}

	/**
	 * Create or update an item.
	 *
	 * @param string          $repo_class Repository class.
	 * @param WP_REST_Request $request    Request.
	 * @param bool            $creating   Whether this is a create (POST) call.
	 * @return WP_REST_Response|WP_Error
	 */
	private function upsert( string $repo_class, WP_REST_Request $request, bool $creating ) {
		$repo = $this->plugin->get( $repo_class );
		$item = $this->sanitize_deep( (array) $request->get_json_params() );

		if ( ! $creating && empty( $item['key'] ) ) {
			$item['key'] = $request['key'];
		}

		$item['key'] = isset( $item['key'] ) ? sanitize_key( $item['key'] ) : '';

		if ( '' === $item['key'] ) {
			return new WP_Error( 'cptb_invalid_key', __( 'A valid "key" is required.', 'cpt-builder' ), array( 'status' => 400 ) );
		}
		if ( $creating && $repo->exists( $item['key'] ) ) {
			return new WP_Error( 'cptb_exists', __( 'An item with this key already exists.', 'cpt-builder' ), array( 'status' => 409 ) );
		}

		$repo->save( $item );

		return rest_ensure_response( $repo->find( $item['key'] ) );
	}

	/**
	 * Recursively sanitize scalar values of an incoming payload.
	 *
	 * @param array $data Raw data.
	 * @return array
	 */
	private function sanitize_deep( array $data ): array {
		$clean = array();
		foreach ( $data as $key => $value ) {
			$key = sanitize_text_field( (string) $key );
			if ( is_array( $value ) ) {
				$clean[ $key ] = $this->sanitize_deep( $value );
			} elseif ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
				$clean[ $key ] = $value;
			} else {
				$clean[ $key ] = sanitize_textarea_field( (string) $value );
			}
		}
		return $clean;
	}
}
