<?php
/**
 * @package   Musilda EventPress
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      https://musilda.com
 * @copyright 2021 musilda.com
 *
 * @wordpress-plugin
 * Plugin Name:       Musilda EventPress
 * Plugin URI:        
 * Description:       Musilda EventPress
 * Version:           1.0
 * Author:            Musilda.com
 * Author URI:        https://musilda.com
 * Text Domain:       musilda-eventpress
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	/**
	 * Admin notice
	 * 
	 * @since 1.0
	 */
	add_action('admin_notices', 'musilda_admin_notice_wc_not_active');
  	function musilda_admin_notice_wc_not_active() {
    	$class = 'notice notice-error';
    	printf(
      		'<div class="error" style="background:red; color:#fff;"><p>%s</p></div>',
      		__( 'You Must Install WooCommerce Plugin before activating EventPress ', 'musilda-eventpress' )
    	);
  	}
  
}

/**
 * Load plugin code, when WooCommerce is loaded
 * 
 * @since 1.0
 */
add_action( 'woocommerce_loaded', 'load_eventpress' );
function load_eventpress() {

	include( 'includes/class-wc-product-event.php' );

	/**
	 * Add product type to selector
	 * 
	 * @since 1.0
	 */

	add_filter( 'product_type_selector', 'musilda_add_type' );
	function musilda_add_type( $types ) {

		$types['event'] = __( 'Event', 'musilda' );
	
		return $types;

	}

	/**
	 * Register product class
	 * 
	 * @since 1.0
	 */
	add_filter( 'woocommerce_product_class', 'musilda_woocommerce_product_class', 10, 2 ); 
	function musilda_woocommerce_product_class( $classname, $product_type ) {
			
		if ( $product_type == 'event' ) { 
			$classname = 'WC_Product_Event';
		}
		return $classname;
			
	}

	add_filter( 'woocommerce_product_data_tabs', 'musilda_event_product_data_tabs', 10, 1 );
	function musilda_event_product_data_tabs( $option ) {

		global $post;
		$product = wc_get_product( $post->ID );

		if ( 'event' == $product->get_type() ) {

			$option['general']['class'][] 			= 'show_if_event';
			$option['general']['class'][] 			= 'active';
			$option['inventory']['class'][] 		= 'hide_if_event';
			$option['shipping']['class'][] 			= 'hide_if_event';
			$option['linked_product']['class'][] 	= 'hide_if_event';
			$option['attribute']['class'][] 		= 'hide_if_event';
			$option['advanced']['class'][] 			= 'hide_if_event';

			$option['event'] = array(
				'label'    => __( 'Event', 'musilda-eventpress' ),
				'target'   => 'musilda_eventpress_product_option',
				'class'    => array( 'show_if_event' ),
				'priority' => 60,
			);

		}

		return $option;

	}

	add_action( 'woocommerce_product_data_panels', 'musilda_eventpress_product_option' );
	function musilda_eventpress_product_option() {

		echo '<div id="musilda_eventpress_product_option" class="panel woocommerce_options_panel">';
			echo __( 'Event data', 'musilda-eventpress' );

			global $post;
			$min_participants = get_post_meta( $post->ID, '_min_participants', true );
			if ( empty( $min_participants ) ) {
				$min_participants = 1;
			}
			$max_participants = get_post_meta( $post->ID, '_max_participants', true );
			if ( empty( $max_participants ) ) {
				$max_participants = 1;
			}

			woocommerce_wp_text_input(
				array(
					'id'                => '_min_participants',
					'value'             => $min_participants,
					'label'             => __( 'Minimal participants', 'musilda-eventpress' ),
					'placeholder'       => __( '1', 'musilda-eventpress' ),
					'description'       => __( 'Add minimum participants for event', 'musilda-eventpress' ),
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => '1',
						'min'  => '1',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => '_max_participants',
					'value'             => $max_participants,
					'label'             => __( 'Maximal participants', 'musilda-eventpress' ),
					'placeholder'       => __( '1', 'musilda-eventpress' ),
					'description'       => __( 'Add maximal participants for event', 'musilda-eventpress' ),
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => '1',
						'min'  => '1',
					),
				)
			);

			$event_start_date_time = get_post_meta( $post->ID, '_event_start_date_time', true );
			if ( empty( $event_start_date_time ) ) {
				$event_start_date_time = '';
			}
			echo '<p class="form-field event_start_date_time">
				<label for="event_start_date_time">' . esc_html__( 'Event start date time', 'musilda-eventpress' ) . '</label>
				<input type="text" class="short" name="_event_start_date_time" id="event_start_date_time" value="' . $event_start_date_time . '" />				
			</p>';

		echo '</div>';				

	}

	/**
	 * Show pricing fields for event product.
	 */
	add_action( 'admin_footer', 'event_product_custom_js' );
	function event_product_custom_js() {

		if ( 'product' != get_post_type() ) :
			return;
		endif;

		?><script type='text/javascript'>
			jQuery( document ).ready( function() {				
				jQuery( '.options_group.pricing' ).addClass( 'show_if_event' );
				jQuery( '.show_if_event' ).show();
			});

		</script><?php

	}

	/**
	 * Add admin assets
	 * 
	 */
	add_action( 'admin_enqueue_scripts', 'eventpress_enqueue_admin_styles' );
	function eventpress_enqueue_admin_styles() {
		$current_screen = get_current_screen();
		if ( 'product' == $current_screen->id ) {
			wp_enqueue_style( 'bulma-calendar', plugins_url( 'assets/admin/css/bulma-calendar.css', __FILE__ ), array(), '1.0' );
		}
	}
	add_action( 'admin_enqueue_scripts', 'eventpress_enqueue_admin_scripts' );
	function eventpress_enqueue_admin_scripts() {
		$current_screen = get_current_screen();
		if ( 'product' == $current_screen->id ) {
			wp_enqueue_script( 'bulma-calendar', plugins_url( 'assets/admin/js/bulma-calendar.min.js', __FILE__ ), array(), '1.0', true );
			wp_enqueue_script( 'event-admin', plugins_url( 'assets/admin/js/main.js', __FILE__ ), array(), '1.0', true );
		}
	}

	add_action( 'woocommerce_process_product_meta', 'musilda_product_fields_save' );
	function musilda_product_fields_save( $post_id ){

		//Check autosave
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post_id;	
		}	
			
		//Don't update on Quick Edit
		if ( defined('DOING_AJAX') ) {	
			return $post_id;	
		}	
		
		//Don't update on Quick Edit	
		if ( !empty( $_REQUEST['bulk_edit'] ) ) {	
			return $post_id;	
		}
	
		if ( !empty( $_POST['_min_participants'] ) ) {
			$_min_participants = sanitize_text_field( $_POST['_min_participants'] );
			update_post_meta( $post_id, '_min_participants', $_min_participants );
		} else {
			delete_post_meta( $post_id, '_min_participants' );
		}
		if ( !empty( $_POST['_max_participants'] ) ) {
			$_max_participants = sanitize_text_field( $_POST['_max_participants'] );
			update_post_meta( $post_id, '_max_participants', $_max_participants );
		} else {
			delete_post_meta( $post_id, '_max_participants' );
		}
		if ( !empty( $_POST['_event_start_date_time'] ) ) {
			$_event_start_date_time = sanitize_text_field( $_POST['_event_start_date_time'] );
			update_post_meta( $post_id, '_event_start_date_time', $_event_start_date_time );
		} else {
			delete_post_meta( $post_id, '_event_start_date_time' );
		}

	}

}