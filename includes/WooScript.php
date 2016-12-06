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
    public $var;
    /**
     * WooScript constructor.
     */
    public function __construct($id) {
        add_action( 'wp_enqueue_scripts', 'setup_script' );
        add_action('wp_footer','js_scripts',5);
        add_action('wp_footer','css_styles',10);

        $this->var = $id;

        //global $woocommerce;

        //var_dump($GLOBALS);

        //$o = new WC_Order();
        //var_dump($o);
        //var_dump($GLOBALS);


    }


    public function setup_script() {
        wp_enqueue_script( 'jquery' );
    }

    public function js_scripts() { ?>

        <?php //$var = get_query_var('order-pay'); ?>


            <script type="text/javascript">
                jQuery(document).ready(function($) {

                    alert('ok');
                    alert('<?php echo $this->var; ?>');
                    console.log('hi');
                    console.log('<?php echo $this->var; ?>');

                });
            </script>

    <?php }



    public function css_styles() { ?>
        <style type="text/css">

            <?php if (is_admin()) { ?>



            <?php } else { ?>



            <?php } ?>
        </style>
    <?php }

}