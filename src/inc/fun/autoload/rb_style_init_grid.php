<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Enqueue style grid
function rb_style_init_grid() {
	wp_enqueue_style( 'rb_grid',  Rb_Restaurant_bookings::plugin_dir_url() . '/css/rb_grid.min.css' );
}
add_action( 'wp_enqueue_scripts', 'rb_style_init_grid' );

?>