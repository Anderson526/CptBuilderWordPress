<?php
namespace CPTBuilder\Fields;

use CPTBuilder\Core\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Registers post meta, renders meta boxes and saves field values.
 */
class FieldManager {

	const NONCE_ACTION = 'cptb_save_fields';
	const NONCE_NAME   = 'cptb_fields_nonce';

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function repository(): FieldRepository {
		return $this->plugin->get( FieldRepository::class );
	}

	public function init(): void {
		add_action( 'init', array( $this, 'register_meta' ), 20 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	public static function field_defaults(): array {
		return array(
			'key'           => '',
			'label'         => '',
			'type'          => 'text',
			'description'   => '',
			'placeholder'   => '',
			'default'       => '',
			'required'      => false,
			'options'       => '',
			'min'           => '',
			'max'           => '',
			'rel_post_type' => '',
			'multiple'      => false,
			'rest'          => 'read',
		);
	}

	public function register_meta(): void {
		foreach ( $this->repository()->with_runtime() as $group ) {
			foreach ( (array) ( $group['post_types'] ?? array() ) as $post_type ) {
				foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
					$this->register_field_meta( $post_type, wp_parse_args( $field, self::field_defaults() ) );
				}
			}
		}
	}

	private function register_field_meta( string $post_type, array $field ): void {
		if ( empty( $field['key'] ) ) {
			return;
		}

		$is_multiple = 'relationship' === $field['type'] && ! empty( $field['multiple'] );
		$rest        = $field['rest'];

		$show_in_rest = false;
		if ( 'hidden' !== $rest ) {
			$show_in_rest = $is_multiple
				? array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				)
				: true;
		}

		$auth = 'write' === $rest
			? static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			}
			: '__return_false';

