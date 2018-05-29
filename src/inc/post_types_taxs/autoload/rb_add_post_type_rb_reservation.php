<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register a rb_reservation post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function rb_add_post_type_rb_reservation() {

	$post_type = 'rb_reservation';
	$labels = array(
		'name'                  => _x( 'Reservations', 'Post Type General Name', 'restaurant-bookings' ),
		'singular_name'         => _x( 'Reservation', 'Post Type Singular Name', 'restaurant-bookings' ),
		'menu_name'             => __( 'Reservations', 'restaurant-bookings' ),
		'name_admin_bar'        => __( 'Reservation', 'restaurant-bookings' ),
		'archives'              => __( 'Reservations', 'restaurant-bookings' ),
		'attributes'            => __( 'Reservation Attributes', 'restaurant-bookings' ),
		'parent_item_colon'     => __( 'Parent Reservation:', 'restaurant-bookings' ),
		'all_items'             => __( 'All Reservations', 'restaurant-bookings' ),
		'add_new_item'          => __( 'Add New Reservation', 'restaurant-bookings' ),
		'add_new'               => __( 'Add New', 'restaurant-bookings' ),
		'new_item'              => __( 'New Reservation', 'restaurant-bookings' ),
		'edit_item'             => __( 'Edit Reservation', 'restaurant-bookings' ),
		'update_item'           => __( 'Update Reservation', 'restaurant-bookings' ),
		'view_item'             => __( 'View Reservation', 'restaurant-bookings' ),
		'view_items'            => __( 'View Reservations', 'restaurant-bookings' ),
		'search_items'          => __( 'Search Reservation', 'restaurant-bookings' ),
		'not_found'             => __( 'Not found', 'restaurant-bookings' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'restaurant-bookings' ),
		'featured_image'        => __( 'Featured Image', 'restaurant-bookings' ),
		'set_featured_image'    => __( 'Set featured image', 'restaurant-bookings' ),
		'remove_featured_image' => __( 'Remove featured image', 'restaurant-bookings' ),
		'use_featured_image'    => __( 'Use as featured image', 'restaurant-bookings' ),
		'insert_into_item'      => __( 'Insert into Reservation', 'restaurant-bookings' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Reservation', 'restaurant-bookings' ),
		'items_list'            => __( 'Reservations list', 'restaurant-bookings' ),
		'items_list_navigation' => __( 'Reservations list navigation', 'restaurant-bookings' ),
		'filter_items_list'     => __( 'Filter Reservations list', 'restaurant-bookings' ),
	);

	$args = array(
		'label'                 => __( 'Reservation', 'restaurant-bookings' ),
		'description'           => __( 'Reservation description', 'restaurant-bookings' ),
		'labels'                => $labels,
		'supports'              => array('title'),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'menu_icon'             => 'dashicons-calendar-alt',	// https://developer.wordpress.org/resource/dashicons/#admin-page
		'show_in_rest'          => true,
		'rest_base'          	=> $post_type . 's', 
		'capability_type'		=> 'post',
		'register_meta_box_cb' => 'rb_add_reservation_metaboxes',
	);
	register_post_type( $post_type, $args );

}
add_action( 'init', 'rb_add_post_type_rb_reservation' );
add_action( 'rb_on_activate_before_flush', 'rb_add_post_type_rb_reservation' );

function rb_add_reservation_metaboxes() {
	add_meta_box( 'rb_reservation_meta', esc_html__( 'Reservation Details', 'restaurant-bookings' ), 'rb_reservation_meta', 'rb_reservation', 'normal', 'default' );
}

/**
 * Reservation Details Metabox
 */
function rb_reservation_meta() {
	$fields = get_reservation_fields();
	$current_values = array();

	$tables = rb_get_option( 'tables_table_group', 'rb' );

	foreach ($fields as $field) {
		$current_values[$field['name']] = get_post_meta( get_the_ID(), $field['name'], true ) != '' ? get_post_meta( get_the_ID(), $field['name'], true ) : '';
	}

	?>
	<div class="rb-reservation">
		<?php
			foreach ($fields as $field) {
				?>
					<div class="rb-reservation__field-container">
						<label class="rb-reservation__label" for="<?php echo $field['name'] ?>">
							<?php _e($field['label'], 'restaurant-bookings'); ?>
						</label>
						<?php
							if ($field['name'] == 'rb_reservation_table') {
								?>
									<select class="rb-reservation__control" name="<?php echo $field['name'] ?>" id="<?php echo $field['name'] ?>">
										<?php
											foreach ($tables as $table) {
												?>
													<option value="<?php echo $table['title'] ?>">
														<?php echo $table['title'] ?>
													</option>
												<?php
											}
										?>
									</select>
								<?php
							} elseif ($field['type'] == 'longtext') {
								?>
									<textarea class="rb-reservation__control rb-reservation__control--textarea" name="<?php echo $field['name'] ?>" id="<?php echo $field['name'] ?>"><?php echo wp_kses_post($current_values[$field['name']]); ?></textarea>
								<?php
							} else {
							?>
								<input type="<?php echo $field['type'] ?>" class="rb-reservation__control" name="<?php echo $field['name'] ?>" id="<?php echo $field['name'] ?>" <?php echo $field['required'] ? 'required'  : '' ?> value="<?php echo esc_attr($current_values[$field['name']]); ?>" />
							<?php
							}
						?>
					</div>
				<?php
			}
		?>
	</div>
	
	<?php
	
}

/**
 * Save Reservation Details
 */
function rb_reservation_save_meta($post_id) {
	$fields = get_reservation_fields();

	foreach ($fields as $field) {
		if (isset($_POST[$field['name']])) {
			update_post_meta($post_id, $field['name'], $_POST[$field['name']]);
		} else {
			delete_post_meta($post_id, $field['name']);
		}
	}
}
add_action( 'save_post', 'rb_reservation_save_meta' );

/**
 * Edit Reservation Columns
 */
function rb_reservation_edit_columns($columns) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => 'Name',
		'reservation_date' => 'Reserved For',
		'table' => 'Table',
		'guests' => 'Guests',
		'phone' => 'Phone',
		'email' => 'Email',
		'date' => 'Date',
	);

	return $columns;
}
add_filter('manage_rb_reservation_posts_columns', 'rb_reservation_edit_columns', 10);

