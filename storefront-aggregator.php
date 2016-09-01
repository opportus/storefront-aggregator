<?php

/**
 * Plugin Name: Storefront Aggregator
 * Plugin URI: https://github.com/opportus/storefront-aggregator/
 * Description: Multi Item Aggregator for Storefront. Improves user experience and adds dynamic content to your pages.
 * Version: 0.1
 * Author: Clément Cazaud
 * Author URI: https://github.com/opportus/
 * Licence: MIT Licence
 * Licence URI: https://opensource.org/licenses/MIT
 * Requires at least: 4.4
 * Tested up to 4.6
 * Text Domain: storefront-aggregator
 *
 * NOTES
 * The design guideline is simplicity, flexibility and extensivity.
 * This plugin allows seamless integration of aggregate custom items with the help of 3 hooks:
 * `storefront_aggregator_meta_boxes` - `storefront_aggregator_query_items` - `storefront_aggregator_item_template`.
 * See `integration/woocommerce/` for more details.
 *
 * @version 0.1
 * @author  Clément Cazaud <opportus@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

storefront_aggregator_define_constants();
storefront_aggregator_add_hooks();
storefront_aggregator_integration();

/**
 * Defines constants. 
 */
function storefront_aggregator_define_constants() {
	define( 'SA_POST_TYPE', apply_filters( 'storefront_aggregator_post_type', 'aggregator' ) );
}

/**
 * Adds hooks.
 */
function storefront_aggregator_add_hooks() {
	
	// ------ Backend ------ //
	add_action( 'init',                                               'storefront_aggregator_register_post_type',  10, 0 );
	add_action( 'save_post_' . SA_POST_TYPE,                          'storefront_aggregator_save_meta_boxes',     10, 2 );
	add_filter( 'manage_' . SA_POST_TYPE . '_posts_columns',          'storefront_aggregator_posts_columns',       10, 1 );
	add_filter( 'manage_' . SA_POST_TYPE . '_posts_custom_column',    'storefront_aggregator_posts_custom_column', 10, 2 );
	add_filter( 'is_protected_meta',                                  'storefront_aggregator_protect_meta',        10, 2 );
	add_action( 'customize_register',                                 'storefront_aggregator_customize_register',  10, 1 );
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'storefront_aggregator_action_links',        10, 1 );

	// ----- Frontend ------ //
	add_action( 'template_redirect',                                  'storefront_aggregator_init',                10, 0 );
	add_action( 'wp_enqueue_scripts',                                 'storefront_aggregator_enqueue_scripts',     10, 0 );
	add_action( 'wp_enqueue_scripts',                                 'storefront_aggregator_customizer_style',    20, 0 );
	add_action( 'storefront_before_footer',                           'storefront_aggregator_output',              10, 0 );
	add_filter( 'storefront_aggregator_query_items',                  'storefront_aggregator_query_items',         10, 2 );
	add_action( 'storefront_aggregator_template',                     'storefront_aggregator_template',            10, 3 );
	add_action( 'storefront_aggregator_item_template',                'storefront_aggregator_item_template',       10, 2 );
}

/**
 * Integration.
 */
function storefront_aggregator_integration() {
	
	// WooCommerce integration.
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		require_once( plugin_dir_path( __FILE__ ) . '/integration/woocommerce/woocommerce.php' );
	}

	do_action( 'storefront_aggregator_integration' );
}

/*
 |-------------------------------------------------------------------
 | Frontend Section
 |-------------------------------------------------------------------
 |
 */

/**
 * Initializes aggregator.
 * 
 * Hooked into `template_redirect` action hook.
 */
function storefront_aggregator_init() {
	$query         = new WP_Query( array( 'post_type' => SA_POST_TYPE ) );
	$q_aggregators = $query->get_posts();

	foreach ( $q_aggregators as $q_aggregator ) {
		$conditions = get_post_meta( $q_aggregator->ID, 'storefront_aggregator_conditions', true );

		foreach ( $conditions as $condition => $value ) {
			if ( $value == true && function_exists( $condition ) && $condition() ) {
				$aggregators[ $q_aggregator->ID ] = $q_aggregator;

				break;
			}
		}
	}

	if ( isset( $aggregators ) ) {
		set_transient( 'storefront_aggregators', $aggregators, 60 * 60 * 12 );
	}
}

