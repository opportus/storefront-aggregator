<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce Integration.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

class Storefront_Aggregator_Integration_WooCommerce {
	
	/**
	 * @var object $_instance
	 */
	private static $_instance;

	/**
	 * Gets singleton instance.
	 *
	 * @return object self::$_instance
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'storefront-aggregator' ), '0.2' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'storefront-aggregator' ), '0.2' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'storefront_aggregator_items',            array( $this, 'items' ),          20, 2 );

		if ( is_admin() ) {
			if ( current_user_can( 'edit_posts' ) ) {
				add_filter( 'storefront_aggregator_meta_boxes',   array( $this, 'meta_boxes' ),     10, 1 );
			}
		} else {
			add_action( 'storefront_aggregator_items_template', array( $this, 'items_template' ), 20, 3 );
		}
	}

	/**
	 * WooCommerce meta boxes.
	 *
	 * Hooked into `storefront_aggregator_meta_boxes` filter hook.
	 *
	 * @param  array $meta_boxes
	 * @return array $meta_boxes
	 */
	public function meta_boxes( $meta_boxes ) {
		$items_type = array(
			'product' => array(),
			'review'  => array(),
		);

		$items_type__ = array(
			'product' => __( 'Last Products', 'storefront-aggregator' ),
			'review'  => __( 'Last Reviews', 'storefront-aggregator' ),
		);

		foreach ( $meta_boxes as $key => $meta_box ) {
			if ( 'storefront_aggregator_items_type_meta_box' === $meta_box['id'] ) {
				$meta_boxes[ $key ]['callback_args']['meta_value']   += $items_type;
				$meta_boxes[ $key ]['callback_args']['meta_value__'] += $items_type__;
			}
		}

		return $meta_boxes;	
	}

	/**
	 * WooCommerce items.
	 *
	 * Hooked into `storefront_aggregator_items` filter hook.
	 *
	 * @param  array $items
	 * @param  array $meta
	 * @return array $items
	 */
	public function items( $items, $meta ) {
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
	 * WooCommerce items template.
	 *
	 * Hooked into `storefront_aggregator_items_template` action hook.
	 *
	 * @param object $items
	 * @param object $aggregator
	 * @param int    $item_count
	 */
	public function items_template( $item, $aggregator, $item_count ) {
		switch ( $aggregator->meta['items_type'] ) {
			case 'product':
			case 'review':
				require( STOREFRONT_AGGREGATOR_PATH . 'includes/templates/' . str_replace( '_', '-', $aggregator->meta['items_type'] ) . '.php' );

			break;
		}
	}
}
