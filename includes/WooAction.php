<?php
/**
 * Copyright (c) 2016.  |  Andrew M. Gunn
 * andrewgunn.org  |  github.com/andrewgunn1992
 *
 */

/**
 * Created by PhpStorm.
 * User: andy
 * Date: 11/30/16
 * Time: 4:07 AM
 */

namespace WooPreOrderFix;


class WooAction
{
    /**
     * WooAction constructor.
     */
    public function __construct() {
        add_action('woocommerce_checkout_order_processed', array($this, 'fah'));
        add_action('woocommerce_new_order', array($this, 'fah'));
    }
}