function rb_reservation_custom_column($column_name, $post_id) {
	switch ($column_name) {
		case 'reservation_date':
			$datetime = __( 'Date:', 'restaurant-bookings' ).' <span class="rb_reservation_date">'.get_post_meta( $post_id, 'rb_reservation_date', true ).'</span><br>'.__( 'Time:', 'restaurant-bookings' ).' <span class="rb_reservation_time">'.get_post_meta( $post_id, 'rb_reservation_time', true ).'</span>';
			echo $datetime;
			break;

		case 'table':
			echo '<span class="rb_reservation_table">'.get_post_meta( $post_id, 'rb_reservation_table', true ).'</span>';
			break;

		case 'guests':
			echo '<span class="rb_reservation_guests">'.get_post_meta( $post_id, 'rb_reservation_guests', true ).'</span>';
			break;

		case 'phone':
			echo '<span class="rb_reservation_phone">'.get_post_meta( $post_id, 'rb_reservation_phone', true ).'</span>';
			break;

		case 'email':
			echo '<span class="rb_reservation_email"><a href="mailto:'.get_post_meta( $post_id, 'rb_reservation_email', true ).'">'.get_post_meta( $post_id, 'rb_reservation_email', true ).'</a></span>';
			break;
	}
}
add_action('manage_rb_reservation_posts_custom_column', 'rb_reservation_custom_column', 10, 2);

function rb_reservation_sortable_columns($columns) {
	$columns['reservation_date'] = 'reservation_date';
  return $columns;
}
add_filter( 'manage_edit-rb_reservation_sortable_columns', 'rb_reservation_sortable_columns' );


/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function rb_get_option( $key = '', $option_key = 'rb_options', $default = false ) {
	if ( function_exists( 'cmb2_get_option' ) ) {
		// Use cmb2_get_option as it passes through some key filters.
		return cmb2_get_option( $option_key, $key, $default );
	}
	// Fallback to get_option if CMB2 is not loaded yet.
	$opts = get_option( $option_key, $default );
	$val = $default;
	if ( 'all' == $key ) {
		$val = $opts;
	} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}
	return $val;
}

/** All the fields - used for making loops easier **/
function get_reservation_fields() {
	$fields = array(
		array(
			'name' => 'rb_reservation_date',
			'label' => 'Date',
			'type' => 'date',
			'required' =>  true,
		),
		array(
			'name' => 'rb_reservation_time',
			'label' => 'Time',
			'type' => 'time',
			'required' =>  true,
		),
		array(
			'name' => 'rb_reservation_table',
			'label' => 'Table',
			'type' => 'text',
			'required' =>  true,
		),
		array(
			'name' => 'rb_reservation_guests',
			'label' => 'Number of Guests',
			'type' => 'number',
			'required' =>  true,
		),
		array(
			'name' => 'rb_reservation_name',
			'label' => 'Name',
			'type' => 'text',
			'required' =>  false,
		),
		array(
			'name' => 'rb_reservation_email',
			'label' => 'Email Address',
			'type' => 'email',
			'required' =>  true,
		),
		array(
			'name' => 'rb_reservation_phone',
			'label' => 'Phone Number',
			'type' => 'tel',
			'required' =>  false,
		),
		array(
			'name' => 'rb_reservation_message',
			'label' => 'Message',
			'type' => 'longtext',
			'required' =>  false,
		),
	);

	return $fields;
}

?>