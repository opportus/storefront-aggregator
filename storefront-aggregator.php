<?php

/**
 * Plugin Name: Storefront Aggregator
 * Plugin URI: https://github.com/opportus/storefront-aggregator/
 * Author: Clément Cazaud
 * Author URI: https://github.com/opportus/
 * Licence: MIT Licence
 * Licence URI: https://opensource.org/licenses/MIT
 * Description: Flexible and extensible content Aggregator for Storefront. Improves user experience and adds dynamic content to your pages.
 * Version: 0.2
 * Requires at least: 4.4
 * Tested up to 4.6
 * Text Domain: storefront-aggregator
 *
 * NOTES:
 *
 * The design guideline is simplicity > flexibility > extensibility.
 * 
 * This plugin allows seamless integration of aggregate custom items by the help of 3 hooks:
 * `storefront_aggregator_meta_boxes` - `storefront_aggregator_query_items` - `storefront_aggregator_item_template`.
 * See `includes/classes/class-storefront-aggregator-integration-woocommerce/` for reference.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Storefront Aggregator class.
 *
 * Initializes everything.
 */
final class Storefront_Aggregator {

	/**
	 * @var object $_instance Singleton instance
	 */
	private static $_instance;

	/**
	 * @var array $_aggregators Contains aggregator objects
	 */
	private $_aggregators;

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
	private function __construct() {
		add_action( 'init', array( $this, 'init_hooks' ), 0, 0 );
	}

	/**
	 * Initialization hooks.
	 *
	 * Hooked into `init` action hook.
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'init_constants' ),   10, 0 );
		add_action( 'init', array( $this, 'init_integration' ), 20, 0 );
		add_action( 'init', array( $this, 'init_aggregators' ), 30, 0 );

		if ( is_admin() ) {
			if ( current_user_can( 'edit_posts' ) ) {
				add_action( 'init', array( $this, 'init_admin' ), 40, 0 );
			}
		} else {
			add_action( 'template_redirect', array( $this, 'init_frontend' ), 10, 0 );
		}

		add_action( 'customize_register', array( $this, 'init_customizer' ), 10, 1 );
	}

	/**
	 * Initializes constants.
	 *
	 * Hooked into `init` action hook.
	 */
	public function init_constants() {
		define( 'STOREFRONT_AGGREGATOR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'STOREFRONT_AGGREGATOR_URL',  plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Initializes Integration.
	 * 
	 * Hooked into `init` action hook.
	 */
	public function init_integration() {

		// WooCommerce integration.
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			include_once( STOREFRONT_AGGREGATOR_PATH . 'includes/classes/class-storefront-aggregator-integration-woocommerce.php' );

			Storefront_Aggregator_Integration_WooCommerce::get_instance();
		}
	}

	/**
	 * Initializes Aggregators.
	 *
	 * Hooked into `init` action hook.
	 */
	public function init_aggregators() {

		// Aggregator objects generated below are stored into a transient. This transient is deleted on `save_post` action hook.
		if ( false === $aggregators = get_transient( 'storefront_aggregators' ) ) {

			// Picks up Aggregators.
			$query_args = array(
				'post_type'   => 'ultimate_aggregator',
				'post_status' => 'publish',
				'meta_query'  => array(
					array(
						'key' => 'storefront_aggregator_items_type',
					),
					array(
						'key' => 'storefront_aggregator_items_number',
					),
					array(
						'key' => 'storefront_aggregator_domain',
					),
				),
			);

			$query_args = apply_filters( 'storefront_aggregator_query_args', $query_args );
			$query      = new WP_Query( $query_args );
			$posts      = $query->get_posts();

			foreach ( $posts as $aggregator ) {

				// Picks up Aggregator meta once and for all... This will avoid further queries.
				$unserializable_meta = (array) apply_filters( 'storefront_aggregator_unserializable_meta', array( 'storefront_aggregator_domain' ) );

				foreach ( get_post_meta( $aggregator->ID ) as $key => $array ) {
					foreach ( $array as $value ) {
						$meta[ substr( $key, 22 ) ] = in_array( $key, $unserializable_meta ) ? unserialize( $value ) : $value;
					}
				}

				// Picks up Aggregator's Items.
				$items = array();
				
				switch ( $meta['items_type'] ) {
					case 'post':
						$args = array(
							'post_type'      => 'post',
							'post_status'    => 'publish',
							'posts_per_page' => $meta['items_number'],
						);
						$items_query = new WP_Query( $args );
						$items       = $items_query->get_posts();
					break;

					case 'comment':
						$args = array(
							'post_type' => 'post',
							'status'    => 'approved',
							'number'    => $meta['items_number'],
						);
						$items_query = new WP_Comment_Query( $args );
						$items       = $items_query->get_comments();
					break;
				}

				$items = (array) apply_filters( 'storefront_aggregator_items', $items, $meta );

				$aggregator->{ 'meta' }  = $meta;  // Defines `meta` Aggregator's property.
				$aggregator->{ 'items' } = $items; // Defines `items` Aggregator's property.

				$aggregators[ $aggregator->ID ] = $aggregator;
			}

			set_transient( 'storefront_aggregators', $aggregators, 60*60*24 );
		}

		$this->_aggregators = $aggregators;
	}

	/**
	 * Initializes Admin.
	 * 
	 * Hooked into `init` action hook.
	 */
	public function init_admin() {
		include_once( STOREFRONT_AGGREGATOR_PATH . 'includes/classes/class-storefront-aggregator-admin.php' );

		Storefront_Aggregator_Admin::get_instance();
	}

	/**
	 * Initializes Frontend.
	 *
	 * Hooked into `template_redirect` action hook.
	 */
	public function init_frontend() {
		foreach ( $this->_aggregators as $aggregator ) {
			if ( function_exists( $aggregator->meta['domain']['page'] ) && $aggregator->meta['domain']['page']() ) {
				include_once( STOREFRONT_AGGREGATOR_PATH . 'includes/classes/class-storefront-aggregator-frontend.php' );

				new Storefront_Aggregator_Frontend( $aggregator );
			}
		}
	}

	/**
	 * Initializes Customizer.
	 *
	 * Hooked into `customize_register` action hook.
	 *
	 * @param object $wp_customize
	 */
	public function init_customizer( $wp_customize ) {
		include_once( STOREFRONT_AGGREGATOR_PATH . 'includes/classes/class-storefront-aggregator-customizer.php' );
		
		foreach ( $this->_aggregators as $aggregator ) {
			new Storefront_Aggregator_Customizer( $aggregator, $wp_customize );
		}
	}

	/**
	 * Gets Aggregators.
	 *
	 * @param  int          $id (default: null)
	 * @return array|object
	 */
	public function get_aggregators( $id = null ) {
		if ( isset( $id ) ) {
			if ( isset( $this->_aggregators[ (int) $id ] ) ) {
				return $this->_aggregators[ (int) $id ];
			} else {
				return array();
			}
		} else {
			return $this->_aggregators;
		}
	}
}

/**
 * Storefront Aggegator function.
 *
 * Avoids the use of global..
 *
 * @return object Plugin Instance
 */
function storefront_aggregator() {
	$theme = wp_get_theme();

	if ( 'Storefront' == $theme->name || 'storefront' == $theme->template ) {
		return Storefront_Aggregator::get_instance();
	}
}

storefront_aggregator();
