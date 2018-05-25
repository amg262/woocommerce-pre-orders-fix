<?php
/**
 * Plugin Name: WooCommerce Pre-Orders Fix
 * Plugin URI: https://woocommerce.com/products/woocommerce-pre-orders/
 * Description: Sell pre-orders for products in your WooCommerce store
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * Version: 1.5.4
 * Text Domain: wc-pre-orders
 * Domain Path: /languages/
 * WC tested up to: 3.2
 * WC requires at least: 2.6
 *
 * Copyright: (c) 2015-2017 WooCommerce
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * See https://docs.woocommerce.com/document/pre-orders/ for full documentation.
 *
 * Woo: 178477:b2dc75e7d55e6f5bbfaccb59830f66b7
 *
 * @package   WC_Pre_Orders
 * @author    WooCommerce
 * @category  Marketing
 * @copyright Copyright (c) 2017, WooCommerce
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Required functions.
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( __DIR__ . '/woo-includes/woo-functions.php' );
}

// Plugin updates.
woothemes_queue_update( plugin_basename( __FILE__ ), 'b2dc75e7d55e6f5bbfaccb59830f66b7', '178477' );

// Check if WooCommerce is active and deactivate extension if it's not.
if ( ! is_woocommerce_active() ) {
	return;
}

/**
 * The WC_Pre_Orders global object
 *
 * @name                 $wc_pre_orders
 * @global WC_Pre_Orders $GLOBALS ['wc_pre_orders']
 */
$GLOBALS['wc_pre_orders'] = new WC_Pre_Orders();

global $wc_obj, $woocommerce;

define( 'WC_PRE_ORDERS_VERSION', '1.5.4' );

const WCO_FILE = __FILE__;
const WCO_URL  = __DIR__ . '/woocommerce-pre-orders-fix.php';

const WCO_DIR = __DIR__;
const WCO_INC = __DIR__ . '/includes/';
const WCO_JS  = __DIR__ . '/includes/wc-bom-admin.js';
const WCO_CSS = __DIR__ . '/includes/wc-bom.css';

/**
 * Main Plugin Class
 *
 * @since 1.0
 */
class WC_Pre_Orders {

	/**
	 * Plugin file path without trailing slash
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * Plugin url without trailing slash
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * WC_Logger instance
	 *
	 * @var object
	 */
	private $logger;

	/**
	 * Setup main plugin class
	 *
	 * @since  1.0
	 * @return \WC_Pre_Orders
	 */
	public function __construct() {

		// load core classes
		$this->load_classes();

		// load classes that require WC to be loaded
		add_action( 'woocommerce_init', [ $this, 'init' ] );

		// add pre-order notification emails
		add_filter( 'woocommerce_email_classes', [ $this, 'add_email_classes' ] );

		// add 'pay later' payment gateway
		add_filter( 'woocommerce_payment_gateways', [ $this, 'add_pay_later_gateway' ] );

		// Hook up emails
		foreach (
			[
				'wc_pre_order_status_new_to_active',
				'wc_pre_order_status_completed',
				'wc_pre_order_status_active_to_cancelled',
				'wc_pre_orders_pre_order_date_changed',
			] as $action
		) {
			add_action( $action, [ $this, 'send_transactional_email' ], 10, 2 );
		}

		// Load translation files
		add_action( 'init', [ $this, 'load_translation' ] );

		// When plugin is activated.
		register_activation_hook( __FILE__, [ $this, 'activate' ] );

		// Un-schedule events on plugin deactivation
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
	}

