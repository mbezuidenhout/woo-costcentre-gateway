<?php

/**
 * The file that defines the payment gateway.
 *
 * @link       https://www.facebook.com/marius.bezuidenhout1
 * @since      1.0.0
 *
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 */

/**
 * The file that defines the payment gateway.
 *
 * This is used to define the payment gateway, admin setting, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 * @author     Marius Bezuidenhout <marius.bezuidenhout@gmail.com>
 */
class WC_Gateway_Costcentre extends WC_Payment_Gateway {
	public function __construct() {
		$this->id           = 'costcentre';
		$this->method_title = __( 'Cost centre', 'woo-costcentre-gateway' );
		$this->method_description   = __( 'Cost centre gateway works by sending the user to a requisition form.', 'woo-costcentre-gateway' );
	}
}