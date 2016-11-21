<?php
/**
 * WooCommerce Pre-Orders
 *
 * @package   WC_Pre_Orders/My_Pre_Orders
 * @author    WooThemes
 * @copyright Copyright (c) 2015, WooThemes
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) )  {
	exit;
}

/**
 * My Pre-Orders class
 *
 * @since 1.4.4
 */
class WC_Pre_Orders_My_Pre_Orders {

	/**
	 * Adds needed hooks / filters
	 */
	public function __construct() {
		// Display pre-orders on a user's account page
		add_action( 'woocommerce_before_my_account', array( $this, 'my_pre_orders' ) );
	}

	/**
	 * Output "My Pre-Orders" table in the user's My Account page
	 */
	public function my_pre_orders() {
		global $wc_pre_orders;

		$pre_orders = WC_Pre_Orders_Manager::get_users_pre_orders();
		$items      = array();
		$actions    = array();

		foreach ( $pre_orders as $order ) {
			$_actions   = array();
			$order_item = WC_Pre_Orders_Order::get_pre_order_item( $order );

			// Stop if the pre-order is complete
			if ( is_null( $order_item ) ) {
				continue;
			}

			// Set the items for the table
			$items[] = array(
				'order' => $order,
				'data'  => $order_item
			);

			// Determine the available actions (Cancel)
			if ( WC_Pre_Orders_Manager::can_pre_order_be_changed_to( 'cancelled', $order ) ) {
				$_actions['cancel'] = array(
					'url'  => WC_Pre_Orders_Manager::get_users_change_status_link( 'cancelled', $order ),
					'name' => __( 'Cancel', 'wc-pre-orders' )
				);
			}

			$actions[ $order->id ] = $_actions;
		}

		// Load the template
		woocommerce_get_template(
			'myaccount/my-pre-orders.php',
			array(
				'pre_orders' => $pre_orders,
				'items'      => $items,
				'actions'    => $actions,
			),
			'',
			$wc_pre_orders->get_plugin_path() . '/templates/'
		);
	}
}

new WC_Pre_Orders_My_Pre_Orders();
