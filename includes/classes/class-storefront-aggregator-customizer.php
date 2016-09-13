<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Storefront Aggregator Customizer class.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

class Storefront_Aggregator_Customizer {

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
			return $this->_instance_count;
		}
	}

	/**
	 * Constructor.
	 *
	 * @param object $aggregator
	 * @param object $wp_customize
	 */
	public function __construct( $aggregator, $wp_customize ) {
		self::$_instance_count ++;
		
		$this->_aggregator = $aggregator;
		
		$this->_add_hooks( $wp_customize );
		$this->_add_settings( $wp_customize );
		$this->_add_controls( $wp_customize );
		$this->_add_section( $wp_customize );

		if ( self::$_instance_count === 1 ) {
			self::_add_panel( $wp_customize );
		}		
	}

	/**
	 * Adds hooks.
	 *
	 * @var object $wp_customize
	 */
	private function _add_hooks( $wp_customize ) {
		if ( $wp_customize->is_preview() ) {
			add_action( 'wp_footer', array( $this, 'preview_script' ), 20 );
		}
	}

	/**
	 * Adds panel.
	 *
	 * @var object $wp_customize
	 */
	private static function _add_panel( $wp_customize ) {
		$panel = array(
			'title'       => __( 'Aggregators', 'storefront-aggregator' ),
			'description' => __( 'Customize your aggregators', 'storefront-aggregator' ),
		);

		$panel = (array) apply_filters( 'storefront_aggregator_customizer_panel', $panel );
		
		$wp_customize->add_panel( 'storefront_aggregator_customizer', $panel );
	}

	/**
	 * Adds section.
	 *
	 * @var object $wp_customize
	 */
	private function _add_section( $wp_customize ) {
		$title        = sprintf( esc_html__( 'Aggregator ID %s', 'storefront-aggregator' ), esc_html( strval( $this->_aggregator->ID ) ) );
		$description  = esc_html__( 'Title' ) . ': ';
		$description .= $this->_aggregator->post_title ? apply_filters( 'the_title', esc_html( $this->_aggregator->post_title ) ) : esc_html__( 'Untitled' );
		$description .= '<br />';
		$description .= esc_html__( 'Page', 'storefront-aggregator' ) . ': ';
		$description .= esc_html( $this->_aggregator->meta['domain']['page'] );
		$description .= '<br />';
		$description .= esc_html__( 'Hook', 'storefront-aggregator' ) . ': ';
		$description .= esc_html( $this->_aggregator->meta['domain']['hook'] );
		$description .= '<br />';
		$description .= esc_html__( 'Priority', 'storefront-aggregator' ) . ': ';
		$description .= esc_html( $this->_aggregator->meta['domain']['priority'] );
		$description .= '<br />';
		$description .= esc_html__( 'Items Type', 'storefront-aggregator' ) . ': ';
		$description .= esc_html( $this->_aggregator->meta['items_type'] );

		$section = array(
			'title'       => $title,
			'description' => $description,
			'panel'       => 'storefront_aggregator_customizer',
		);

		$section = (array) apply_filters( 'storefront_aggregator_customizer_section', $section,  $this->_aggregator );

		include_once( STOREFRONT_AGGREGATOR_PATH . 'includes/classes/class-storefront-aggregator-customizer-section.php' );

		$wp_customize->add_section( new Storefront_Aggregator_Customizer_Section( $wp_customize, 'storefront_aggregator_customizer_' . strval( $this->_aggregator->ID ), $section ) );
	}

	/**
	 * Adds controls.
	 *
	 * @var object $wp_customize
	 */
	private function _add_controls( $wp_customize ) {
		$section  = 'storefront_aggregator_customizer_' . strval( $this->_aggregator->ID );
		$controls = array(
			'background_color'      => array(
				'priority' => 1,
				'section'  => $section,
				'label'    => __( 'Background Color', 'storefront-aggregator' ),
			),
			'title_color'           => array(
				'priority' => 2,
				'section'  => $section,
				'label'    => __( 'Title Color', 'storefront-aggregator' ),
			),
			'content_color'         => array(
				'priority' => 3,
				'section'  => $section,
				'label'    => __( 'Content Color', 'storefront-aggregator' ),
			),
			'item_background_color' => array(
				'priority' => 4,
				'section'  => $section,
				'label'    => __( 'Items Background Color', 'storefront-aggregator' ),
			),
			'item_title_color'      => array(
				'priority' => 5,
				'section'  => $section,
				'label'    => __( 'Items Title Color', 'storefront-aggregator' ),
			),
			'item_content_color'    => array(
				'priority' => 6,
				'section'  => $section,
				'label'    => __( 'Items Content Color', 'storefront-aggregator' ),
			),
			'item_date_color'       => array(
				'priority' => 7,
				'section'  => $section,
				'label'    => __( 'Item Date Color', 'storefront-aggregator' ),
			),
			'item_author_color'     => array(
				'priority' => 8,
				'section'  => $section,
				'label'    => __( 'Item Author Color', 'storefront-aggregator' ),
			),
			'item_border_color'     => array(
				'priority' => 9,
				'section'  => $section,
				'label'    => __( 'Item Border Color', 'storefront-aggregator' ),
			),
		);

		$controls = (array) apply_filters( 'storefront_aggregator_customizer_controls', $controls, $this->_aggregator );

		foreach ( $controls as $setting_id => $values ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'storefront_aggregator_customizer_' . strval( $this->_aggregator->ID ) . '[' . $setting_id . ']', $values ) );
		}
	}

	/**
	 * Adds settings.
	 *
	 * @var object $wp_customize
	 */
	private function _add_settings( $wp_customize ) {
		$settings = array(
			'background_color'      => array(
				'default'           => '#ffffff',
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'title_color'           => array(
				'default'           => get_theme_mod( 'storefront_heading_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'content_color'         => array(
				'default'           => get_theme_mod( 'storefront_text_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'item_background_color' => array(
				'default'           => '#ffffff',
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'item_title_color'      => array(
				'default'           => get_theme_mod( 'storefront_heading_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'item_content_color'    => array(
				'default'           => get_theme_mod( 'storefront_text_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'item_date_color'       => array(
				'default'           => get_theme_mod( 'storefront_text_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'item_author_color'     => array(
				'default'           => get_theme_mod( 'storefront_text_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'item_border_color'     => array(
				'default'           => '#ffffff',
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
		);

		$settings = (array) apply_filters( 'storefront_aggregator_customizer_settings', $settings, $this->_aggregator );
		
		foreach ( $settings as $setting_id => $values ) {
			$wp_customize->add_setting( 'storefront_aggregator_customizer_' . strval( $this->_aggregator->ID ) . '[' . $setting_id . ']', $values );
		}
	}

	/**
	 * Preview script.
	 *
	 * Hooked into `wp_footer` action hook.
	 */
	public function preview_script() {
		$id  = '#storefront-aggregator-' . strval( $this->_aggregator->ID );
		$css = array(
			'background_color'      => array(
				'selector'      => $id . '.storefront-aggregator',
				'attribute'	    => 'background-color',
			),
			'title_color'           => array(
				'selector'      => $id . '.storefront-aggregator__title',
				'attribute'     => 'color',
			),
			'content_color'         => array(
				'selector'      => $id . ' .storefront-aggregator__content',
				'attribute'     => 'color',
			),
			'item_background_color' => array(
				'selector'      => $id . ' .storefront-aggregator__item',
				'attribute'     => 'background-color',
			),
			'item_title_color'      => array(
				'selector'      => $id . ' .storefront-aggregator__item__title',
				'attribute'     => 'color',
			),
			'item_content_color'    => array(
				'selector'      => $id . ' .storefront-aggregator__item__content',
				'attribute'     => 'color',
			),
			'item_date_color'       => array(
				'selector'      => $id . ' .storefront-aggregator__item__date',
				'attribute'     => 'color',
			),
			'item_author_color'     => array(
				'selector'      => $id . ' .storefront-aggregator__item__author',
				'attribute'     => 'color',
			),
			'item_border_color'     => array(
				'selector'      => $id . ' .storefront-aggregator__item',
				'attribute'     => 'border-color',
			),
		);

		foreach ( $css as $setting_id => $setting ) {
			?>
			<script type="text/javascript">
				( function( $ ) {
					wp.customize( '<?php echo esc_js( 'storefront_aggregator_customizer_' . strval( $this->_aggregator->ID ) . '[' . $setting_id . ']' ); ?>', function( value ) {
						value.bind( function( to ) {
							$( '<?php echo esc_js( $setting['selector'] ); ?>' ).css( '<?php echo esc_js( $setting['attribute'] ); ?>', to );
						} );
					} );
				} )( jQuery )
			</script>
			<?php
		}
	}
}
