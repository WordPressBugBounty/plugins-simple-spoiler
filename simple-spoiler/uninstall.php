<?php
/**
 * Uninstall script for Simple Spoiler plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options_to_delete = array(
	'simple_spoiler_bg_wrap',
	'simple_spoiler_bg_body',
	'simple_spoiler_br_color',
);

if ( ! is_multisite() ) {

	foreach ( $options_to_delete as $option ) {
		delete_option( $option );
	}

} else {
	global $wpdb;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" );
	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );

		foreach ( $options_to_delete as $option ) {
			delete_option( $option );
		}
	}

	switch_to_blog( $original_blog_id );
}
