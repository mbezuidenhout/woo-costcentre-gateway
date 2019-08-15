<?php
/**
 * The file that defines public features.
 *
 * @link       https://www.facebook.com/marius.bezuidenhout1
 * @since      1.0.0
 *
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 */

/**
 * The file that defines admin features.
 *
 * This is used to define public features such as customer and admin emails.
 *
 * @since      1.0.0
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 * @author     Marius Bezuidenhout <marius.bezuidenhout@gmail.com>
 */
class Woo_Costcentre_Gateway_Public {

	/**
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 */
	public function order_details( $order, $sent_to_admin, $plain_text ) {
		/** @var WC_Gateway_Costcentre $payment_gateway */
		$payment_gateway = wc_get_payment_gateway_by_order( $order );
		// Details sent to admin
		if ( $sent_to_admin &&
			is_a( $payment_gateway, 'WC_Payment_Gateway' ) &&
			'costcentre' === $payment_gateway->id ) {
			$fields = $payment_gateway->fields->get_payment_fields();
			foreach ( $fields as $field ) {
				$value = get_post_meta( $order->get_id(), '_' . $payment_gateway->id . '_' . $field['name'], true );
				if ( $plain_text ) {
					echo $field['label'] . ': ' . $value . "\n"; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				} else {
					echo '<p><strong>' . esc_html( $field['label'] ) . ':</strong> ' . esc_html( $value ) . '</p>';
				}
			}
		}
	}
}
