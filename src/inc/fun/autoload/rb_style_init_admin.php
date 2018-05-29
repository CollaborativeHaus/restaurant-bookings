<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Enqueue style admin
function rb_style_init_admin() {
	wp_enqueue_style( 'rb_admin',  Rb_Restaurant_bookings::plugin_dir_url() . '/css/rb_admin.min.css' );
}
add_action( 'admin_enqueue_scripts', 'rb_style_init_admin' );

?>