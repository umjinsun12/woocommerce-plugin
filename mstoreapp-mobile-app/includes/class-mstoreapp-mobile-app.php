<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://mstoreapp.com
 * @since      1.0.0
 *
 * @package    Mstoreapp_Mobile_App
 * @subpackage Mstoreapp_Mobile_App/includes
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
 * @package    Mstoreapp_Mobile_App
 * @subpackage Mstoreapp_Mobile_App/includes
 * @author     Mstoreapp <support@mstoreapp.com>
 */
class Mstoreapp_Mobile_App {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Mstoreapp_Mobile_App_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'mstoreapp-mobile-app';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Mstoreapp_Mobile_App_Loader. Orchestrates the hooks of the plugin.
	 * - Mstoreapp_Mobile_App_i18n. Defines internationalization functionality.
	 * - Mstoreapp_Mobile_App_Admin. Defines all hooks for the admin area.
	 * - Mstoreapp_Mobile_App_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mstoreapp-mobile-app-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mstoreapp-mobile-app-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mstoreapp-mobile-app-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-mstoreapp-mobile-app-public.php';


		$this->loader = new Mstoreapp_Mobile_App_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Mstoreapp_Mobile_App_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Mstoreapp_Mobile_App_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Mstoreapp_Mobile_App_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action('wp_ajax_mstoreapp-mobile-app-notification', $plugin_admin, 'mobile_app_notification');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-mobile-app-notification', $plugin_admin, 'mobile_app_notification');

		$this->loader->add_action('admin_menu', $plugin_admin, 'mstoreapp_mobile_app_menu');
		$this->loader->add_action('admin_menu', $plugin_admin, 'push_notification_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_mstoreapp_mobile_app_settings');
        $this->loader->add_action( 'save_post', $plugin_admin, 'save_post' );
       // $this->loader->add_action('admin_init', $plugin_admin, 'initOptions');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Mstoreapp_Mobile_App_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action('wp_ajax_mstoreapp-keys', $plugin_public, 'keys');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-keys', $plugin_public, 'keys');

        $this->loader->add_action('wp_ajax_mstoreapp-login', $plugin_public, 'login');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-login', $plugin_public, 'login');

        $this->loader->add_action('wp_ajax_mstoreapp-cart', $plugin_public, 'cart');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-cart', $plugin_public, 'cart');

        $this->loader->add_action('wp_ajax_mstoreapp-apply_coupon', $plugin_public, 'apply_coupon');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-apply_coupon', $plugin_public, 'apply_coupon');

        $this->loader->add_action('wp_ajax_mstoreapp-like', $plugin_public, 'like');
		$this->loader->add_action('wp_ajax_nopriv_mstoreapp-like', $plugin_public, 'like');
		
		$this->loader->add_action('wp_ajax_mstoreapp-check_payment_response', $plugin_public, 'check_payment_response');
		$this->loader->add_action('wp_ajax_nopriv_mstoreapp-check_payment_response', $plugin_public, 'check_payment_response');
		

        $this->loader->add_action('wp_ajax_mstoreapp-remove_coupon', $plugin_public, 'remove_coupon');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-remove_coupon', $plugin_public, 'remove_coupon');

        $this->loader->add_action('wp_ajax_mstoreapp-update_shipping_method', $plugin_public, 'update_shipping_method');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-update_shipping_method', $plugin_public, 'update_shipping_method');

        $this->loader->add_action('wp_ajax_mstoreapp-remove_cart_item', $plugin_public, 'remove_cart_item');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-remove_cart_item', $plugin_public, 'remove_cart_item');

        $this->loader->add_action('wp_ajax_mstoreapp-get_checkout_form', $plugin_public, 'get_checkout_form');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-get_checkout_form', $plugin_public, 'get_checkout_form');

        $this->loader->add_action('wp_ajax_mstoreapp-update_order_review', $plugin_public, 'update_order_review');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-update_order_review', $plugin_public, 'update_order_review');

