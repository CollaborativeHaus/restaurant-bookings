<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Rb_defaults {


	protected $defaults = array();

	public function add_default( $arr ){
		$defaults = $this->defaults;
		$this->defaults = array_merge( $defaults , $arr);
	}
	
	public function get_default( $key ){
		if ( array_key_exists($key, $this->defaults) ){
			return $this->defaults[$key];

		}
			return null;
	}


}

function rb_init_defaults(){
	global $rb_defaults;
	
	$rb_defaults = new Rb_defaults();
	
	// $defaults = array(
	// 	// silence ...
	// );
	
	// $rb_defaults->add_default( $defaults );	
}
add_action( 'admin_init', 'rb_init_defaults', 1 );
add_action( 'init', 'rb_init_defaults', 1 );



?>