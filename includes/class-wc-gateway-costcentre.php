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

	/**
	 * List of checkout fields.
	 *
	 * @var Woo_Costcentre_Gateway_Payment_Fields
	 */
	public $fields;

	/**
	 * WC_Gateway_Costcentre constructor.
	 */
	public function __construct() {
		/* Mandatory payment gateway fields */
		$this->id                 = 'costcentre';
		$this->method_title       = __( 'Cost centre', 'woo-costcentre-gateway' );
		$this->method_description = __( 'Cost centre gateway works by sending the user to a requisition form.', 'woo-costcentre-gateway' );
		$this->icon               = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/assets/images/building-regular.svg';
		$this->has_fields         = true;
		/* End of mandatory fields */

		$this->title = $this->get_option( 'title' );

		$this->init_settings_fields();
		$this->init_settings();

		$this->fields = new Woo_Costcentre_Gateway_Payment_Fields( $this );
		$field        = [
			'id'       => 'woo-costcentre-number',
			'class'    => 'input-text',
			'name'     => 'woo_costcentre_number',
			'label'    => 'Cost centre number',
			'regex'    => $this->get_option( 'cost_centre_regex' ),
			'required' => true,
		];
		$this->fields->add( $field );

		// Actions.
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			[
				$this,
				'process_admin_options',
			]
		);
		add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'thankyou_page' ] );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', [ $this, 'email_instruction' ], 10, 2 );

		// Administrator Emails.
		add_action( 'woocommerce_email_order_meta', [ $this, 'admin_email_order_meta' ], 10, 3 );

		add_action( 'woocommerce_after_checkout_validation', [ $this, 'validate_checkout_fields' ], 10, 2 );
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 */
	public function email_instruction( $order, $sent_to_admin ) {
		if ( ! $sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
			wc_get_template( 'email-instruction.php', [ 'order' => $order ], 'woo-costcentre-gateway', plugin_dir_path( __DIR__ ) . 'templates/' );
		}

		do_action( 'woo_costcentre_gateway_after_email_instructions', $order, $sent_to_admin );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = new WC_Order( $order_id );

		foreach ( $this->fields->get_payment_fields() as $gateway_field ) {
			// Add _ to beginning of meta data to set it as protected.
			if ( isset( $_REQUEST[ $gateway_field['name'] ] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
				$order->add_meta_data( '_' . $this->id . '_' . $gateway_field['name'], wc_clean( sanitize_text_field( wp_unslash( $_REQUEST[ $gateway_field['name'] ] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}
		}
		$order->save();

		// Mark as on-hold (we're awaiting for authorisation).
		$order->update_status( apply_filters( 'woocommerce_process_payment_order_status_' . $this->id, 'on-hold', $order ), __( 'Awaiting cost centre authorisation', 'woo-costcentre-gateway' ) );

		// Remove cart.
		WC()->cart->empty_cart();

		// Add custom order note.
		$order->add_order_note( __( 'This order is awaiting confirmation from the shop manager', 'woo-costcentre-gateway' ) );

		do_action( 'woo_costcentre_gateway_process_payment', $order_id );

		// Return thankyou redirect.
		return [
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		];

	}

	/**
	 * Template to use for displaying the payment fields.
	 */
	public function payment_fields() {
		wc_get_template( 'requisition-form.php', [ 'gateway_fields' => $this->fields->get_payment_fields() ], 'woo-costcentre-gateway', plugin_dir_path( __DIR__ ) . 'templates/' );
	}

	/**
	 * Check out fields validation.
	 *
	 * @param  array    $data   An array of posted data.
	 * @param  WP_Error $errors Validation errors.
	 *
	 * @throws Exception Exception is thrown if order could not be completed.
	 */
	public function validate_checkout_fields( $data, $errors ) {
		$validate_fields = [];
		foreach ( $this->fields->get_payment_fields() as $field ) {
			if ( isset( $_REQUEST[ $field['name'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$validate_fields[ $field['name'] ] = sanitize_text_field( wp_unslash( $_REQUEST[ $field['name'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}
		}
		if ( isset( $data['payment_method'] ) && $data['payment_method'] === $this->id ) {
			$validate_fields = apply_filters( 'woo_costcentre_gateway_fields', $validate_fields );

			foreach ( $this->fields->get_payment_fields() as $field ) {
				if ( $field['required'] && empty( $validate_fields[ $field['name'] ] ) ) {
					$errors->add(
						'payment',
						sprintf(
							/* translators: %s: Field name */
							__( '%s is a required field.', 'woo-costcentre-gateway' ),
							'<strong>' . $field['label'] . '</strong>'
						)
					);
				} elseif ( ! empty( $field['regex'] ) && 0 === preg_match( $field['regex'], $validate_fields[ $field['name'] ] ) ) {
					/* translators: 1: Field value 2: Field name */
					$errors->add( 'payment', sprintf( __( '%1$s is not a valid %2$s.', 'woo-costcentre-gateway' ), '<strong>' . esc_html( $validate_fields[ $field['name'] ] ) . '</strong>', '<strong>' . $field['label'] . '</strong>' ) );
				}
			}
			do_action( 'woo_costcentre_gateway_validate_fields', $data, $errors );
		}
	}

	/**
	 * Displayed after the user has checked out.
	 *
	 * @param int $order_id The order ID.
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		wc_get_template( 'thankyou.php', [ 'order' => $order ], 'woo-costcentre-gateway', plugin_dir_path( __DIR__ ) . 'templates/' );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @since 1.0.0
	 */
	public function init_settings_fields() {
		$form_fields       = [
			'enabled'           => [
				'title'       => __( 'Enable/Disable', 'woo-costcentre-gateway' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable cost centre payment option', 'woo-costcentre-gateway' ),
				'description' => __( 'This controls whether or not this gateway is enabled within WooCommerce.', 'woo-costcentre-gateway' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			],
			'title'             => [
				'title'       => __( 'Title', 'woo-costcentre-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woo-costcentre-gateway' ),
				'default'     => __( 'Cost Centre', 'woo-costcentre-gateway' ),
				'desc_tip'    => true,
			],
			'description'       => [
				'title'       => __( 'Description', 'woo-costcentre-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woo-costcentre-gateway' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'cost_centre_regex' => [
				'title'       => __( 'Cost Centre Pattern', 'woo-costcentre-gateway' ),
				'type'        => 'regex',
				'description' => __( 'Cost centre number regex pattern. See <a href="https://regexr.com">https://regexr.com</a> for guide.', 'woo-costcentre-gateway' ),
				'default'     => '/^.+$/',
				'desc_tip'    => false,
			],
		];
		$this->form_fields = apply_filters( 'woo_costcentre_gateway_settings_fields', $form_fields );
	}

	/**
	 * Send order via email to administrator.
	 *
	 * @param WC_Order $order An instance of WC_Order.
	 * @param bool     $sent_to_admin A boolean value to indicate if the email has been sent.
	 * @param bool     $plain_text A boolean value to indicate if the email should be sent as plain text.
	 */
	public function admin_email_order_meta( $order, $sent_to_admin, $plain_text ) {
		if ( ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			foreach ( $this->fields->get_payment_fields() as $field ) {
				$value = get_post_meta( $order->get_id(), '_' . $this->id . '_' . $field['name'], true );
				if ( $plain_text ) {
					echo esc_html( $field['label'] . ':' ) . "\n";
					echo esc_html( $value ) . "\n";
				} else {
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

	/**
	 * Validate the Regular Expression
	 *
	 * @param string $key   Field ID.
	 * @param string $value Field value.
	 *
	 * @return string Return the field value.
	 * @throws Exception Throws error message if regular expression is not valid.
	 */
	public function validate_regex_field( $key, $value ) {
		$is_valid = true;
		try {
			if ( false === @preg_match( wp_unslash( $value ), '' ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$is_valid = false;
			}
		} catch ( Exception $e ) {
			$is_valid = false;
		}
		if ( ! $is_valid ) {
			if ( isset( $this->form_fields[ $key ] ) ) {
				$settings_field = $this->form_fields[ $key ];
			} else {
				/* translators: %s Field id */
				throw new Exception( sprintf( __( 'Could not find setting field with id: %s.', 'woo-costcentre-gateway' ), $key ) );
			}

			// WooCommerce is supposed to show the message that has been thrown in the error.
			// In WC_Settings_API::process_admin_options a call to add_error is made but nothing is done with it.
			/* translators: 1: Regex pattern 2: Field label */
			$message = sprintf( __( '%1$s is not a valid regex pattern in %2$s.', 'woo-costcentre-gateway' ), sanitize_text_field( wp_unslash( $value ) ), $settings_field['title'] );
			WC_Admin_Settings::add_error( $message );
			throw new Exception( $message );
		}

		return wp_unslash( $value );
	}

	/**
	 * Generate Regexp Input HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_regex_html( $key, $data ) {
		unset( $data['type'] );
		return $this->generate_text_html( $key, $data );
	}
}