		register_post_meta(
			$post_type,
			$field['key'],
			array(
				'single'            => true,
				'type'              => $this->meta_type( $field ),
				'description'       => $field['label'],
				'show_in_rest'      => $show_in_rest,
				'auth_callback'     => $auth,
				'sanitize_callback' => function ( $value ) use ( $field ) {
					return $this->sanitize_value( $value, $field );
				},
			)
		);
	}

	private function meta_type( array $field ): string {
		if ( 'relationship' === $field['type'] && ! empty( $field['multiple'] ) ) {
			return 'array';
		}
		if ( 'number' === $field['type'] ) {
			return 'number';
		}
		if ( in_array( $field['type'], array( 'image' ), true ) || 'relationship' === $field['type'] ) {
			return 'integer';
		}
		return 'string';
	}

	public function add_meta_boxes(): void {
		foreach ( $this->repository()->with_runtime() as $group ) {
			foreach ( (array) ( $group['post_types'] ?? array() ) as $post_type ) {
				add_meta_box(
					'cptb_group_' . $group['key'],
					$group['title'],
					array( $this, 'render_meta_box' ),
					$post_type,
					'normal',
					'default',
					array( 'group' => $group )
				);
			}
		}
	}

	public function render_meta_box( \WP_Post $post, array $box ): void {
		$group = $box['args']['group'];
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<div class="cptb-fields">';
		foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
			$field = wp_parse_args( $field, self::field_defaults() );
			if ( empty( $field['key'] ) ) {
				continue;
			}
			$value = get_post_meta( $post->ID, $field['key'], true );
			if ( '' === $value && '' !== $field['default'] ) {
				$value = $field['default'];
			}
			$this->render_field( $field, $value );
		}
		echo '</div>';
	}

	private function render_field( array $field, $value ): void {
		$name        = 'cptb_fields[' . esc_attr( $field['key'] ) . ']';
		$id          = 'cptb-field-' . esc_attr( $field['key'] );
		$placeholder = esc_attr( $field['placeholder'] );
		$required    = $field['required'] ? ' <span class="cptb-required">*</span>' : '';

		echo '<div class="cptb-field cptb-field-' . esc_attr( $field['type'] ) . '">';
		echo '<label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $field['label'] ) . '</strong>' . $required . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput

		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea id="%s" name="%s" rows="4" class="widefat" placeholder="%s">%s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					$placeholder, // phpcs:ignore WordPress.Security.EscapeOutput
					esc_textarea( (string) $value )
				);
				break;

			case 'number':
				printf(
					'<input type="number" id="%s" name="%s" value="%s" class="regular-text" step="any"%s%s />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( (string) $value ),
					'' !== $field['min'] ? ' min="' . esc_attr( $field['min'] ) . '"' : '',
					'' !== $field['max'] ? ' max="' . esc_attr( $field['max'] ) . '"' : ''
				);
				break;

			case 'email':
			case 'url':
			case 'date':
			case 'color':
				printf(
					'<input type="%s" id="%s" name="%s" value="%s" class="regular-text" placeholder="%s" />',
					esc_attr( $field['type'] ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( (string) $value ),
					$placeholder // phpcs:ignore WordPress.Security.EscapeOutput
				);
				break;

			case 'checkbox':
				printf(
					'<label class="cptb-checkbox"><input type="checkbox" id="%s" name="%s" value="1" %s /> %s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( '1', (string) $value, false ),
					esc_html( $field['description'] ? $field['description'] : __( 'Yes', 'cpt-builder' ) )
				);
				break;

			case 'select':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" class="regular-text">';
				echo '<option value="">' . esc_html__( '— Select —', 'cpt-builder' ) . '</option>';
				foreach ( self::parse_options( $field['options'] ) as $opt_value => $opt_label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $opt_value ),
						selected( (string) $value, (string) $opt_value, false ),
						esc_html( $opt_label )
					);
				}
				echo '</select>';
				break;

			case 'image':
				$attachment_id = absint( $value );
				$preview       = $attachment_id ? wp_get_attachment_image( $attachment_id, 'thumbnail' ) : '';
				printf(
					'<div class="cptb-image-field"><input type="hidden" id="%s" name="%s" value="%s" /><div class="cptb-image-preview">%s</div><button type="button" class="button cptb-select-image">%s</button> <button type="button" class="button cptb-remove-image"%s>%s</button></div>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $attachment_id ? $attachment_id : '' ),
					$preview, // phpcs:ignore WordPress.Security.EscapeOutput
					esc_html__( 'Select Image', 'cpt-builder' ),
					$attachment_id ? '' : ' style="display:none"',
					esc_html__( 'Remove', 'cpt-builder' )
				);
				break;

			case 'relationship':
				$this->render_relationship_select( $name, $id, $field['rel_post_type'], $value, ! empty( $field['multiple'] ) );
				break;

			case 'text':
			default:
				printf(
					'<input type="text" id="%s" name="%s" value="%s" class="regular-text" placeholder="%s" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( (string) $value ),
					$placeholder // phpcs:ignore WordPress.Security.EscapeOutput
				);
				break;
		}

		if ( $field['description'] && 'checkbox' !== $field['type'] ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
		echo '</div>';
	}

	public function render_relationship_select( string $name, string $id, string $post_type, $value, bool $multiple ): void {
		if ( ! $post_type || ! post_type_exists( $post_type ) ) {
			echo '<p class="description">' . esc_html__( 'Related post type is not registered.', 'cpt-builder' ) . '</p>';
			return;
		}

		$selected = $multiple ? array_map( 'absint', (array) $value ) : array( absint( $value ) );
		$posts    = get_posts(
			array(
				'post_type'   => $post_type,
				'numberposts' => 500,
				'orderby'     => 'title',
				'order'       => 'ASC',
				'post_status' => 'publish',
			)
		);

		printf(
			'<select id="%s" name="%s%s" class="regular-text"%s>',
			esc_attr( $id ),
			esc_attr( $name ),
			$multiple ? '[]' : '',
			$multiple ? ' multiple size="6"' : ''
		);
		if ( ! $multiple ) {
			echo '<option value="">' . esc_html__( '— None —', 'cpt-builder' ) . '</option>';
		}
		foreach ( $posts as $p ) {
			printf(
				'<option value="%d" %s>%s</option>',
				(int) $p->ID,
				in_array( (int) $p->ID, $selected, true ) ? 'selected' : '',
				esc_html( get_the_title( $p ) )
			);
		}
		echo '</select>';
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

		$submitted = isset( $_POST['cptb_fields'] ) && is_array( $_POST['cptb_fields'] )
			? wp_unslash( $_POST['cptb_fields'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per field below.
			: array();

		foreach ( $this->repository()->for_post_type( $post->post_type ) as $group ) {
			foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
				$field = wp_parse_args( $field, self::field_defaults() );
				if ( empty( $field['key'] ) ) {
					continue;
				}

				$raw   = isset( $submitted[ $field['key'] ] ) ? $submitted[ $field['key'] ] : '';
				$clean = $this->sanitize_value( $raw, $field );

				if ( '' === $clean || ( is_array( $clean ) && empty( $clean ) ) ) {
					delete_post_meta( $post_id, $field['key'] );
				} else {
					update_post_meta( $post_id, $field['key'], $clean );
				}
			}
		}
	}

	/**
	 * Sanitize a raw value according to its field definition.
	 *
	 * @param mixed $value Raw value.
	 * @param array $field Field definition.
	 * @return mixed
	 */
	public function sanitize_value( $value, array $field ) {
		switch ( $field['type'] ) {
			case 'number':
				if ( '' === $value || null === $value ) {
					return '';
				}
				$number = (float) $value;
				if ( '' !== $field['min'] && $number < (float) $field['min'] ) {
					$number = (float) $field['min'];
				}
				if ( '' !== $field['max'] && $number > (float) $field['max'] ) {
					$number = (float) $field['max'];
				}
				return $number;

			case 'email':
				return sanitize_email( (string) $value );

			case 'url':
				return esc_url_raw( (string) $value );

			case 'textarea':
				return sanitize_textarea_field( (string) $value );

			case 'checkbox':
				return $value ? '1' : '';

			case 'select':
				$allowed = array_map( 'strval', array_keys( self::parse_options( $field['options'] ) ) );
				return in_array( (string) $value, $allowed, true ) ? (string) $value : '';

			case 'image':
				$id = absint( $value );
				return $id ? $id : '';

			case 'relationship':
				if ( ! empty( $field['multiple'] ) ) {
					$ids = array_filter( array_map( 'absint', (array) $value ) );
					return array_values( $ids );
				}
				$id = absint( is_array( $value ) ? reset( $value ) : $value );
				return $id ? $id : '';

			case 'date':
			case 'color':
			case 'text':
			default:
				return sanitize_text_field( (string) $value );
		}
	}

	/**
	 * Parse "value : Label" lines into an options map.
	 *
	 * @return array<string,string>
	 */
	public static function parse_options( string $raw ): array {
		$options = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			if ( false !== strpos( $line, ':' ) ) {
				list( $value, $label )        = array_map( 'trim', explode( ':', $line, 2 ) );
				$options[ $value ]            = $label;
			} else {
				$options[ $line ] = $line;
			}
		}
		return $options;
	}
}
