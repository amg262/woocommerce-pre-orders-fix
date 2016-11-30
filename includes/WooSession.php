<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 11/23/16
 * Time: 12:19 PM
 */

namespace WooSession;


class WooSession
{

    private $session, $cart, $cart_item, $session_id;

    /**
     * WooSession constructor.
     */
    public function __construct()
    {
        add_action('admin_init', array($this, 'woo_create_order'));
    }


    public function woo_start_session() {

        if(!session_id()) {
            session_start();
            $this->session_id = session_id();
        }

    }

    public function woo_end_session() {
        if (session_id()) {
            session_destroy();
            $this->session_id = null;
        }

    }

    public function woo_set_transient() {
        
    }

    public function woo_get_parent_order($order_id) {

    }
    
    public function woo_create_order() {
        $order = wc_create_order();
        update_post_meta($order->id, '_customer_user', get_current_user_id() );
        update_post_meta( $order_id, '_wc_pre_orders_is_pre_order', 1 );
        $order->set_address( $address_billing, 'billing' );
        $order->set_address( $address_shipping, 'shipping' );
        $order->update_status( 'pre-ordered' );
        $order->add_coupon( 'wmfreeship' ); // not pennies (use dollars amount)
        $order->calculate_totals();
        

        /* indicate the order contains a pre-order
        * update_post_meta( $order_id, '_wc_pre_orders_is_pre_order', 1 );
 /*
 * // save when the pre-order amount was charged (either upfront or upon release)
 * update_post_meta( $order_id, '_wc_pre_orders_when_charged', $product->wc_pre_orders_when_to_charge );
 *
 * $order->update_status( 'pre-ordered' );
 * $order->order_custom_fields = get_post_custom( $order->id );
 *
 *
 * return (bool) $order->order_custom_fields['_wc_pre_orders_is_pre_order'][0];
 *
 *
 *
 * $order = wc_create_order();
 * $order->add_product( get_product( '12' ), 2 ); //(get_product with id and next is for quantity)
 * $order->set_address( $address, 'billing' );
 * $order->set_address( $address, 'shipping' );
 * $order->add_coupon('Fresher','10','2'); // accepted param $couponcode, $couponamount,$coupon_tax
 * $order->calculate_totals();
 *
 * $order->calculate_totals();
 * // assign the order to the current user
 * update_post_meta($order->id, '_customer_user', get_current_user_id() );
 * // payment_complete
  *
  *//////
        ///

    }


}

$woo_session = new WooSession();
//echo $woo_session['session_id'];