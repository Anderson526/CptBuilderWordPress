<?php
/**
 * Uninstall handler. Removes plugin data only when the user opted in.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$cptb_settings = get_option( 'cptb_settings', array() );

if ( empty( $cptb_settings['delete_data_on_uninstall'] ) ) {
	return;
}

delete_option( 'cptb_post_types' );
delete_option( 'cptb_taxonomies' );
delete_option( 'cptb_field_groups' );
delete_option( 'cptb_relationships' );
delete_option( 'cptb_settings' );
delete_option( 'cptb_flush_needed' );
