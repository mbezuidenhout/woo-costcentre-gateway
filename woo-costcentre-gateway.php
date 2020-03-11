<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.facebook.com/marius.bezuidenhout1
 * @since             1.0.0
 * @package           Woo_Costcentre_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:          Cost Centre Gateway for WooCommerce
 * Plugin URI:           https://github.com/mbezuidenhout/woocommerce-costcentre-gateway
 * Description:          Process payments made via company cost centre.
 * Version:              1.1.1
 * Requires at least:    5.0
 * Tested up to:         5.4
 * WC tested up to:      4.0.0
 * WC requires at least: 3.0
 * Author:               Marius Bezuidenhout
 * Author URI:           https://profiles.wordpress.org/mbezuidenhout/
 * License:              GPL-2.0+
 * License URI:          http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:          woo-costcentre-gateway
 * Domain Path:          /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOO_COSTCENTRE_GATEWAY_VERSION', '1.1.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-costcentre-gateway-activator.php
 */
function activate_woo_costcentre_gateway() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-costcentre-gateway-activator.php';
	Woo_Costcentre_Gateway_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-costcentre-gateway-deactivator.php
 */
function deactivate_woo_costcentre_gateway() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-costcentre-gateway-deactivator.php';
	Woo_Costcentre_Gateway_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_costcentre_gateway' );
register_deactivation_hook( __FILE__, 'deactivate_woo_costcentre_gateway' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-costcentre-gateway.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_costcentre_gateway() {
	$plugin = new Woo_Costcentre_Gateway();
	$plugin->run();

}
run_woo_costcentre_gateway();