/**
 * Outputs aggregator.
 *
 * Hooked into `storefront_before_footer` action hook.
 *
 * @return void
 */
function storefront_aggregator_output() {
	$aggregators = get_transient( 'storefront_aggregators' );
	
	delete_transient( 'storefront_aggregators' );

	if ( ! $aggregators ) {
		return;
	}
	
	foreach ( $aggregators  as $id => $aggregator ) {
		
		foreach ( get_post_meta( $id ) as $key => $array ) {
			foreach ( $array as $value ) {
				$meta[ substr( $key, 22 ) ] = $value;
			}
		}

		/**
		 * Hook here your custom items query.
		 *
		 * Hook used by `storefront_aggregator_query_items( $items, $meta )` - 10
		 */
		$items = apply_filters( 'storefront_aggregator_query_items', null, $meta );
		
		if ( ! is_array( $items ) || count( $items ) < 4 ) {
			continue;
		}

		/**
		 * Hook used by `storefront_aggregator_template( $items, $aggregator, $meta )` - 10
		 */
		do_action( 'storefront_aggregator_template', $items, $aggregator, $meta );
	}
}

/**
 * Queries items.
 *
 * Hooked into `storefront_aggregator_query_items` filter hook.
 *
 * @param  null       $items
 * @param  array      $meta
 * @return null|array $items
 */
function storefront_aggregator_query_items( $items, $meta ) {
	switch ( $meta['items_type'] ) {
		case 'post':
			$args = array(
				'post_type'      => 'post',
				'posts_per_page' => $meta['items_number'],
			);

			$query = new WP_Query( $args );
			$items = $query->get_posts();

		break;

		case 'comment':
			$args = array(
				'post_type' => 'post',
				'number'    => $meta['items_number'],
			);

			$query = new WP_Comment_Query( $args );
			$items = $query->get_comments();

		break;
	}

	return $items;
}

/**
 * Main template.
 *
 * Hooked into `storefront_aggregator_template` action hook.
 *
 * @param array  $items
 * @param object $aggregator
 * @param array  $meta
 */
function storefront_aggregator_template( $items, $aggregator, $meta ) {
	include( plugin_dir_path( __FILE__ ) . '/templates/aggregator.php' );
}

/**
 * Item template.
 *
 * Hooked into `storefront_aggregator_item_template` action hook.
 *
 * @param object $item
 * @param array  $meta
 */
function storefront_aggregator_item_template( $item, $meta ) {
	switch ( $meta['items_type'] ) {
		case 'post':
		case 'comment':
			include( plugin_dir_path( __FILE__ ) . '/templates/' . str_replace( '_', '-', $meta['items_type'] ) . '.php' );

		break;
	}
}

/**
 * Registers and enqueues scripts and styles.
 *
 * Hooked into `wp_enqueue_scripts` action hook.
 */
function storefront_aggregator_enqueue_scripts() {
	if ( get_transient( 'storefront_aggregators' ) ) {
		wp_register_style( 'storefront-aggregator-flexslider-style', plugins_url( '/', __FILE__ ) . 'assets/flexslider/flexslider.min.css' );
		wp_register_style( 'storefront-aggregator-style',            plugins_url( '/', __FILE__ ) . 'assets/storefront-aggregator.min.css' );

		wp_register_script( 'storefront-aggregator-flexslider-init', plugins_url( '/', __FILE__ ) . 'assets/flexslider/flexslider-init.min.js', array( 'storefront-aggregator-flexslider' ) );
		wp_register_script( 'storefront-aggregator-flexslider',      plugins_url( '/', __FILE__ ) . 'assets/flexslider/jquery.flexslider-min.js', array( 'jquery' ) );
	
		wp_enqueue_style( 'storefront-aggregator-flexslider-style' );
		wp_enqueue_style( 'storefront-aggregator-style' );

		wp_enqueue_script( 'storefront-aggregator-flexslider-init' );
		wp_enqueue_script( 'storefront-aggregator-flexslider' );
	}
}

/**
 * Customizer style.
 *
 * Hooked into `wp_enqueue_scripts` action hook.
 */
