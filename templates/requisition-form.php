<?php
/**
 * The template for displaying a cost centre fields.
 *
 * This template can be overridden by copying it to yourtheme/woo-costcentre-gateway/requisition-form.php
 * @package Woo_Costcentre_Gateway/Templates
 * @version 1.0.0
 * @since   1.0.0
 * @var array $gateway_fields
 *
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$nonce = wp_create_nonce( 'requisition-form' );
foreach ( $gateway_fields as $gateway_field ) {
	$field = '';
	$label = $gateway_field['label'];
	if ( $gateway_field['required'] ) {
		$label .= '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
	}
	$field  = sprintf( '<label for="%s">%s</label>', $gateway_field['id'], $label );
	$field .= sprintf( '<input autocorrect="no" autocapitalize="no" spellcheck="no" id="%s" type="text" class="%s" name="%s" />', $gateway_field['id'], $gateway_field['class'], $gateway_field['name'] );
	echo $field; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
}
