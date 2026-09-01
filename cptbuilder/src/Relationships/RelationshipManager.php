<?php
namespace CPTBuilder\Relationships;

use CPTBuilder\Core\Plugin;
use CPTBuilder\Fields\FieldManager;

defined( 'ABSPATH' ) || exit;

/**
 * Renders relationship meta boxes on the "from" post type and stores
 * selected post IDs in post meta.
 */
class RelationshipManager {

	const NONCE_ACTION = 'cptb_save_relationships';
	const NONCE_NAME   = 'cptb_relationships_nonce';

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function repository(): RelationshipRepository {
		return $this->plugin->get( RelationshipRepository::class );
	}

	public function init(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	public function add_meta_boxes(): void {
		foreach ( $this->repository()->all() as $rel ) {
			if ( empty( $rel['from'] ) || ! post_type_exists( $rel['from'] ) ) {
				continue;
			}
			add_meta_box(
				'cptb_rel_' . $rel['key'],
				$rel['label'],
				array( $this, 'render_meta_box' ),
				$rel['from'],
				'side',
				'default',
				array( 'relationship' => $rel )
			);
		}
	}

	public function render_meta_box( \WP_Post $post, array $box ): void {
		$rel      = $box['args']['relationship'];
		$multiple = RelationshipRepository::is_multiple( $rel['type'] );
		$meta_key = RelationshipRepository::meta_key( $rel['key'] );
		$value    = get_post_meta( $post->ID, $meta_key, true );

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		/** @var FieldManager $fields */
		$fields = $this->plugin->get( FieldManager::class );
		$fields->render_relationship_select(
			'cptb_relationships[' . esc_attr( $rel['key'] ) . ']',
			'cptb-rel-' . esc_attr( $rel['key'] ),
			(string) $rel['to'],
			$value,
			$multiple
		);
	}

	public function save_post( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$submitted = isset( $_POST['cptb_relationships'] ) && is_array( $_POST['cptb_relationships'] )
			? wp_unslash( $_POST['cptb_relationships'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized below.
			: array();

		foreach ( $this->repository()->all() as $rel ) {
			if ( $rel['from'] !== $post->post_type ) {
				continue;
			}

			$meta_key = RelationshipRepository::meta_key( $rel['key'] );
			$raw      = isset( $submitted[ $rel['key'] ] ) ? $submitted[ $rel['key'] ] : '';

			if ( RelationshipRepository::is_multiple( $rel['type'] ) ) {
				$ids = array_values( array_filter( array_map( 'absint', (array) $raw ) ) );
				if ( empty( $ids ) ) {
					delete_post_meta( $post_id, $meta_key );
				} else {
					update_post_meta( $post_id, $meta_key, $ids );
				}
			} else {
				$id = absint( is_array( $raw ) ? reset( $raw ) : $raw );
				if ( $id ) {
					update_post_meta( $post_id, $meta_key, $id );
				} else {
					delete_post_meta( $post_id, $meta_key );
				}
			}
		}
	}
}