        $this->loader->add_action('wp_ajax_mstoreapp-add_to_cart', $plugin_public, 'add_to_cart');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-add_to_cart', $plugin_public, 'add_to_cart');

        $this->loader->add_action('wp_ajax_mstoreapp-payment', $plugin_public, 'payment');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-payment', $plugin_public, 'payment');

        $this->loader->add_action('wp_ajax_mstoreapp-userdata', $plugin_public, 'userdata');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-userdata', $plugin_public, 'userdata');

        $this->loader->add_action('wp_ajax_mstoreapp-public-mobile-app-notification', $plugin_public, 'mobile_app_notification');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-public-mobile-app-notification', $plugin_public, 'mobile_app_notification');

        $this->loader->add_action('wp_ajax_mstoreapp-json_search_products', $plugin_public, 'json_search_products');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-json_search_products', $plugin_public, 'json_search_products');

        $this->loader->add_action('wp_ajax_mstoreapp-nonce', $plugin_public, 'nonce');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-nonce', $plugin_public, 'nonce');

        $this->loader->add_action('wp_ajax_mstoreapp-passwordreset', $plugin_public, 'passwordreset');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-passwordreset', $plugin_public, 'passwordreset');

        $this->loader->add_action('wp_ajax_mstoreapp-get_country', $plugin_public, 'get_country');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-get_country', $plugin_public, 'get_country');

        $this->loader->add_action('wp_ajax_mstoreapp-get_wishlist', $plugin_public, 'get_wishlist');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-get_wishlist', $plugin_public, 'get_wishlist');

        $this->loader->add_action('wp_ajax_mstoreapp-add_wishlist', $plugin_public, 'add_wishlist');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-add_wishlist', $plugin_public, 'add_wishlist');

        $this->loader->add_action('wp_ajax_mstoreapp-remove_wishlist', $plugin_public, 'remove_wishlist');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-remove_wishlist', $plugin_public, 'remove_wishlist');

        $this->loader->add_action('wp_ajax_mstoreapp-page_content', $plugin_public, 'pagecontent');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-page_content', $plugin_public, 'pagecontent');

        $this->loader->add_action('wp_ajax_mstoreapp-related_products', $plugin_public, 'get_related_products');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-related_products', $plugin_public, 'get_related_products');

        $this->loader->add_action('wp_ajax_mstoreapp-vendor-report', $plugin_public, 'vendor_report_sales_by_date');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-vendor-report', $plugin_public, 'vendor_report_sales_by_date');

        $this->loader->add_action('wp_ajax_mstoreapp_vendor_report_product', $plugin_public, 'vendor_report_sales_by_product');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp_vendor_report_product', $plugin_public, 'vendor_report_sales_by_product');

        $this->loader->add_action('wp_ajax_mstoreapp-vendor-order-list', $plugin_public, 'vendor_order_list');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-vendor-order-list', $plugin_public, 'vendor_order_list');

        $this->loader->add_action('wp_ajax_mstoreapp_set_fulfill_status', $plugin_public, 'set_fulfill_status');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp_set_fulfill_status', $plugin_public, 'set_fulfill_status');

        $this->loader->add_action('wp_ajax_mstoreapp-facebook_connect', $plugin_public, 'facebook_connect');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-facebook_connect', $plugin_public, 'facebook_connect');

	$this->loader->add_action('wp_ajax_mstoreapp-google_connect', $plugin_public, 'google_connect');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-google_connect', $plugin_public, 'google_connect');

	$this->loader->add_action('wp_ajax_mstoreapp-logout', $plugin_public, 'logout');
        $this->loader->add_action('wp_ajax_nopriv_mstoreapp-logout', $plugin_public, 'logout');
        

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
	 * @return    Mstoreapp_Mobile_App_Loader    Orchestrates the hooks of the plugin.
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
