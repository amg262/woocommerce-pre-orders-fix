<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 11/23/16
 * Time: 12:19 PM
 */

namespace WooPreOrderFix;


class WooSession
{

    // Hold the class instance.
    private static $instance = null;
    private $session, $cart, $cart_item, $session_id;


    // The constructor is private
    // to prevent initiation with outer code.
    private function __construct()
    {
        // The expensive process (e.g.,db connection) goes here.
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance()
    {
        if ( self::$instance === null ) {
            self::$instance = new WooSession();
        }

        return self::$instance;
    }


    public function woo_start_session()
    {

        if ( ! session_id() ) {
            session_start();
            $this->session_id = session_id();
        }

    }

    public function woo_end_session()
    {
        if ( session_id() ) {
            session_destroy();
            $this->session_id = null;
        }

    }

    public function woo_set_transient()
    {

    }

    /* public function woo_get_parent_order( $order_id ) {


         $order = new WC_Order( $order_id );
         $shipping = $order->get_shipping_address();
         $billing = $order->get_billing_address();
         /*$order = new WC_Order( $order_id );
          * Shipping

         $order->shipping_first_name
         $order->shipping_last_name
         $order->shipping_company
         $order->shipping_address_1
         $order->shipping_address_2
         $order->shipping_city
         $order->shipping_state
         $order->shipping_postcode
         $order->shipping_country
         Billing

         $order->billing_first_name
         $order->billing_last_name
         $order->billing_company
         $order->billing_address_1
         $order->billing_address_2
         $order->billing_city
         $order->billing_state
         $order->billing_postcode
         $order->billing_country


         $address = array(
             'first_name' => $order->shipping_first_name,
             'last_name'  => $order->shipping_last_name,
             'company'    => $order->shipping_company,
             'email'      => $email,
             'phone'      => '777-777-777-777',
             'address_1'  => $order->shipping_address_1,
             'address_2'  => $order->shipping_address_2,
             'city'       => $order->shipping_city,
             'state'      => $order->shipping_state,
             'postcode'   => $order->shipping_postcode,
             'country'    => $order->shipping_country
         );
     }*/

    public function woo_get_order( $order )
    {
        if ( ! is_object( $order ) ) {
            $order = new WC_Order( $order );
        }

        echo $order->get_billing_address();
        var_dump( $order );
    }

    public function woo_create_order()
    {

        //$parent_order = new WC_Order(5548);
        $args = array( 'sdf' => '4', 'nuu' => '2' );
        $order = wc_create_order();
        update_post_meta( $order->id, '_customer_user', get_current_user_id() );
        update_post_meta( $order->id, '_wc_pre_orders_is_pre_order', 1 );
        // $order->set_address( $parent_order->get_billing_address, 'billing' );
        $order->set_address( $args, 'shipping' );
        //$order->set_address( $parent_order->get_shipping_address, 'shipping' );
        $order->update_status( 'pre-ordered' );

        $order->order_custom_fields = get_post_custom( $order->id );

        var_dump( $order->get_billing_address );
        //$order->add_coupon( 'wmfreeship' ); // not pennies (use dollars amount)
        //$order->calculate_totals();


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

$object1 = Singleton::getInstance();
$woo_session = new WooSession();
//echo $woo_session['session_id'];