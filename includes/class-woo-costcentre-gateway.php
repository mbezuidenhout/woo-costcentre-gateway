<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.facebook.com/marius.bezuidenhout1
 * @since      1.0.0
 *
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Costcentre_Gateway
 * @subpackage Woo_Costcentre_Gateway/includes
 * @author     Marius Bezuidenhout <marius.bezuidenhout@gmail.com>
 */
class Woo_Costcentre_Gateway {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_Costcentre_Gateway_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOO_COSTCENTRE_GATEWAY_VERSION' ) ) {
			$this->version = WOO_COSTCENTRE_GATEWAY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-costcentre-gateway';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Costcentre_Gateway_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Costcentre_Gateway_i18n. Defines internationalization functionality.
	 * - Woo_Costcentre_Gateway_Admin. Defines all hooks for the admin area.
	 * - Woo_Costcentre_Gateway_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-costcentre-gateway-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-costcentre-gateway-i18n.php';

		$this->loader = new Woo_Costcentre_Gateway_Loader();

		if ( is_admin() ) {
			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-costcentre-gateway-admin.php';
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-costcentre-gateway-payment-fields.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-costcentre-gateway-public.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Costcentre_Gateway_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_Costcentre_Gateway_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {
		$public_plugin = new Woo_Costcentre_Gateway_Public();
		$this->loader->add_action( 'woocommerce_email_order_details', $public_plugin, 'order_details', 40, 3 );
		$this->loader->add_action( 'plugins_loaded', $this, 'woo_gateway_init', 0 );
	}

	/**
	 * Add the filter to add the payment method to WooCommerce.
	 */
	public function woo_gateway_init() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}
		/**
		 * The class responsible to defining the payment gateway action and settings.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-gateway-costcentre.php';

		$this->loader->add_filter( 'woocommerce_payment_gateways', $this, 'add_gateway' );
		$this->loader->run();
	}

	/**
	 * Add the Gateway to WooCommerce
	 *
	 * @param array $methods An array of payment methods.
	 *
	 * @return array
	 **/
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Costcentre';
		return $methods;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woo_Costcentre_Gateway_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
