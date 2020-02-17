<?php
/**
 * The template to add instructions to customer order email
 *
 * This template can be overridden by copying it to yourtheme/woo-costcentre-gateway/email-instruction.php
 * @package Woo_Costcentre_Gateway/Templates
 * @version 1.0.0
 * @since   1.0.0
 * @var WC_Order $order
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* translators: e-mail address */
echo wp_kses_post( sprintf( __( 'Complete a requisition form and return it to %s to confirm your order.', 'woo-costcentre-gateway' ), get_option( 'admin_email' ) ) );
