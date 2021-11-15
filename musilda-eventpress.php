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
			
		if ( $product_type == 'falcoholster' ) { 
			$classname = 'WC_Product_Falcoholster';
		}
		return $classname;
			
	}

}