function storefront_aggregator_customizer_style() {
	$options  = get_option( 'storefront_aggregator_customizer' );
	$settings = storefront_aggregator_customizer_settings();
	$style    = '';

	foreach ( $settings as $id => $setting ) {
		$value = isset( $options[ $id ] ) && ! empty( $options[ $id ] ) ? $options[ $id ] : $setting['setting']['default'];

		foreach ( $setting['css'] as $selector => $attribute ) {
			$style .= "$selector { $attribute: $value; }";
		}
	}

	$style = apply_filters( 'storefront_aggregator_customizer_style', $style, $settings );

	wp_add_inline_style( 'storefront-aggregator-style', wp_kses( wp_strip_all_tags( $style ), array( "\'", '\"' ) ) );
}

/*
 |-------------------------------------------------------------------
 | Backend Section
 |-------------------------------------------------------------------
 |
 */

/**
 * Registers post type.
 *
 * Hooked into `init` action hook.
 */
function storefront_aggregator_register_post_type() {	
	$args = array(
		'labels'               => array(
			'name'               => _x( 'Aggregator', 'post type singular name', 'storefront-aggregator' ),
			'singular_name'      => _x( 'Aggregators', 'post type general name', 'storefront-aggregator' ),
			'add_new'            => _x( 'Add New Aggregator', 'add new aggregator', 'storefront-aggregator' ),
			'add_new_item'       => __( 'Add New Aggregator', 'storefront-aggregator' ),
			'edit_item'          => __( 'Edit Aggregator', 'storefront-aggregator' ),
			'new_item'           => __( 'New Aggregator', 'storefront-aggregator' ),
			'view_item'          => __( 'View Aggregator', 'storefront-aggregator' ),
			'search_items'       => __( 'Search Aggregators', 'storefront-aggregator' ),
			'not_found'          => __( 'No Aggregators found', 'storefront-aggregator' ),
			'not_found_in_trash' => __( 'No Aggregators found in trash', 'storefront-aggregator' ),
			'all_items'          => __( 'Aggregators', 'storefront-aggregator' ),
		),
		'public'               => false,
		'show_ui'              => true,
		'show_in_menu'         => 'themes.php',
		'show_in_admin_bar'    => false,
		'supports'             => array( 'title', 'editor' ),
		'register_meta_box_cb' => 'storefront_aggregator_add_meta_boxes',
	);

	$args = apply_filters( 'storefront_aggregator_post_type_args', $args, SA_POST_TYPE );

	register_post_type( SA_POST_TYPE, $args );
}

/**
 * Meta boxes.
 *
 * @return array $meta_boxes
 */
function storefront_aggregator_meta_boxes() {
	$meta_boxes = array(
		array(
			'id'             => 'storefront_aggregator_items_type_meta_box',
			'title'          => __( 'Items Type', 'storefront-aggregator' ),
			'callback'       => 'storefront_aggregator_output_meta_boxes',
			'screen'         => SA_POST_TYPE,
			'context'        => 'side',
			'priority'       => 'default',
			'callback_args'  => array(
				'meta_box_id'  => 'storefront_aggregator_items_type_meta_box',
				'meta_key'     => 'storefront_aggregator_items_type',
				'meta_data'    => array(
					'post'    => __( 'Last Posts', 'storefront-aggregator' ),
					'comment' => __( 'Last Comments', 'storefront-aggregator' ),
				),
				'nonce_action' => 'storefront_aggregator_items_type_meta_box_nonce',
				'nonce_name'   => 'storefront_aggregator_items_type_nonce',
			),
		),
		array(
			'id'             => 'storefront_aggregator_items_number_meta_box',
			'title'          => __( 'Items Number', 'storefront-aggregator' ),
			'callback'       => 'storefront_aggregator_output_meta_boxes',
			'screen'         => SA_POST_TYPE,
			'context'        => 'side',
			'priority'       => 'default',
			'callback_args'  => array(
				'meta_box_id'  => 'storefront_aggregator_items_number_meta_box',
				'meta_key'     => 'storefront_aggregator_items_number',
				'meta_data'    => array(
					'min' => 4,
				),
				'nonce_action' => 'storefront_aggregator_items_number_meta_box_nonce',
				'nonce_name'   => 'storefront_aggregator_items_number_nonce',
			),
		),
		array(
			'id'             => 'storefront_aggregator_conditions_meta_box',
			'title'          => __( 'Display on', 'storefront-aggregator' ),
			'callback'       => 'storefront_aggregator_output_meta_boxes',
			'screen'         => SA_POST_TYPE,
			'context'        => 'side',
			'priority'       => 'default',
			'callback_args'  => array(
				'meta_box_id'  => 'storefront_aggregator_conditions_meta_box',
				'meta_key'     => 'storefront_aggregator_conditions',
				'meta_data'    => array(
					'is_home'       => __( 'Home', 'storefront-aggregator' ),
					'is_front_page' => __( 'Front Page', 'storefront-aggregator' ),
					'is_archive'    => __( 'Archive Page', 'storefront-aggregator' ),
					'is_category'   => __( 'Category Page', 'storefront-aggregator' ),
					'is_tag'        => __( 'Tag Page', 'storefront-aggregator' ),
					'is_search'     => __( 'Search Page', 'storefront-aggregator' ),
					'is_page'       => __( 'Page', 'storefront-aggregator' ),
				),
				'nonce_action' => 'storefront_aggregator_conditions_meta_box_nonce',
				'nonce_name'   => 'storefront_aggregator_conditions_nonce',
			),
		),
	);

	return apply_filters( 'storefront_aggregator_meta_boxes', $meta_boxes, SA_POST_TYPE );
}

