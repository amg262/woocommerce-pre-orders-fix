<?php // Get out!


if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}


//note in multisite looping through blogs to delete options on each blog does not scale. You'll just have to leave them.
/*
* Getting options groups
*/

