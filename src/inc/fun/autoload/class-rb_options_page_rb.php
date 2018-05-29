<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * CMB2 Plugin Options
 * @version  taskRunner_set_version
 * @see      https://github.com/CMB2/CMB2-Snippet-Library/blob/59166b81693f4ab8651868e70cb29702576bd055/options-and-settings-pages/theme-options-cmb.php
 */
class Rb_Options_Page_Rb {

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'rb';

	private $tabs = array(
		
		'general' => array(
			'general' => 'General',
			//'metabox_form_args' => array(
			//	'save_button' => __('Save','restaurant-bookings')
			//)
		),
		
		'tables' => array(
			'tables' => 'Tables',
			//'metabox_form_args' => array(
			//	'save_button' => __('Save','restaurant-bookings')
			//)
		),
		
	);

	/**
 	 * Options page metabox ids
 	 * @var array
 	 */
	private $metabox_ids = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Pages hook
	 * @var array
	 */
	protected $options_pages = array();

	/**
	 * Holds an instance of the object
	 *
	 * @var Rb_Options_Page_Rb
	 */
	protected static $instance = null;

	/**
	 * Returns the running object
	 *
	 * @return Rb_Options_Page_Rb
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 * @since taskRunner_set_version
	 */
	protected function __construct() {
		// Set our title
		$this->title = __( 'Reservation Settings', 'restaurant-bookings' );

		foreach( $this->tabs as $key => $val ) {
			$this->metabox_ids[$key] = array( 'metabox_id'	=>	$this->key . '_' . $key );
			foreach( $val as $k => $v ) {
				$this->metabox_ids[$key][$k] = $v;
			}
		}
	}

	/**
	 * Initiate our hooks
	 * @since taskRunner_set_version
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );

		foreach( $this->metabox_ids as $key => $val ) {
			add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' . '__' . $key ) );
			add_action( 'cmb2_after_options-page_form_' . $val['metabox_id'], array( $this, 'enqueue_style_script'), 10, 2 );
		}

		add_action( 'cmb2_after_init', array( $this, 'handle_submission') );
	}

	/**
	 * Enqueue styles and scripts
	 * @since taskRunner_set_version
	 */
	public function enqueue_style_script( $post_id, $cmb ) {
		wp_enqueue_style( 'rb_options_page_rb', Rb_Restaurant_bookings::plugin_dir_url() . '/css/rb_options_page_rb.min.css', false );
		wp_enqueue_script('rb_options_page_rb', Rb_Restaurant_bookings::plugin_dir_url() . '/js/rb_options_page_rb.min.js', array( 'jquery' ));
	}