/**
 * Adds meta boxes.
 * 
 * Callback of `register_post_type()`
 */
function storefront_aggregator_add_meta_boxes() {
	foreach ( storefront_aggregator_meta_boxes() as $meta_box ) {
		add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'], $meta_box['callback_args'] );
	}
}

/**
 * Outputs meta boxes.
 *
 * Callback of `add_meta_box()`.
 *
 * @param object $post
 * @param array  $callback_args
 */
function storefront_aggregator_output_meta_boxes( $post, $callback_args ) {
	$meta_box_id  = $callback_args['args']['meta_box_id'];
	$meta_key     = $callback_args['args']['meta_key'];
	$meta_data    = $callback_args['args']['meta_data'];
	$nonce_action = $callback_args['args']['nonce_action'];
	$nonce_name   = $callback_args['args']['nonce_name'];
	$post_meta    = get_post_meta( $post->ID, $meta_key, true );

	switch ( $meta_box_id ) {
		case 'storefront_aggregator_items_type_meta_box':
			echo '<p class="description">' . esc_html__( 'Select the type of items you wish to aggregate...', 'storefront-aggregator' ) . '</p>';
			echo '<select id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '">';
			echo '<option value="">' . esc_html__( 'Select...', 'storefront-aggregator' ) . '</option>';

			foreach ( $meta_data as $slug => $name ) {
				$selected = ( $slug === $post_meta ) ? 'selected ' : '';

				echo '<option ' . $selected . 'value="' . esc_attr( $slug ) . '">' . esc_html( $name ) . '</option>';
			}

			echo '</select>';
				
			wp_nonce_field( $nonce_action, $nonce_name );

		break;
			
		case 'storefront_aggregator_items_number_meta_box':
			echo '<p class="description">' . esc_html__( 'How many items do you want to aggregate ? (4 minimum)', 'storefront-aggregator' ) . '</p>';
			echo '<input id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" type="number" min="' . esc_attr( $meta_data['min'] ) . '" value="' . esc_attr( intval( $post_meta ) ) . '" />';
					
			wp_nonce_field( $nonce_action, $nonce_name );

		break;

		case 'storefront_aggregator_conditions_meta_box':
			$i = 0;

			echo '<ul>';
			echo '<p class="description">' . esc_html__( 'Check the pages on which you wish to aggregate the selected items...', 'storefront-aggregator' ) . '</p>';

			foreach ( $meta_data as $slug => $name ) {
				$meta_value = isset( $post_meta[ $slug ] ) && ! empty( $post_meta[ $slug ] ) ? true : false;

				echo '<li>';
				echo '<label for="' . esc_attr( $meta_key . '_' . $i ) . '">';
				echo '<input name="' . esc_attr( $meta_key . '[' . $slug . ']' ) . '" type="hidden" value="" />';
				echo '<input id="' . esc_attr( $meta_key . '_' . $i ++ ) . '" name="' . esc_attr( $meta_key . '[' . $slug . ']' ) . '" type="checkbox" value="1"' . checked( $meta_value, 1, false ) . '  />';
				echo esc_html( $name );
				echo '</label>';
				echo '</li>';
			}

			echo '</ul>';
				
			wp_nonce_field( $nonce_action, $nonce_name );
				
		break;
	}
}	

