<?php
/**
* The file that defines admin features.
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
 * This is used to define admin features such as payment details
 * in admin order view.
 *
 * @since      1.0.0
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 * @author     Marius Bezuidenhout <marius.bezuidenhout@gmail.com>
 */
class Woo_Costcentre_Gateway_Admin {

	/**
	 * Woo_Costcentre_Gateway_Admin constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 40 );
	}

	/**
	 * Add meta boxes to admin pages
	 */
	public function add_meta_boxes() {
		/** @var WP_Post $post */
		$post = get_post();

		if ( in_array( $post->post_type, wc_get_order_types(), true ) ) {
			$payment_gateway = wc_get_payment_gateway_by_order( wc_get_order() );

			if ( is_a( $payment_gateway, 'WC_Payment_Gateway' ) &&
				'costcentre' === $payment_gateway->id ) {
				foreach ( wc_get_order_types() as $type ) {
					$order_type_object = get_post_type_object( $type );
					add_meta_box( 'woocommerce-order-costcentre', sprintf( __( 'Cost centre details', 'woo-costcentre-gateway' ), $order_type_object->labels->singular_name ), 'Woo_Costcentre_Gateway_Admin::payment_details', $type, 'side', 'default' );
				}
			}
		}

	}

	/**
	 * Output the captures cost centre details
	 *
	 * @param WP_Post $post Instance of WP_Post.
	 */
	public static function payment_details( $post ) {
		$order = wc_get_order( $post->ID );
		/** @var WC_Gateway_Costcentre $payment_gateway */
		$payment_gateway = wc_get_payment_gateway_by_order( $order );

		if ( 'costcentre' === $payment_gateway->id ) {

			foreach ( $payment_gateway->fields->get_payment_fields() as $field ) {
				$value = get_post_meta( $order->get_id(), '_' . $payment_gateway->id . '_' . $field['name'], true );
				if ( ! empty( $value ) ) {
					?>
					<p class="woo-costcentre-gateway-detail">
						<strong><?php echo esc_html( $field['label'] . ':' ); ?></strong>
						<div class="woo-costcentre-gateway-value"><?php echo esc_html( $value ); ?></div>
					</p>
					<?php
				}
			}
		}
	}

}

new Woo_Costcentre_Gateway_Admin();
