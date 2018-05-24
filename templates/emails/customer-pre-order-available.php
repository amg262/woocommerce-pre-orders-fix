<?php
/**
 * WooCommerce Pre-Orders
 *
 * @package     WC_Pre_Orders/Templates/Email
 * @author      WooThemes
 * @copyright   Copyright (c) 2013, WooThemes
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Customer pre-order available notification email
 *
 * @since 1.0.0
 * @version 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
$pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );
$billing_email = $pre_wc_30 ? $order->billing_email : $order->get_billing_email();
$billing_phone = $pre_wc_30 ? $order->billing_phone : $order->get_billing_phone();

if ( 'pending' === $order->get_status() && ! WC_Pre_Orders_Manager::is_zero_cost_order( $order ) ) : ?>

	<p><?php
/* translators: 1: href link for checkout payment url 2: closing href link */
printf( __( "Your pre-order is now available, but requires payment. %sPlease pay for your pre-order now.%s", 'wc-pre-orders' ), '<a href="' . $order->get_checkout_payment_url() . '">', '</a>' ); ?></p>

<?php elseif ( 'failed' === $order->get_status() || 'on-hold' === $order->get_status() ) : ?>

	<p><?php
/* translators: 1: href link for checkout payment url 2: closing href link */
printf( __( "Your pre-order is now available, but automatic payment failed. %sPlease update your payment information now.%s", 'wc-pre-orders' ), '<a href="' . $order->get_checkout_payment_url() . '">', '</a>' ); ?></p>

<?php else : ?>

<p><?php _e( "Your pre-order is now available. Your order details are shown below for your reference.", 'wc-pre-orders' ); ?></p>

<?php endif; ?>

<?php if ( $message ) : ?>
	<blockquote><?php echo wpautop( wptexturize( $message ) ); ?></blockquote>
<?php endif; ?>

<?php do_action( 'woocommerce_email_before_order_table', $order, false, $plain_text, $email ); ?>

<h2><?php echo __( 'Order:', 'wc-pre-orders' ) . ' ' . $order->get_order_number(); ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'wc-pre-orders' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'wc-pre-orders' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Price', 'wc-pre-orders' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php echo $pre_wc_30 ? $order->email_order_items_table() : wc_get_email_order_items( $order ); ?>
	</tbody>
	<tfoot>
		<?php
			if ( $totals = $order->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?><tr>
						<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['label']; ?></th>
						<td style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; ?></td>
					</tr><?php
				}
			}
		?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, false, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, false, $plain_text, $email ); ?>

<h2><?php _e( 'Customer details', 'wc-pre-orders' ); ?></h2>

<?php if ( $billing_email ) : ?>
	<p><strong><?php _e( 'Email:', 'wc-pre-orders' ); ?></strong> <?php echo $billing_email; ?></p>
<?php endif; ?>
<?php if ( $billing_phone ) : ?>
	<p><strong><?php _e( 'Tel:', 'wc-pre-orders' ); ?></strong> <?php echo $billing_phone; ?></p>
<?php endif; ?>

<?php wc_get_template( 'emails/email-addresses.php', array( 'order' => $order ) ); ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
