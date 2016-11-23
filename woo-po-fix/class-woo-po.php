<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 11/23/16
 * Time: 3:04 AM
 */

namespace WooPoFix;


class WooPo {


	private $cart, $cart_items, $po_items;

	/**
	 * WooPo constructor.
	 *
	 * @param $cart
	 */
	public function __construct( $cart ) {
		$this->cart = $cart;
	}



}