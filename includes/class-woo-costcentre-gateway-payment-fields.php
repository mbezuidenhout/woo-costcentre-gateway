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

	/** @var array */
	protected $payment_fields;

	public function __construct() {
		$this->payment_fields = array(
			array(
				'id'       => 'woo-costcentre-number',
				'class'    => 'input-text',
				'name'     => 'woo_costcentre_number',
				'label'    => 'Cost centre number',
				'required' => true,
			),
		);
		$this->payment_fields = apply_filters( 'woo_costcentre_gateway_form_fields', $this->payment_fields );
	}

	/**
	 * @return array
	 */
	public function get_payment_fields() {
		return $this->payment_fields;
	}

}
