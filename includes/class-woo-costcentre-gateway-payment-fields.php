<?php
/**
 * The file that defines payment gateway customer fields.
 *
 * @link       https://www.facebook.com/marius.bezuidenhout1
 * @since      1.0.0
 *
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 */

/**
 * The file that defines payment gateway customer fields.
 *
 * This defines a list of fields that customers will need to complete.
 *
 * @since      1.0.0
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 * @author     Marius Bezuidenhout <marius.bezuidenhout@gmail.com>
 */
class Woo_Costcentre_Gateway_Payment_Fields {

	/**
	 * An array of payment fields.
	 *
	 * @var array
	 */
	protected $payment_fields = [];

	/**
	 * The WC_Gateway_Costcentre instance.
	 *
	 * @var WC_Gateway_Costcentre
	 */
	private $gateway;

	/**
	 * Woo_Costcentre_Gateway_Payment_Fields constructor.
	 *
	 * @param WC_Gateway_Costcentre $gateway An instance of WC_Gateway_Costcentre.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * Add another field.
	 *
	 * @param array $field Field to add.
	 */
	public function add( $field ) {
		$this->payment_fields[] = $field;
	}

	/**
	 * Returns the payment fields.
	 *
	 * @return array
	 */
	public function get_payment_fields() {
		$fields = apply_filters( 'woo_costcentre_gateway_form_fields', $this->payment_fields, $this->gateway );
		return $fields;
	}

}