	/**
	 * Register our setting to WP
	 * @since  taskRunner_set_version
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since taskRunner_set_version
	 */
	public function add_options_page() {

		$this->options_page = add_menu_page(
			$this->title,
			$this->title,
			'manage_options',
			$this->key,
			array( $this, 'admin_page_display' ),
			''	// string $icon_url
		);
		

		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  taskRunner_set_version
	 */
	public function admin_page_display() {

		// get active tab
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : array_keys( $this->metabox_ids )[0];

		echo '<div class="wrap cmb2-options-page ' . $this->key . '">';
			echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

			// navigation tabs
			echo '<h2 class="nav-tab-wrapper">';
				foreach( $this->metabox_ids as $key => $val) {
					echo '<a href="?page=rb&tab=' . $key . '" class="nav-tab' . ($key === $active_tab ? ' nav-tab-active' : '') . '">' . __( $val[$key], 'restaurant-bookings') . '</a>';
				}
			echo '</h2>';

			// form
			cmb2_metabox_form(
				$this->metabox_ids[$active_tab]['metabox_id'],
				$this->key,
				isset( $this->metabox_ids[$active_tab]['metabox_form_args'] ) ? $this->metabox_ids[$active_tab]['metabox_form_args'] : array()
			);

		echo '</div>';
	}

	
	public function add_options_page_metabox__general() {
		$tab = 'general';

		$metabox_id = $this->key . '_' . $tab;

		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'option_key' => 'rb_general_options',
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Set our CMB2 fields
		$cmb->add_field( array(
			'name' => __( 'Test Text', 'restaurant-bookings' ),
			'desc' => __( 'field description (optional)', 'restaurant-bookings' ),
			'id'   => $tab . '_' . 'test_text',
			'type' => 'text',
			'default' => 'Default Text',
		) );

		$cmb->add_field( array(
			'name'    => __( 'Test Color Picker', 'restaurant-bookings' ),
			'desc'    => __( 'field description (optional)', 'restaurant-bookings' ),
			'id'   => $tab . '_' . '_test_colorpicker',
			'type'    => 'colorpicker',
			'default' => '#bada55',
		) );

	}

	
	public function add_options_page_metabox__tables() {
		$tab = 'tables';

		$metabox_id = $this->key . '_' . $tab;

		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'option_key' 	=> 'rb_table_options',
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		$group_field_id = $cmb->add_field( array(
			'id'          => $tab . '_' . 'table_group',
			'type'        => 'group',
			'description' => __( 'Details of individual tables.', 'restaurant-bookings' ), 
			// 'repeatable'  => false, // use false if you want non-repeatable group
			'options'     => array(
				'group_title'   => __( 'Table {#}', 'restaurant-bookings' ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __( 'Add Another Table', 'restaurant-bookings' ),
				'remove_button' => __( 'Remove Table', 'restaurant-bookings' ),
				'sortable'      => true, // beta
				// 'closed'     => true, // true to have the groups closed by default
			),
		) );
		
		// Id's for group's fields only need to be unique for the group. Prefix is not needed.
		$cmb->add_group_field( $group_field_id, array(
			'name' => 'Table Name',
			'id'   => 'title',
			'type' => 'text',
			// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => 'Description',
			'description' => 'Write a short description for this table',
			'id'   => 'description',
			'type' => 'textarea_small',
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => 'Number of Seats',
			'id'   => 'seats',
			'type' => 'text',
		) );

	}

	// /**
	//  * Wrapper function around cmb2_get_option
	//  * @since  0.1.0
	//  * @param  string $key     Options array key
	//  * @param  mixed  $default Optional default value
	//  * @return mixed           Option value
	//  */
	// function rb_get_option( $key = '', $default = false ) {
	// 	if ( function_exists( 'cmb2_get_option' ) ) {
	// 		// Use cmb2_get_option as it passes through some key filters.
	// 		return cmb2_get_option( 'rb_options', $key, $default );
	// 	}
	// 	// Fallback to get_option if CMB2 is not loaded yet.
	// 	$opts = get_option( 'rb_options', $default );
	// 	$val = $default;
	// 	if ( 'all' == $key ) {
	// 		$val = $opts;
	// 	} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
	// 		$val = $opts[ $key ];
	// 	}
	// 	return $val;
	// }


	protected function get_metabox_by_nonce( $nonce, $return = 'metabox' ) {
		if (! $nonce || ! strpos($nonce, 'nonce_CMB2php') === 0 )
			return false;

		$metabox_id = str_replace( 'nonce_CMB2php', '', $nonce );

		switch ( $return ){
			case 'metabox':
				return cmb2_get_metabox( $metabox_id, $this->key );
				break;
			case 'metabox_id':
				return $metabox_id;
				break;
			case 'tab_name':
				return str_replace( $this->key . '_', '', $metabox_id );
				break;
			default:
				// silence ...
		}

	}

	public function handle_submission() {

		// is form submission?
		if ( empty( $_POST ) || ! isset( $_POST['submit-cmb'], $_POST['object_id'] ) ) return false;
		// is rb form submission?
		if ( ! $_POST['object_id'] == $this->key ) return false;

		// get nonce, metabox, tab_name
		$nonce = array_keys( $this->preg_grep_keys('/nonce_CMB2php\w+/', $_POST ) )[0];
		$tab_name = $this->get_metabox_by_nonce( $nonce, 'tab_name');
		$cmb = $this->get_metabox_by_nonce( $nonce );
		if (! $cmb ) return false;

		// Check security nonce
		if ( ! isset( $_POST[ $cmb->nonce() ] ) || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
			// error, Something went wrong, Nonce verification failed
			return;
		}

		// Fetch sanitized values
		$sanitized_values = $cmb->get_sanitized_values( $_POST );

		switch ( $tab_name ){
			
			case 'general':
				break;
			
			case '':
				break;
			
			default:
				// silence ...
		}

	}

	public function preg_grep_keys( $pattern, $input, $flags = 0 ){
		$keys = preg_grep( $pattern, array_keys( $input ), $flags );
		$vals = array();
		foreach ( $keys as $key )    {
			$vals[$key] = $input[$key];
		}
		return $vals;
	}


	/**
	 * Register settings notices for display
	 *
	 * @since  taskRunner_set_version
	 * @param  int   $object_id Option key
	 * @param  array $updated   Array of updated fields
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'restaurant-bookings' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}

	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  taskRunner_set_version
	 * @param  string  $field Field to retrieve
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

/**
 * Helper function to get/return the Rb_Options_Page_Rb object
 * @since  taskRunner_set_version
 * @return Rb_Options_Page_Rb object
 */
function rb_options_page_rb() {
	return Rb_Options_Page_Rb::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  taskRunner_set_version
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function rb_rb_get_option( $key = '', $default = null ) {
	if ( function_exists( 'cmb2_get_option' ) ) {
		// Use cmb2_get_option as it passes through some key filters.
		return cmb2_get_option( rb_options_page_rb()->key, $key, $default );
	}

	// Fallback to get_option if CMB2 is not loaded yet.
	$opts = get_option( rb_options_page_rb()->key, $key, $default );

	$val = $default;

	if ( gettype($opts) === 'array' && !empty($opts) ){
		if ( 'all' == $key ) {
			$val = $opts;
		} elseif ( array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}
	}

	return $val;
}

// Get it started
rb_options_page_rb();


?>