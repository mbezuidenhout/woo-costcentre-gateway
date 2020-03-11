<?php
/**
 * The template for displaying cost centre thank you message.
 *
 * This template can be overridden by copying it to yourtheme/woo-costcentre-gateway/thankyou.php.
 * HOWEVER, on occasion woo-costcentre-gateway will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package Woo_Costcentre_Gateway/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<span>
<?php
/* translators: %s admin email */
	echo esc_html( sprintf( __( 'Please complete a requisition form and return it to %s to confirm your order.' ), get_option( 'admin_email' ) ) );
?>
</span>
