<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Storefront Aggregator Admin class.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

class Storefront_Aggregator_Admin {

	/**
	 * @var object $_instance
	 */
	private static $_instance;

	/**
	 * @var array $_meta_boxes
	 */
	private $_meta_boxes;
	
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
		$this->_set_meta_boxes();
		
		add_action( 'init',                                               array( $this, 'register_post_type' ),  50, 0 );
		add_action( 'save_post_ultimate_aggregator',                      array( $this, 'save_meta_boxes' ),     10, 2 );
		add_action( 'post_submitbox_misc_actions',                        array( $this, 'customize_button' ),    10, 0 );
		add_action( 'trashed_post',                                       array( $this, 'trashed_post' ),        10, 1 );
		add_filter( 'manage_ultimate_aggregator_posts_columns',           array( $this, 'posts_columns' ),       10, 0 );
		add_filter( 'manage_ultimate_aggregator_posts_custom_column',     array( $this, 'posts_custom_column' ), 10, 2 );
		add_filter( 'is_protected_meta',                                  array( $this, 'protect_meta' ),        10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ),        10, 1 );
	}

	/**
	 * Sets meta boxes.
	 */
	private function _set_meta_boxes() {
		$meta_boxes = array(
			array(
				'id'             => 'storefront_aggregator_items_type_meta_box',
				'title'          => __( 'Items Type', 'storefront-aggregator' ),
				'callback'       => array( $this, 'output_meta_boxes' ),
				'screen'         => 'ultimate_aggregator',
				'context'        => 'side',
				'priority'       => 'default',
				'callback_args'  => array(
					'meta_box_id'  => 'storefront_aggregator_items_type_meta_box',
					'meta_key'     => 'storefront_aggregator_items_type',
					'meta_value'   => array(
						'post'    => array(),
						'comment' => array(),
					),
					'meta_value__' => array(
						'post'    => __( 'Last Posts', 'storefront-aggregator' ),
						'comment' => __( 'Last Comments', 'storefront-aggregator' ),
					),
					'meta_default' => 'comment',
					'nonce_action' => 'storefront_aggregator_items_type_meta_box_nonce',
					'nonce_name'   => 'storefront_aggregator_items_type_nonce',
				),
			),
			array(
				'id'             => 'storefront_aggregator_items_number_meta_box',
				'title'          => __( 'Items Number', 'storefront-aggregator' ),
				'callback'       => array( $this, 'output_meta_boxes' ),
				'screen'         => 'ultimate_aggregator',
				'context'        => 'side',
				'priority'       => 'default',
				'callback_args'  => array(
					'meta_box_id'  => 'storefront_aggregator_items_number_meta_box',
					'meta_key'     => 'storefront_aggregator_items_number',
					'meta_value'   => array(),
					'meta_value__' => array(),
					'meta_default' => '4',
					'nonce_action' => 'storefront_aggregator_items_number_meta_box_nonce',
					'nonce_name'   => 'storefront_aggregator_items_number_nonce',
				),
			),
			array(
				'id'             => 'storefront_aggregator_domain_meta_box',
				'title'          => __( 'Domain', 'storefront-aggregator' ),
				'callback'       => array( $this, 'output_meta_boxes' ),
				'screen'         => 'ultimate_aggregator',
				'context'        => 'normal',
				'priority'       => 'default',
				'callback_args'  => array(
					'meta_box_id'  => 'storefront_aggregator_domain_meta_box',
					'meta_key'     => 'storefront_aggregator_domain',
					'meta_value'   => array(),
					'meta_value__' => array(),
					'meta_default' => array(
						'page'     => 'is_front_page',
						'hook'     => 'storefront_before_footer',
						'priority' => '10',
					),
					'nonce_action' => 'storefront_aggregator_domain_meta_box_nonce',
					'nonce_name'   => 'storefront_aggregator_domain_nonce',
				),
			),
		);

		$this->_meta_boxes = apply_filters( 'storefront_aggregator_meta_boxes', $meta_boxes );
	}

	/**
	 * Gets meta boxes.
	 *
	 * @return `$this->_meta_boxes`
	 */
	public function get_meta_boxes() {
		return $this->_meta_boxes;
	}

	/**
	 * Registers post type.
	 *
	 * Hooked into `init` action hook.
	 */
	public function register_post_type() {
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
			'register_meta_box_cb' => array( $this, 'add_meta_boxes' ),
		);

		$args = apply_filters( 'storefront_aggregator_post_type_args', $args );

		register_post_type( 'ultimate_aggregator', $args );
	}

	/**
	 * Adds meta boxes.
	 * 
	 * Callback of `register_post_type()`
	 */
	public function add_meta_boxes() {
		foreach ( $this->_meta_boxes as $meta_box ) {
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
	public function output_meta_boxes( $post, $callback_args ) {
		$aggregator   = storefront_aggregator()->get_aggregators( $post->ID );
		$meta_box_id  = $callback_args['args']['meta_box_id'];
		$meta_key     = $callback_args['args']['meta_key'];
		$meta_value   = $callback_args['args']['meta_value'];
		$meta_value__ = $callback_args['args']['meta_value__'];
		$meta_default = $callback_args['args']['meta_default'];
		$nonce_action = $callback_args['args']['nonce_action'];
		$nonce_name   = $callback_args['args']['nonce_name'];
		$value        = isset( $aggregator->meta[ substr( $meta_key, 22 ) ] ) ? $aggregator->meta[ substr( $meta_key, 22 ) ] : $meta_default;

		switch ( $meta_box_id ) {
			case 'storefront_aggregator_items_type_meta_box':
				$html  = '<p class="description">' . esc_html__( 'Select the type of items you wish to aggregate...', 'storefront-aggregator' ) . '</p>';
				$html .= '<select id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '">';
				$html .= '<option value="">' . esc_html__( 'Select...', 'storefront-aggregator' ) . '</option>';

				foreach ( $meta_value as $item_type => $item_type_value ) {
					$selected = ( $item_type === $value ) ? 'selected ' : '';

					$html .= '<option ' . $selected . 'value="' . esc_attr( $item_type ) . '">' . esc_html( $meta_value__[  $item_type ] ) . '</option>';
				}

				$html .= '</select>';

				echo $html;

				wp_nonce_field( $nonce_action, $nonce_name );

			break;

			case 'storefront_aggregator_items_number_meta_box':
				$html  = '<p class="description">' . esc_html__( 'How many items do you want to aggregate ?', 'storefront-aggregator' ) . '</p>';
				$html .= '<input id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" type="number" value="' . esc_attr( $value ) . '" />';

				echo $html;

				wp_nonce_field( $nonce_action, $nonce_name );

			break;

			case 'storefront_aggregator_domain_meta_box':
				$label_1 = '<strong>' . esc_html__( 'Page' ) . '</strong>';
				$label_2 = '<strong>' . esc_html__( 'Hook' ) . '</strong>';
				$label_3 = '<strong>' . esc_html__( 'Priority' ) . '</strong>';
				$link_1  = '<a href="https://codex.wordpress.org/Conditional_Tags" target="_blank">' . esc_html__( 'Codex references', 'storefront-aggregator' ) . '</a>';

				$html  = '<p class="description">' . esc_html__( 'Where do you want to show this aggregator ?', 'storefront-aggregator' ) . '</p><br />';
				$html .= '<p class="description">' . sprintf( esc_html__( '%1$s: Takes the form of a conditional tag. %2$s.' ), $label_1, $link_1 ) . '</p>';
				$html .= '<p class="description">' . sprintf( esc_html__( '%s: A template action hook that must be present on the page.' ), $label_2 ) . '</p>';
				$html .= '<p class="description">' . sprintf( esc_html__( '%s: The action hook priority. Useful if you want this aggregator to move around other elements hooked to the same action.' ), $label_3 ) . '</p><br />';
				$html .= '<div id="postcustomstuff">';
			
				$html .= '<table class="form-table">';
				$html .= '<tr><td>';
				$html .= '<label for="' . esc_attr( $meta_key . '_page' ) . '">' . $label_1 . '</label>';
				$html .= '</td><td>';
				$html .= '<input id="' . esc_attr( $meta_key . '_page' ) . '" name="' . esc_attr( $meta_key . '[page]' ) . '" type="text" value="' . esc_attr( $value['page'] ) . '">';
				$html .= '</td></tr><tr><td>';
				$html .= '<label for="' . esc_attr( $meta_key . '_hook' ) . '">' . $label_2 . '</label>';
				$html .= '</td><td>';
				$html .= '<input id="' . esc_attr( $meta_key . '_hook' ) . '" name="' . esc_attr( $meta_key . '[hook]' ) . '" type="text" max="20" value="' . esc_attr( $value['hook'] ) . '">';
				$html .= '</td></tr><tr><td>';
				$html .= '<label for="' . esc_attr( $meta_key . '_priority' ) . '">' . $label_3 . '</label>';
				$html .= '</td><td>';
				$html .= '<input id="' . esc_attr( $meta_key . '_priority' ) . '" name="' . esc_attr( $meta_key . '[priority]' ) . '" type="number" max="1000"  value="' . esc_attr( $meta_default['priority'] ) . '">';
				$html .= '</td></tr>';
				$html .= '</table>';

				echo $html;

				wp_nonce_field( $nonce_action, $nonce_name );

			break;
		}
	}

	public function customize_button() {
		global $post;
		
		if ( 'ultimate_aggregator' === $post->post_type && 'publish' === $post->post_status ) {
			$query = array(
				'autofocus[section]' => 'storefront_aggregator_customizer_' . $post->ID,
			);

			$url = add_query_arg( $query, admin_url( 'customize.php' ) );

			$html  = '<div id="major-publishing-actions" style="overflow:hidden">';
			$html .= '<div id="publishing-action">';
			$html .= '<a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Customize this aggregator', 'storefront-aggregator' ) . '</a>';
			$html .= '</div>';
			$html .= '</div>';
			echo $html;
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
	public function save_meta_boxes( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		foreach ( $this->_meta_boxes as $meta_box ) {
			$meta_key     = $meta_box['callback_args']['meta_key'];
			$nonce_action = $meta_box['callback_args']['nonce_action'];
			$nonce_name   = $meta_box['callback_args']['nonce_name'];

			if ( isset( $_POST[ $meta_key ] ) &&  wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
				$meta_value = $this->_validate_meta( $_POST[ $meta_key ], $meta_key );
				$meta_value = $this->_sanitize_meta( $meta_value );
				$meta_key   = sanitize_key( $meta_key );

				if ( ! empty( $meta_value ) ) {
					update_post_meta( $post_id, $meta_key, $meta_value );

					delete_transient( 'storefront_aggregators' );
				}
			}
		}
	}

	/**
	 * Validates meta.
	 *
	 * @param  array|string $meta_value
	 * @param  string       $meta_key
	 * @return array|string
	 */
	private function _validate_meta( $meta_value, $meta_key ) {
		if ( 'storefront_aggregator_items_type' === $meta_key ) {
			return preg_match( '#^[a-z_]{1,20}$#', $meta_value ) ? $meta_value : '';

		} elseif ( 'storefront_aggregator_items_number' === $meta_key ) {
			return ! empty( $meta_value ) && intval( $meta_value ) < 20 ? $meta_value : '';

		} elseif ( 'storefront_aggregator_domain' === $meta_key ) {
			if ( is_array( $meta_value ) ) {
				foreach ( $meta_value as $key => $value ) {
					if ( 'page' === $key || 'hook' === $key ) {
						if ( preg_match( '#^[a-z_]{1,50}$#', $value ) ) {
							$meta_value_array[ $key ] = $value;
						} else {
							return '';
						}
					} elseif ( 'priority' === $key ) {
						if ( ! empty( $value ) && intval( $value ) < 1000 ) {
							$meta_value_array[ $key ] = $value;
						} else {
							return '';
						}
					}
				}

				return isset( $meta_value_array ) ? $meta_value_array : '';

			} else {
				return '';
			}
		} else {
			return '';
		}
	}

	/**
	 * Sanitizes meta.
	 *
	 * @param  array|string $meta
	 * @return array|string $sanitized_meta
	 */
	private function _sanitize_meta( $meta ) {
		if ( is_array( $meta ) && ! empty( $meta ) ) {	
			foreach ( $meta as $key => $value ) {
				$sanitized_value = $this->_sanitize_meta( $value );

				if ( ! empty( $sanitized_value ) ) {
					$sanitized_meta[ sanitize_key( $key ) ] = $sanitized_value;
				}
			}	

			if ( ! isset( $sanitized_meta ) ) {
				$sanitized_meta = '';
			}
		} elseif ( is_string( $meta ) && ! empty( $meta ) ) {
			$sanitized_meta = sanitize_text_field( $meta );
		} else {
			$sanitized_meta = '';
		}

		return $sanitized_meta;
	}

	/**
	 * Columns.
	 *
	 * Hooked into `manage_${post_type}_posts_columns` filter hook.
	 *
	 * @return array $columns
	 */
	public function posts_columns() {
		$columns['cb']    = '<input type="checkbox" />';
		$columns['title'] = __( 'Title' );

		foreach ( $this->_meta_boxes as $meta_box ) {
			$columns[ $meta_box['callback_args']['meta_key'] ] = $meta_box['title'];
		}

		$columns['date']  = __( 'Date' );

		return $columns;
	}

	/**
	 * Custom column.
	 *
	 * Hooked into `manage_${post_type}_posts_custom_column` filter hook.
	 *
	 * @param array $column_name
	 * @param int   $post_id
	 */
	public function posts_custom_column( $column_name, $post_id ) {
		$aggregator = storefront_aggregator()->get_aggregators( $post_id );
		$meta       = $aggregator->meta[ substr( $column_name, 22 ) ];

		foreach ( $this->_meta_boxes as $meta_box ) {
			if ( $meta_box['callback_args']['meta_key'] !== $column_name ) {
				continue;
			}

			switch ( $column_name ) {
				case 'storefront_aggregator_items_type':
					echo esc_html( $meta_box['callback_args']['meta_value__'][ $meta ] );

				break;

				case 'storefront_aggregator_items_number':
					echo esc_html( $meta );

				break;

				case 'storefront_aggregator_domain':
					echo '<strong>' . esc_html__( 'Page', 'storefront-aggregator' ) . '</strong>: ' . $meta['page'] . '<br />';
					echo '<strong>' . esc_html__( 'Hook', 'storefront-aggregator' ) . '</strong>: ' . $meta['hook'] . '<br />';
					echo '<strong>' . esc_html__( 'Priority', 'storefront-aggregator' ) . '</strong>: ' . $meta['priority'] . '<br />';

				break;
			}
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
	public function protect_meta( $protected, $meta_key ) {
		foreach ( $this->_meta_boxes as $meta_box ) {
			if ( $meta_key === $meta_box['callback_args']['meta_key'] ) {
				return true;
			}
		}

		return $protected;
	}

	/**
	 * Action links.
	 * 
	 * Hooked into `plugin_action_links_${plugin_file_name}` filter hook.
	 *
	 * @param  array $links
	 * @return array $links
	 */
	public function action_links( $links ) {
		$edit_post_query  = array(
			'post_type' => 'ultimate_aggregator',
		);

		$customizer_query = array(
			'autofocus[panel]' => 'storefront_aggregators',
		);

		$edit_post_url  = add_query_arg( $edit_post_query, admin_url( 'edit.php' ) );
		$customizer_url = add_query_arg( $customizer_query, admin_url( 'customize.php' ) );
		
		$my_links = array(
			'<a href="' . esc_url( $edit_post_url ) . '">' . __( 'Add' ) . '</a>',
			'<a href="' . esc_url( $customizer_url ) . '">' . __( 'Customizer' ) . '</a>',
		);

		return $links += $my_links;
	}

	/**
	 * Trashed post actions.
	 *
	 * Hooked into `trashed_post` action hook.
	 *
	 * @param int $post_id
	 */
	public function trashed_post( $post_id ) {
		if ( 'ultimate_aggregator' === get_post_type( $post_id ) ) {
			delete_option( 'storefront_aggregator_customizer_' . $post_id );
		}
	}
}
