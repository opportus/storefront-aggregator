<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce integration.
 *
 * @version 0.1
 * @author  Clément Cazaud <opportus@gmail.com>
 */

add_action( 'storefront_aggregator_integration', 'storefront_aggregator_wc_integration', 10, 0 );

/**
 * WooCommerce integration.
 */
function storefront_aggregator_wc_integration() {

	// Backend.
	add_filter( 'storefront_aggregator_meta_boxes',    'storefront_aggregator_wc_meta_boxes',       10, 1 );

	// Frontend
	add_action( 'wp_enqueue_scripts',                  'storefront_aggregator_wc_register_scripts', 30, 0 );
	add_filter( 'storefront_aggregator_query_items',   'storefront_aggregator_wc_query_items',      20, 2 );
	add_action( 'storefront_aggregator_item_template', 'storefront_aggregator_wc_item_template',    20, 2 );
}

/**
 * WooCommerce meta boxes.
 *
 * Hooked into `storefront_aggregator_meta_boxes` filter hook.
 *
 * @param  array $meta_boxes
 * @return array $meta_boxes
 */
function storefront_aggregator_wc_meta_boxes( $meta_boxes ) {
	$item_types = array(
		'product' => __( 'Last Products', 'storefront-aggregator' ),
		'review'  => __( 'Last Reviews', 'storefront-aggregator' ),
	);

	$conditions = array(
		'is_product'          => __( 'Product', 'storefront-aggregator' ),
		'is_product_category' => __( 'Product Categories', 'storefront-aggregator' ),
		'is_product_tag'      => __( 'Product Tags', 'storefront-aggregator' ),
		'is_product_taxonomy' => __( 'Product Taxonomy', 'storefront-aggregator' ),
		'is_shop'             => __( 'Shop Page', 'storefront-aggregator' ),
		'is_cart'             => __( 'Cart Page', 'storefront-aggregator' ),
		'is_checkout'         => __( 'Checkout Page', 'storefront-aggregator' ),
		'is_account_page'     => __( 'Account Page', 'storefront-aggregator' ),
	);

	foreach ( $meta_boxes as $key => $meta_box ) {
		if ( 'storefront_aggregator_items_type_meta_box' === $meta_box['id'] ) {
			$meta_boxes[ $key ]['callback_args']['meta_data'] += $item_types;
		
		} elseif ( 'storefront_aggregator_conditions_meta_box' === $meta_box['id'] ) {
			$meta_boxes[ $key ]['callback_args']['meta_data'] += $conditions;
		}
	}

	return $meta_boxes;	
}

/**
 * WooCommerce items.
 *
 * Hooked into `storefront_aggregator_query_items` filter hook.
 *
 * @param  null       $items
 * @param  array      $meta
 * @return null|array $items
 */
function storefront_aggregator_wc_query_items( $items, $meta ) {
	switch ( $meta['items_type'] ) {
		case 'product':
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => $meta['items_number'],
			);

			$query = new WP_Query( $args );
			$items = $query->get_posts();

		break;

		case 'review':
			$args = array(
				'number'    => $meta['items_number'],
				'post_type' => 'product',
			);

			$query = new WP_Comment_Query( $args );
			$items = $query->get_comments();

		break;
	}

	return $items;
}

/**
 * WooCommerce item template.
 *
 * Hooked into `storefront_aggregator_item_template` action hook.
 *
 * @param object $item
 * @param array  $meta
 */
function storefront_aggregator_wc_item_template( $item, $meta ) {
	switch ( $meta['items_type'] ) {
		case 'product':
		case 'review':
			require( plugin_dir_path( __FILE__ ) . '/templates/' . str_replace( '_', '-', $meta['items_type'] ) . '.php' );

		break;
	}
}

/**
 * Registers scripts.
 *
 * Hooked into `wp_enqueue_scripts` action hook.
 */
function storefront_aggregator_wc_register_scripts() {
	if ( get_transient( 'storefront_aggregators' ) ) {
		wp_register_style( 'storefront-aggregator-wc-style', plugins_url( '/', __FILE__ ) . 'assets/woocommerce.min.css' );
	
		wp_enqueue_style( 'storefront-aggregator-wc-style' );
	}
}
