<?php
/**
 * @package   Musilda EventPress
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      https://musilda.com
 * @copyright 2021 musilda.com
 * 
 */

class WC_Product_Event extends WC_Product {

	/**
	 * Initialize event product.
	 *
	 * @param mixed $product
	 */
	public function __construct( $product = 0 ) {
		$this->product_type = 'product';
		parent::__construct( $product );
	}
    
    /**
     * Return the product type
     * @return string
     */
    public function get_type() {
        return 'event';
    }

}
