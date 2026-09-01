<?php
namespace CPTBuilder\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Base repository storing keyed configuration items in a WP option.
 */
abstract class Repository {

	/** @var string */
	protected $option_name = '';

	/**
	 * @return array<string,array>
	 */
	public function all(): array {
		$items = get_option( $this->option_name, array() );
		return is_array( $items ) ? $items : array();
	}

	public function find( string $key ): ?array {
		$items = $this->all();
		return isset( $items[ $key ] ) ? $items[ $key ] : null;
	}

	public function exists( string $key ): bool {
		return null !== $this->find( $key );
	}

	public function count(): int {
		return count( $this->all() );
	}

	/**
	 * Insert or update an item. The item must contain a 'key'.
	 */
	public function save( array $item ): void {
		if ( empty( $item['key'] ) ) {
			return;
		}
		$items                 = $this->all();
		$items[ $item['key'] ] = $item;
		update_option( $this->option_name, $items );
		update_option( 'cptb_flush_needed', 1 );
	}

	public function delete( string $key ): void {
		$items = $this->all();
		if ( isset( $items[ $key ] ) ) {
			unset( $items[ $key ] );
			update_option( $this->option_name, $items );
			update_option( 'cptb_flush_needed', 1 );
		}
	}

	/**
	 * Replace the full collection (used by importer).
	 *
	 * @param array<string,array> $items Items keyed by their key.
	 */
	public function replace_all( array $items ): void {
		update_option( $this->option_name, $items );
		update_option( 'cptb_flush_needed', 1 );
	}
}