/**
 * Saves meta boxes.
 *
 * Hooked into `save_post_${post_type}` action hook.
 *
 * @param  int    $post_id
 * @param  object $post
 * @return int    $post_id
 */
function storefront_aggregator_save_meta_boxes( $post_id, $post ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( storefront_aggregator_meta_boxes() as $meta_box ) {
		$meta_key     = $meta_box['callback_args']['meta_key'];
		$nonce_action = $meta_box['callback_args']['nonce_action'];
		$nonce_name   = $meta_box['callback_args']['nonce_name'];

		if ( isset( $_POST[ $meta_key ] ) &&  wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
			$meta_value = $_POST[ $meta_key ];

			if ( is_array( $meta_value ) ) {
				$meta_data  = isset( $meta_box['callback_args']['meta_data'] ) ? $meta_box['callback_args']['meta_data'] : array();
				$meta_value = array_intersect_key( $meta_value, $meta_data );
			}

			update_post_meta( $post_id, sanitize_key( $meta_key ), storefront_aggregator_sanitize_meta( $meta_value ) );
		}
	}
}

/**
 * Sanitizes meta data.
 *
 * @param  array|string $data
 * @return array|string $sanitized_data
 */
function storefront_aggregator_sanitize_meta( $data ) {
	if ( is_array( $data ) ) {
		foreach ( $data as $key => $value ) {
			$sanitized_data[ sanitize_key( $key ) ] = storefront_aggregator_sanitize_meta( $value );
		}

		return $sanitized_data;
	} elseif ( is_string( $data ) ) {
		return $sanitized_data = sanitize_text_field( $data );
	} else {
		return '';
	}
}

/**
 * Columns.
 *
 * Hooked into `manage_${post_type}_posts_columns` filter hook.
 *
 * @param  array $columns
 * @return array $columns
 */
function storefront_aggregator_posts_columns( $columns ) {
	return $columns = array(
		'cb'                                 => '<input type="checkbox" />',
		'title'                              => __( 'Title' ),
		'storefront_aggregator_items_type'   => __( 'Items Type', 'storefront-aggregator' ),
		'storefront_aggregator_items_number' => __( 'Items Number', 'storefront-aggregator' ),
		'storefront_aggregator_conditions'   => __( 'Display on', 'storefront-aggregator' ),
		'date'                               => __( 'Date' ),
	);
}

/**
 * Custom column.
 *
 * Hooked into `manage_${post_type}_posts_custom_column` filter hook.
 *
 * @param array $column_name
 * @param int   $post_id
 */
function storefront_aggregator_posts_custom_column( $column_name, $post_id ) {
	switch ( $column_name ) {
		case 'storefront_aggregator_items_type':
			echo sprintf( esc_html__( '%s', 'storefront-aggregator' ), ucwords( str_replace( '_', ' ', get_post_meta( $post_id, $column_name, true ) ) ) );

		break;

		case 'storefront_aggregator_items_number':
			echo esc_html( get_post_meta( $post_id, $column_name, true ) );

		break;

		case 'storefront_aggregator_conditions':
			$conditions = get_post_meta( $post_id, $column_name, true );

			foreach ( $conditions as $condition => $value ) {
				if ( true == $value ) {
					echo sprintf( esc_html__( '%s', 'storefront-aggregator' ), ucwords(  str_replace( '_', ' ', substr( $condition, 3 ) ) ) ) . '<br />';
				}
			}

		break;
	}
}

/**
 * Protects meta.
 *
 * Hooked into `is_protected_meta` action hook.
 *
 * @param  bool $protected
 * @param  int $meta_key
 * @return bool
 */
function storefront_aggregator_protect_meta( $protected, $meta_key ) {
	foreach ( storefront_aggregator_meta_boxes() as $meta_box ) {
		if ( $meta_key === $meta_box['callback_args']['meta_key'] ) {
			return true;
		}
	}

	return $protected;
}

/**
 * Registers customizer.
 *
 * Hooked into `customize_register` action hook.
 *
 * @param object $wp_customize
 */
