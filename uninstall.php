<?php

/**
 * Storefront Aggregator uninstall script.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || __FILE__ != WP_UNINSTALL_PLUGIN  || ! current_user_can( 'activate_plugins' ) ) {
	return;
}

global $wpdb;

// Delete options.
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'storefront_aggregator\_%';");

// Delete posts + data.
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN 'aggregator';" );
$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );
