<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 11/23/16
 * Time: 3:04 AM
 */

namespace WooPoFix;


class WooPo
{


    private $po_items = array();
    private $current, $previous;

    /**
     * WooPo constructor.
     *
     * @param $cart
     */
    public function __construct()
    {

    }

    public function get_po_items()
    {
        return $this->po_items;

    }

    public function add_po_item($cart_item)
    {

        if ($cart_item !== null) {
            array_push($this->po_items, $cart_item);
        }

    }


}