function storefront_aggregator_customize_register( $wp_customize ) {
	foreach ( storefront_aggregator_customizer_settings() as $id => $setting ) {
		$wp_customize->add_setting( 'storefront_aggregator_customizer[' . $id . ']', $setting['setting'] );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'storefront_aggregator_customizer[' . $id . ']', $setting['control'] ) );
	}

	$wp_customize->add_section( 'storefront_aggregator_customizer', array(
		'title'       => __( 'Aggregator', 'storefront-aggregator' ),
		'description' => __( 'Customize your aggregator !', 'storefront-aggregator' ),
		'capability'  => 'edit_theme_options',
	) );

	if ( $wp_customize->is_preview() ) {
		add_action( 'wp_footer', 'storefront_aggregator_customizer_preview_script', 20 );
	}
}

/**
 * Customizer settings.
 *
 * @return array $settings
 */
function storefront_aggregator_customizer_settings() {
	$settings = apply_filters( 'storefront_aggregator_customizer_settings', array(

		// ----- Aggregator background color ----- //
		'background_color' => array(
			'setting' => array(
				'default'           => '#ffffff',
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 1,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Background Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator' => 'background-color',
			),
		),

		// ----- Aggregator title color ----- //
		'title_color' => array(
			'setting' => array(
				'default'           => get_theme_mod( 'storefront_heading_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 2,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Title Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator__title' => 'color',
			),
		),

		// ----- Aggregator content color ----- //
		'content_color' => array(
			'setting' => array(
				'default'           => get_theme_mod( 'storefront_text_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 3,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Content Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator__content' => 'color',
			),
		),

		// ----- Item background color ----- //
		'item_background_color' => array(
			'setting' => array(
				'default'           => '#ffffff',
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 4,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Items Background Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator__item' => 'background-color',
			),
		),

		// ----- Item title color ----- //
		'item_title_color' => array(
			'setting' => array(
				'default'           => get_theme_mod( 'storefront_heading_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 5,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Items Title Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator__item__title' => 'color',
			),
		),

		// ----- Item content color ----- //
		'item_content_color' => array(
			'setting' => array(
				'default'           => get_theme_mod( 'storefront_text_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 6,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Items Content Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator__item__content' => 'color',
			),
		),

		// ----- Item date color ----- //
		'item_date_color' => array(
			'setting' => array(
				'default'           => get_theme_mod( 'storefront_text_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 7,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Item Date Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator__item__date' => 'color',
			),
		),

		// ----- Item author color ----- //
		'item_author_color' => array(
			'setting' => array(
				'default'           => get_theme_mod( 'storefront_text_color' ),
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 8,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Item Author Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator__item__author' => 'color',
			),
		),

		// ----- Item border color ----- //
		'item_border_color' => array(
			'setting' => array(
				'default'           => '#ffffff',
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'control' => array(
				'priority' => 9,
				'section'  => 'storefront_aggregator_customizer',
				'label'    => __( 'Item Border Color', 'storefront-aggregator' ),
			),
			'css'     => array(
				'.storefront-aggregator__item' => 'border-color',
			),
		),
	) );

	return $settings;
}

/**
 * Customizer preview script.
 */
function storefront_aggregator_customizer_preview_script() {
	foreach ( storefront_aggregator_customizer_settings() as $id => $setting ) {
		foreach ( $setting['css'] as $selector => $attribute ) {
			?>
			<script type="text/javascript">
				( function( $ ) {
					wp.customize( '<?php echo esc_js( 'storefront_aggregator_customizer[' . $id . ']' ); ?>', function( value ) {
						value.bind( function( to ) {
							$( '<?php echo esc_js( $selector ); ?>' ).css( '<?php echo esc_js( $attribute ); ?>', to );
						} );
					} );
				} )( jQuery )
			</script>
			<?php
		}
	}
}

/**
 * Action links.
 *
 * @param  array $links
 * @return array $links
 */
function storefront_aggregator_action_links( $links ) {
	$my_links = array(
		'<a href="' . admin_url( 'edit.php?post_type=aggregator' ) . '">' . __( 'Add' ) . '</a>',
		'<a href="' . admin_url( 'customize.php' ) . '">' . __( 'Customizer' ) . '</a>',
	);

	return $links += $my_links;
}