	/**
	 * Load core classes
	 *
	 * @since 1.0
	 */
	public function load_classes() {

		// load wp-cron hooks for scheduled events
		require( __DIR__ . '/includes/class-wc-pre-orders-cron.php' );
		$this->cron = new WC_Pre_Orders_Cron();

		// load manager class to process pre-order actions
		require( __DIR__ . '/includes/class-wc-pre-orders-manager.php' );
		$this->manager = new WC_Pre_Orders_Manager();

		// load product customizations / tweaks
		require( __DIR__ . '/includes/class-wc-pre-orders-product.php' );
		$this->product = new WC_Pre_Orders_Product();

		// Load cart customizations / overrides
		require( __DIR__ . '/includes/class-wc-pre-orders-cart.php' );
		$this->cart = new WC_Pre_Orders_Cart();

		// Load checkout customizations / overrides
		require( __DIR__ . '/includes/class-wc-pre-orders-checkout.php' );
		$this->checkout = new WC_Pre_Orders_Checkout();

		// Load order hooks
		require( __DIR__ . '/includes/class-wc-pre-orders-order.php' );
		$this->order = new WC_Pre_Orders_Order();

		include_once( __DIR__ . '/includes/class-wc-pre-orders-my-pre-orders.php' );
	}

	/**
	 * Get supported product types.
	 *
	 * @return array
	 */
	public static function get_supported_product_types() {

		$product_types = [
			'simple',
			'variable',
			'composite',
			'bundle',
			'booking',
			'mix-and-match',
		];

		return apply_filters( 'wc_pre_orders_supported_product_types', $product_types );
	}

	/**
	 * Load actions and filters that require WC to be loaded
	 *
	 * @since 1.0
	 */
	public function init() {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {

			// Load admin.
			require( __DIR__ . '/includes/admin/class-wc-pre-orders-admin.php' );

			// add a 'Configure' link to the plugin action links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );

		} else {

			// Watch for cancel URL action
			add_action( 'init', [ $this->manager, 'check_cancel_pre_order' ] );

			// add countdown shortcode
			add_shortcode( 'woocommerce_pre_order_countdown', [ $this, 'pre_order_countdown_shortcode' ] );
		}
	}

	/**
	 * Add the 'pay later' gateway, which replaces gateways that do not support pre-orders when the pre-order is charged
	 * upon release
	 *
	 * @since 1.0
	 */
	public function add_pay_later_gateway( $gateways ) {

		require_once( __DIR__ . '/includes/gateways/class-wc-pre-orders-gateway-pay-later.php' );

		$gateways[] = 'WC_Pre_Orders_Gateway_Pay_Later';

		return $gateways;
	}

	/**
	 * Pre-order countdown shortcode
	 *
	 * @param array $atts associative array of shortcode parameters
	 *
	 * @return string shortcode content
	 */
	public function pre_order_countdown_shortcode( $atts ) {

		require_once( __DIR__ . '/includes/shortcodes/class-wc-pre-orders-shortcode-countdown.php' );

		return WC_Shortcodes::shortcode_wrapper( [
			'WC_Pre_Orders_Shortcode_Countdown',
			'output',
		], $atts, [ 'class' => 'woocommerce-pre-orders' ] );
	}

	/**
	 * Adds Pre-Order email classes
	 *
	 * @since 1.0
	 */
	public function add_email_classes( $email_classes ) {

		foreach (
			[
				'new-pre-order',
				'pre-order-available',
				'pre-order-cancelled',
				'pre-order-date-changed',
				'pre-ordered',
			] as $class_file_name
		) {
			require_once( __DIR__ . "/includes/emails/class-wc-pre-orders-email-{$class_file_name}.php" );
		}

		$email_classes['WC_Pre_Orders_Email_New_Pre_Order']          = new WC_Pre_Orders_Email_New_Pre_Order();
		$email_classes['WC_Pre_Orders_Email_Pre_Ordered']            = new WC_Pre_Orders_Email_Pre_Ordered();
		$email_classes['WC_Pre_Orders_Email_Pre_Order_Date_Changed'] = new WC_Pre_Orders_Email_Pre_Order_Date_Changed();
		$email_classes['WC_Pre_Orders_Email_Pre_Order_Cancelled']    = new WC_Pre_Orders_Email_Pre_Order_Cancelled();
		$email_classes['WC_Pre_Orders_Email_Pre_Order_Available']    = new WC_Pre_Orders_Email_Pre_Order_Available();

		return $email_classes;
	}

