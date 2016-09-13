<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Storefront Aggregator Frontend class.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

class Storefront_Aggregator_Frontend {

	/**
	 * @var int $_instance_count
	 */
	private static $_instance_count;

	/**
	 * @var object $_aggregator
	 */
	private $_aggregator;

	/**
	 * Lazy getter.
	 *
	 * @param  string $var
	 * @return mixed
	 */
	public function __get( $var ) {
		if ( '_aggregator' === $var || 'aggregator' === $var ) {
			return $this->_aggregator;
		
		} elseif ( '_instance_count' === $var || 'instance_count' === $var ) {
			return self::$_instance_count;
		
		} elseif ( array_key_exists( $var, get_object_vars( $this->_aggregator ) ) ) {
			return $this->_aggregator->$var;
		}
	}

	/**
	 * Constructor.
	 *
	 * @param object $aggregator
	 */
	public function __construct( $aggregator ) {
		self::$_instance_count ++;

		$this->_aggregator = $aggregator;
		
		add_action( $this->_aggregator->meta['domain']['hook'], array( $this, 'output' ), $this->_aggregator->meta['domain']['priority'] );
		add_action( 'wp_enqueue_scripts',                       array( $this, 'add_styles' ),                                  20, 0 );
		add_action( 'storefront_aggregator_template',           array( 'Storefront_Aggregator_Frontend', 'template' ),         10, 1 );
		add_action( 'storefront_aggregator_items_template',     array( 'Storefront_Aggregator_Frontend', 'items_template' ),   10, 3 );

		if ( self::$_instance_count === 1 ) {
			add_action( 'wp_enqueue_scripts', array( 'Storefront_Aggregator_Frontend', 'enqueue_scripts' ), 10, 0 );
		}
	}

	/**
	 * Aggregator output.
	 *
	 * Hooked into `this->meta['domain']['hook']` action hook.
	 */
	public function output() {
		do_action( 'storefront_aggregator_template', $this->_aggregator );
	}
	
	/**
	 * Aggregator template.
	 *
	 * Hooked into `storefront_aggregator_template` action hook.
	 *
	 * @param object $aggregator
	 */
	public static function template( $aggregator ) {
		include( STOREFRONT_AGGREGATOR_PATH . 'includes/templates/aggregator.php' );
	}

	/**
	 * Items template.
	 *
	 * Hooked into `storefront_aggregator_items_template` action hook.
	 *
	 * @param object $item
	 * @param object $aggregator
	 * @param int    $item_count
	 */
	public static function items_template( $item, $aggregator, $item_count ) {
		switch ( $aggregator->meta['items_type'] ) {
			case 'post':
			case 'comment':
				include( STOREFRONT_AGGREGATOR_PATH . 'includes/templates/' . str_replace( '_', '-', $aggregator->meta['items_type'] ) . '.php' );

			break;
		}
	}
	
	/**
	 * Registers and enqueues scripts and styles.
	 *
	 * Hooked into `wp_enqueue_scripts` action hook.
	 */
	public static function enqueue_scripts() {
		wp_register_style( 'storefront-aggregator-flexslider-style', STOREFRONT_AGGREGATOR_URL . 'assets/flexslider/flexslider.min.css' );
		wp_register_style( 'storefront-aggregator-style',            STOREFRONT_AGGREGATOR_URL . 'assets/storefront-aggregator.min.css' );

		wp_register_script( 'storefront-aggregator-flexslider-init', STOREFRONT_AGGREGATOR_URL . 'assets/flexslider/flexslider-init.min.js', array( 'storefront-aggregator-flexslider' ) );
		wp_register_script( 'storefront-aggregator-flexslider',      STOREFRONT_AGGREGATOR_URL . 'assets/flexslider/jquery.flexslider-min.js', array( 'jquery' ) );
	
		wp_enqueue_style( 'storefront-aggregator-flexslider-style' );
		wp_enqueue_style( 'storefront-aggregator-style' );

		wp_enqueue_script( 'storefront-aggregator-flexslider-init' );
		wp_enqueue_script( 'storefront-aggregator-flexslider' );
	}

	/**
	 * Adds styles.
	 */
	public function add_styles() {
		$id  = '#storefront-aggregator-' . strval( $this->_aggregator->ID );
		$css = array(
			'background_color'      => array(
				'selector'      => $id . '.storefront-aggregator',
				'attribute'	    => 'background-color',
				'default_value' => '#ffffff',
			),
			'title_color'           => array(
				'selector'      => $id . '.storefront-aggregator__title',
				'attribute'     => 'color',
				'default_value' => get_theme_mod( 'storefront_heading_color' ),
			),
			'content_color'         => array(
				'selector'      => $id . ' .storefront-aggregator__content',
				'attribute'     => 'color',
				'default_value' => get_theme_mod( 'storefront_text_color' ),
			),
			'item_background_color' => array(
				'selector'      => $id . ' .storefront-aggregator__item',
				'attribute'     => 'background-color',
				'default_value' => '#ffffff',
			),
			'item_title_color'      => array(
				'selector'      => $id . ' .storefront-aggregator__item__title',
				'attribute'     => 'color',
				'default_value' => get_theme_mod( 'storefront_heading_color' ),
			),
			'item_content_color'    => array(
				'selector'      => $id . ' .storefront-aggregator__item__content',
				'attribute'     => 'color',
				'default_value' => get_theme_mod( 'storefront_text_color' ),
			),
			'item_date_color'       => array(
				'selector'      => $id . ' .storefront-aggregator__item__date',
				'attribute'     => 'color',
				'default_value' => get_theme_mod( 'storefront_text_color' ),
			),
			'item_author_color'     => array(
				'selector'      => $id . ' .storefront-aggregator__item__author',
				'attribute'     => 'color',
				'default_value' => get_theme_mod( 'storefront_text_color' ),
			),
			'item_border_color'     => array(
				'selector'      => $id . ' .storefront-aggregator__item',
				'attribute'     => 'border-color',
				'default_value' => '#ffffff',
			),
		);

		$css     = (array) apply_filters( 'storefront_aggregator_css', $css, $this->_aggregator->ID );
		$options = get_option( 'storefront_aggregator_customizer_' . strval( $this->_aggregator->ID ) );
		$styles  = '';

		foreach ( $css as $setting_id => $setting ) {
			$value   = isset( $options[ $setting_id ] ) && ! empty( $options[ $setting_id ] ) ? $options[ $setting_id ] : $css[ $setting_id ]['default_value'];	
			$styles .= $setting['selector'] . '{' .  $setting['attribute'] . ':' .  $value . '}';
		}

		wp_add_inline_style( 'storefront-aggregator-style', wp_kses( wp_strip_all_tags( $styles ), array( "\'", '\"' ) ) );
	}
}
