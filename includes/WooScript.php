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
 * Time: 3:45 AM
 */

namespace WooPreOrderFix;


class WooScript
{
    /**
     * WooScript constructor.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', 'woo_setup_scripts' );
        add_action('wp_footer','woo_scripts',5);
        add_action('wp_footer','woo_styles',10);
    }

    
    public function setup_script() {
        wp_enqueue_script( 'jquery' );
    }

    public function js_scripts() { ?>

        <?php if (is_admin()) : ?>

            <script type="text/javascript">
                jQuery(document).ready(function($) {


                });
            </script>

        <?php else : ?>

            <script type="text/javascript">
                jQuery(document).ready(function($) {


                });
            </script>

        <?php endif; ?>

    <?php }



    public function css_styles() { ?>
        <style type="text/css">

            <?php if (is_admin()) { ?>



            <?php } else { ?>



            <?php } ?>
        </style>
    <?php }

}