	/**
	 * Sends transactional email by hooking into pre-order status changes
	 *
	 * @since 1.0
	 */
	public function send_transactional_email( $args = [], $message = '' ) {

		global $woocommerce;

		$woocommerce->mailer();

		do_action( current_filter() . '_notification', $args, $message );
	}

	/**
	 * Actions to perform when plugin is activated.
	 *
	 * @since 1.4.7
	 */
	public function activate() {

		add_rewrite_endpoint( 'pre-orders', EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}

	/**
	 * Remove terms and scheduled events on plugin deactivation
	 *
	 * @since 1.0
	 */
	public function deactivate() {

		// Remove scheduling function before removing scheduled hook, or else it will get re-added
		remove_action( 'init', [ $this->cron, 'add_scheduled_events' ] );

		// clear pre-order completion check event
		wp_clear_scheduled_hook( 'wc_pre_orders_completion_check' );
	}

	/**
	 * Return the plugin action links.
	 *
	 * @param  array $actions Associative array of action names to anchor tags.
	 *
	 * @return array          Associative array of plugin action links.
	 */
	public function plugin_action_links( $actions ) {

		$plugin_actions = [
			'manage'  => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc_pre_orders' ) ), __( 'Manage Pre-Orders', 'wc-pre-orders' ) ),
			'support' => '<a href="https://woocommerce.com/my-account/create-a-ticket/">' . __( 'Support', 'wc-pre-orders' ) . '</a>',
			'docs'    => '<a href="https://docs.woocommerce.com/document/pre-orders/">' . __( 'Docs', 'wc-pre-orders' ) . '</a>',
		];

		return array_merge( $plugin_actions, $actions );
	}

	/**
	 * Load plugin text domain
	 *
	 * @since  1.0
	 */
	public function load_translation() {

		load_plugin_textdomain( 'wc-pre-orders', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Returns the plugin's path without a trailing slash
	 *
	 * @since  1.0
	 *
	 * @return string the plugin path
	 */
	public function get_plugin_path() {

		if ( $this->plugin_path ) {
			return $this->plugin_path;
		}

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Returns the plugin's url without a trailing slash
	 *
	 * @since  1.0
	 *
	 * @return string the plugin url
	 */
	public function get_plugin_url() {

		if ( $this->plugin_url ) {
			return $this->plugin_url;
		}

		return $this->plugin_url = plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );
	}

	/**
	 * Log errors to WooCommerce log
	 *
	 * @since 1.0
	 *
	 * @param string $message message to log
	 */
	public function log( $message ) {

		global $woocommerce;

		if ( ! is_object( $this->logger ) ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->logger = new WC_Logger();
			} else {
				$this->logger = $woocommerce->logger();
			}
		}

		$this->logger->add( 'pre-orders', $message );
	}
}

/**
 * Created by PhpStorm.
 * User: andy
 * Date: 5/24/18
 * Time: 5:45 PM
 */
class WC_Object {


	protected static $instance = null;
	private $cart;
	private $products = [];
	private $po_prods = [];
	private $prods = [];
	private $parent_order;
	private $child_order;
	private $linked_orders = [];
	private $po_count;

	/**
	 * WC_Related_Products constructor.
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * WC_Related_Products constructor.
	 */
	public function init() {

		global $wc_obj;//, $wcb_data;

		add_action( 'admin_init', [ $this, 'upgrade_data' ] );

		//register_activation_hook( __FILE__, [ $this, 'activate' ] );
		//register_deactivation_hook( __FILE__, [ $this, 'activate' ] );
		//add_filter( 'plugin_action_links', [ $this, 'plugin_links' ], 10, 5 );
	}


	/**
	 * @return mixed
	 */
	public function getCart() {

		return $this->cart;
	}

	/**
	 * @param mixed $cart
	 */
	public function setCart( $cart ) {

		$this->cart = $cart;
	}

	/**
	 * @return array
	 */
	public function getProducts() {

		return $this->products;
	}

	/**
	 * @param array $products
	 */
	public function setProducts( $products ) {

		$this->products = $products;
	}

	/**
	 * @return array
	 */
	public function getPoProds() {

		return $this->po_prods;
	}

	/**
	 * @param array $po_prods
	 */
	public function setPoProds( $po_prods ) {

		$this->po_prods = $po_prods;
	}

	/**
	 * @return array
	 */
	public function getProds() {

		return $this->prods;
	}

	/**
	 * @param array $prods
	 */
	public function setProds( $prods ) {

		$this->prods = $prods;
	}

	/**
	 * @return mixed
	 */
	public function getParentOrder() {

		return $this->parent_order;
	}

	/**
	 * @param mixed $parent_order
	 */
	public function setParentOrder( $parent_order ) {

		$this->parent_order = $parent_order;
	}

	/**
	 * @return mixed
	 */
	public function getChildOrder() {

		return $this->child_order;
	}

	/**
	 * @param mixed $child_order
	 */
	public function setChildOrder( $child_order ) {

		$this->child_order = $child_order;
	}

	/**
	 * @return array
	 */
	public function getLinkedOrders() {

		return $this->linked_orders;
	}

	/**
	 * @param array $linked_orders
	 */
	public function setLinkedOrders( $linked_orders ) {

		$this->linked_orders = $linked_orders;
	}

	/**
	 * @return mixed
	 */
	public function getPoCount() {

		return $this->po_count;
	}

	/**
	 * @param mixed $po_count
	 */
	public function setPoCount( $po_count ) {

		$this->po_count = $po_count;
	}


	/**
	 *
	 */
	public function load_assets() {

		$url  = 'assets/';
		$url2 = 'assets/';

		wp_register_script( 'bom_adm_js', plugins_url( 'includes/wc-bom-admin.js', 'woocommerce-pre-orders-fix.php' ), [ 'jquery' ] );
		wp_enqueue_script( 'bom_adm_js' );
		wp_register_style( 'bom_css', plugins_url( 'includes/wc-bom-admin.js', 'woocommerce-pre-orders-fix.php' ) );
		wp_enqueue_style( 'bom_css' );
	}

	/**
	 *
	 */
	public function delete_db() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'wc_obj';

		$wpdb->query( "DROP TABLE IF EXISTS " . $table_name . "" );
	}

	/**
	 *
	 */
	public function upgrade_data() {

		global $wpdb, $wc_obj;


		if ( ! get_option( 'wc_obj' ) ) {
			add_option( 'wc_obj', [ 'init' => true, 'db' => 1 ] );
		}

		$wc_obj = get_option( 'wc_obj' );

		$table_name = $wpdb->prefix . 'wc_obj';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
					id int(11) NOT NULL AUTO_INCREMENT,
					title varchar(255),
					post_id int(11),
					type varchar(255),
					sub_ids text,
					data text ,
					time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					active tinyint(1) DEFAULT -1 NOT NULL,
					PRIMARY KEY  (id)
				);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		update_option( WCB_DATA, $this->data );
		dbDelta( $sql );


		return true;

	}

	/**
	 *
	 */
	public function install_data() {

		global $wpdb;

		$welcome_name = 'Mr. WordPress';
		$welcome_text = 'Congratulations, you just completed the installation!';

		$table_name = $wpdb->prefix . WCB_TBL;

		$wpdb->insert( $table_name, [
			'time' => current_time( 'mysql' ),
			'name' => $welcome_name,
			'data' => $welcome_text,
			'url'  => 'http://cloudground.net/',
		] );
	}


}




