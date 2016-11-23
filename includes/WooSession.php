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

    private $session, $cart, $cart_item;

    /**
     * WooSession constructor.
     */
    public function __construct()
    {
        $cart = array();
    }


    public function start_session() {

        if(!session_id()) {
            session_start();
        }

    }

    public function end_session() {
        session_destroy();
    }


    public function add_cart_item( $item )
    {
        if ($item !== null) {

        }
    }


add_action('init', array($this, 'myStartSession'), 1);

    function myStartSession() {
        if(!session_id()) {
            session_start();
        }

        //var_dump(session_id());
    }

    function myEndSession() {
        session_destroy ();
    }
}