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
    private static $instance = null;
    private $session, $woo, $session_id;
    private $cart_items = array();
    private $parent_order;
    public $ship_labels, $bill_labels;
    private $shipping = array();
    private $billing = array();

    private $item, $index, $parent_id;

    // to prevent initiation with outer code.
    private function __construct()
    {
        $this->start_session();
        include_once __DIR__.'/WooScript.php';
        $scr = new WooScript();

        $this->ship_labels = array(
            'shipping_first_name',
            'shipping_last_name',
            'shipping_company',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_city',
            'shipping_state',
            'shipping_postcode',
            'shipping_country',
            'shipping_first_name',
            'shipping_first_name',

        );
        $this->bill_labels = array(
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_postcode',
            'billing_country',
            'billing_first_name',
            'billing_first_name',
        );

        add_action('woocommerce_checkout_order_processed', array($this, 'handle_pre_orders'));
    }

    private function __destruct()
    {
        $this->end_session();
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


    public function start_session()
    {
        if ( ! session_id() ) {
            session_start();
            $this->session_id = session_id();

            return $this->session_id;
        }
    }

    public function end_session()
    {
        if ( session_id() ) {
            session_destroy();
            $this->session_id = null;

            return $this->session_id;
        }
    }

    public function verify_session()
    {

        if ( session_id() === $this->session_id ) {
            print $this->session_id;

            return $this->session_id;
        } else {
            return null;
        }
    }

    public function clear_item_array()
    {
        $this->item = null;
        $this->cart_items = array();
    }

    public function add_item_to_array( $object )
    {

        if ( $object !== null ) {
            $this->item = $object;

            array_push( $this->cart_items, $this->item );
        }
    }

    public function output_item_array()
    {
        return $this->cart_items;
    }


    public function get_parent_order() {

        return $this->parent_order;
    }

    public function set_parent_order($order) {

        if ($order !== null) {
            $this->parent_order = $order;

            //var_dump($this->parent_order);

            foreach ($this->ship_labels as $row) {
                $field = $this->parent_order->$row;
                $this->shipping[$row] = $field;
            }

            foreach ($this->bill_labels as $row) {
                $field = $this->parent_order->$row;
                $this->billing[$row] = $field;
            }

        }

    }

    public function get_parent_bill_meta() {
        return $this->billing;
    }

    public function get_parent_ship_meta() {
        return $this->shipping;
    }



    public function handle_pre_orders() {
        $items = $this->output_item_array();
        $count = count($items);

        foreach ($items as $item) {

            $this->create_new_pre_order($item->prod_id, $item->qty);
        }

    }
    public function create_new_pre_order($prod_id, $qty, $var_id = null)
    {


        $order = wc_create_order();
        update_post_meta( $order->id, '_customer_user', get_current_user_id() );
        update_post_meta( $order->id, '_wc_pre_orders_is_pre_order', 1 );
        $order->add_product($prod_id, $qty);
        $order->set_address($this->get_parent_bill_meta(), 'billing' );
        $order->set_address($this->get_parent_ship_meta(), 'shipping' );
        //$order->set_address( $parent_order->get_shipping_address, 'shipping' );
        $order->update_status( 'pre-ordered' );

        //$order->order_custom_fields = get_post_custom( $order->id );

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
      //var_dump($order->get_billing_address);
      //$order->add_coupon( 'wmfreeship' ); // not pennies (use dollars amount)
      //$order->calculate_totals();


      /* indicate the order contains a pre-order
      * update_post_meta( $order_id, '_wc_pre_orders_is_pre_order', 1 );*/
    }
}
