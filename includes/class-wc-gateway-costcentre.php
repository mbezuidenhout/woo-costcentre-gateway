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

	protected $gateway_fields;

	public function __construct() {
		/* Mandatory payment gateway fields */
		$this->id                 = 'costcentre';
		$this->method_title       = __( 'Cost centre', 'woo-costcentre-gateway' );
		$this->method_description = __( 'Cost centre gateway works by sending the user to a requisition form.', 'woo-costcentre-gateway' );
		$this->icon               = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/assets/images/building-regular.svg';
		$this->has_fields         = true;
		/* End of mandatory fields */

		$this->title = $this->get_option( 'title' );

		$this->init_form_fields();
		$this->init_payment_fields();
		$this->init_settings();

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 2 );
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 */
	public function email_instructions( $order, $sent_to_admin ) {
		if ( ! $sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
			echo wp_kses_post( 'Complete a requisition form and return it to ' . get_option( 'admin_email' ) );
			do_action( 'woo_costcentre_gateway_email_instructions', $order );
		}
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = new WC_Order( $order_id );

		// Mark as on-hold (we're awaiting for authorisation).
		$order->update_status( apply_filters( 'woocommerce_process_payment_order_status_' . $this->id, 'on-hold', $order ), __( 'Awaiting cost centre authorisation', 'woo-costcentre-gateway' ) );

		// Remove cart.
		WC()->cart->empty_cart();

		// Add custom order note
		$order->add_order_note( __( 'This order is awaiting confirmation from the shop manager', 'woo-costcentre-gateway' ) );

		foreach ( $this->gateway_fields as $gateway_field ) {
			$order->add_meta_data( $gateway_field['name'], $_REQUEST[ $gateway_field['name'] ] );
		}

		do_action( 'woo_costcentre_gateway_process_payment', $order_id );

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);

	}

	public function init_payment_fields() {
		$this->gateway_fields = array(
			array(
				'id'       => 'woo-costcentre-number',
				'class'    => 'input-text',
				'name'     => 'woo_costcentre_number',
				'label'    => 'Cost centre number',
				'required' => true,
			),
		);
		$this->gateway_fields = apply_filters( 'woo_costcentre_gateway_form_fields', $this->gateway_fields );
	}

	public function payment_fields() {
		wc_get_template( 'requisition-form.php', array( 'gateway_fields' => $this->gateway_fields ), 'woo-costcentre-gateway', plugin_dir_path( __DIR__ ) . 'templates/' );
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function validate_fields() {
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-process-checkout-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // phpcs:ignore
		wp_verify_nonce( wp_unslash( $nonce_value ), 'woocommerce-process_checkout' );
		$validate_fields = $_REQUEST;
		if ( $_REQUEST['payment_method'] !== $this->id ) {
			return true;
		}
		$validate_fields = apply_filters( 'woo_costcentre_gateway_fields', $validate_fields );
		if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
			throw new Exception( __( 'We were unable to process your order, please try again.', 'woocommerce' ) );
		}
		foreach ( $this->gateway_fields as $field ) {
			if ( $field['required'] && empty( $validate_fields[ $field['name'] ] ) ) {
				wc_add_notice( $field['label'] . ' ' . __( 'cannnot be empty', 'woo-costcentre-gateway' ), 'error' );
			}
		}

		do_action( 'woo_costcentre_gateway_validate_fields' );
		return false;
	}

	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		wc_get_template( 'thankyou.php', array( 'order' => $order ), 'woo-costcentre-gateway', plugin_dir_path( __DIR__ ) . 'templates/' );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'          => array(
				'title'       => __( 'Enable/Disable', 'woo-costcentre-gateway' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable cost centre payment option', 'woo-costcentre-gateway' ),
				'description' => __( 'This controls whether or not this gateway is enabled within WooCommerce.', 'woo-costcentre-gateway' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'title'            => array(
				'title'       => __( 'Title', 'woo-costcentre-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woo-costcentre-gateway' ),
				'default'     => __( 'Cost Centre', 'woo-costcentre-gateway' ),
				'desc_tip'    => true,
			),
			'description'      => array(
				'title'       => __( 'Description', 'woo-costcentre-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woo-costcentre-gateway' ),
				'default'     => '',
				'desc_tip'    => true,
			),
		);
	}
}