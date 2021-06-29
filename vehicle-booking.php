<?php
/**
 * Plugin Name: Vehicle Booking
 * Plugin URI: h
 * Description: This is a custom plugin
 * Version: 1.0.0
 * Author:      vinit
 * Author URI:  
 * Text Domain: vehicle-booking
 * Domain Path: /languages
 * License:     GPLv2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


//Load the vehicle-booking-list
if ( file_exists( dirname(__FILE__) . '/vehicle-booking-list.php' ) ) 
{
  require_once dirname(__FILE__) . '/vehicle-booking-list.php';
} 
//Load the add new vehicle-booking
if ( file_exists( dirname(__FILE__) . '/add-vehicle-booking.php' ) ) 
{
  require_once dirname(__FILE__) . '/add-vehicle-booking.php';
}  
// Load the Shortcodes
if ( file_exists( dirname( __FILE__ ) . '/vehicle-booking-shortcodes.php' ) ) 
{
  require_once dirname(__FILE__) . '/vehicle-booking-shortcodes.php';
}

/*==================================================================================================
===================== Register Plugin Css/js
==================================================================================================*/
function vehicle_booking_scripts() 
{
    wp_enqueue_style( 'plugin-style', plugin_dir_url(__FILE__) . 'css/plugin.css' );

    wp_enqueue_script( 'plugin_script', plugin_dir_url(__FILE__) . 'js/scripts.js',  array(), '', true );
    wp_localize_script( 'plugin_script', 'AjaxUrl', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'vehicle_booking_scripts' );

function admin_plugin_scripts() 
{
    wp_enqueue_script( 'my_script', plugin_dir_url ( __FILE__ ) . '/js/admin-scripts.js', array( 'jquery' ), '1.0.0', true );
    wp_localize_script( 'my_script', 'AjaxUrl', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts', 'admin_plugin_scripts' );

/*==================================================================================================
===================== Register the DL Timer Menu
==================================================================================================*/
function vehicle_booking_menu_register()
{
    add_menu_page( __('Vehicles Booking','vehicle-booking'),       //Page Title
                  __('Vehicles Booking','vehicle-booking'),        //Menu Title
                  'manage_options', 
                  'vehicle-bookings',               //Menu Slug
                  'vehicle_bookings_list',           //Menu Page
                  'dashicons-admin-plugins', //Menu Icon
                  6
    );

    add_submenu_page( 'vehicle-bookings',            //Parent Menu Slug
                      __('Vehicles Booking','vehicle-booking'),                //Page Title
                      __('Vehicles Booking','vehicle-booking'),                //SubMenu Title
                      'manage_options', 
                      'vehicle-bookings',       //SubMenu Slug
                      'vehicle_bookings_list'   //SubMenu Page
    );

    add_submenu_page( 'vehicle-bookings',            //Parent Menu Slug
                      __('Add New Bookings','vehicle-booking'),                //Page Title
                      __('Add New Bookings','vehicle-booking'),              //SubMenu Title
                      'manage_options', 
                      'add-new-vehicle-booking',       //SubMenu Slug
                      'add_new_vehicle_booking'   //SubMenu Page
    );
}
add_action( 'admin_menu', 'vehicle_booking_menu_register' );





/* ==========================================================================
================== List
========================================================================== */
add_action( 'wp_ajax_get_vehicle_list', 'get_vehicle_list' );
function get_vehicle_list()
{
    $vehicle_type = $_REQUEST['vehicle_type'];

    $args = array( 
        'post_type' => 'vehicles', 
        'posts_per_page' => -1,
        'order' => 'ASC',
        'tax_query' => array(
          array(
            'taxonomy' => 'vehicles_taxonomy',
            'field'    => 'name',
            'terms'    => $vehicle_type,
        ),
      ),
    ); 

    $posts = get_posts( $args );

    foreach ($posts as $post) 
    {
        echo '<option value="'. $post->ID. '">'. $post->post_title. '</option>';
    }
   die();
}
/* ==========================================================================
================== 
========================================================================== */
add_action( 'wp_ajax_get_vehicle_price', 'get_vehicle_price' );
function get_vehicle_price()
{
    $vehicle_id = $_REQUEST['vehicle'];

    $starting_price_per_day = get_post_meta( $vehicle_id, 'starting_price_per_day', true  );
    // print_r(get_post_meta( $vehicle_id, 'starting_price_per_day', true  ));

    // echo $vehicle_id;
    echo $starting_price_per_day;

    die();
}
/*==================================================================================================
===================== Creating a function to create our Vehicles CPT
==================================================================================================*/
function custom_post_type() 
{
    $labels = array(
        'name'                  => _x( 'Vehicles', 'Post Type General Name', 'vehicle-booking' ),
        'singular_name'         => _x( 'Vehicle', 'Post Type Singular Name', 'vehicle-booking' ),
        'menu_name'             => __( 'Vehicles', 'vehicle-booking' ),
        'name_admin_bar'        => __( 'Vehicles', 'vehicle-booking' ),
        'archives'              => __( 'Vehicles Archives', 'vehicle-booking' ),
        'attributes'            => __( 'Vehicles Attributes', 'vehicle-booking' ),
        'parent_item_colon'     => __( 'Parent Vehicles:', 'vehicle-booking' ),
        'all_items'             => __( 'All Vehicles', 'vehicle-booking' ),
        'add_new_item'          => __( 'Add New Vehicle', 'vehicle-booking' ),
        'add_new'               => __( 'Add New Vehicle', 'vehicle-booking' ),
        'new_item'              => __( 'New Vehicle', 'vehicle-booking' ),
        'edit_item'             => __( 'Edit Vehicle', 'vehicle-booking' ),
        'update_item'           => __( 'Update Vehicle', 'vehicle-booking' ),
        'view_item'             => __( 'View Vehicle', 'vehicle-booking' ),
        'view_items'            => __( 'View Vehicles', 'vehicle-booking' ),
        'search_items'          => __( 'Search Vehicle', 'vehicle-booking' ),
        'not_found'             => __( 'Not found', 'vehicle-booking' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'vehicle-booking' ),
        'featured_image'        => __( 'Featured Image', 'vehicle-booking' ),
        'set_featured_image'    => __( 'Set featured image', 'vehicle-booking' ),
        'remove_featured_image' => __( 'Remove featured image', 'vehicle-booking' ),
        'use_featured_image'    => __( 'Use as featured image', 'vehicle-booking' ),
        'insert_into_item'      => __( 'Insert into Vehicle', 'vehicle-booking' ),
        'uploaded_to_this_item' => __( 'Uploaded to this Vehicle', 'vehicle-booking' ),
        'items_list'            => __( 'Vehicles list', 'vehicle-booking' ),
        'items_list_navigation' => __( 'Vehicles list navigation', 'vehicle-booking' ),
        'filter_items_list'     => __( 'Filter Vehicles list', 'vehicle-booking' ),
    );
    $args = array(
        'label'                 => __( 'Vehicle', 'vehicle-booking' ),
        'description'           => __( 'Post Type Description', 'vehicle-booking' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'taxonomies'            => array( 'vehicles_taxonomy', 'post_tag' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-buddicons-activity',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
    );
    register_post_type( 'vehicles', $args );
}
add_action( 'init', 'custom_post_type', 0 );

/*==================================================================================================
===================== Register Custom Vehicles Taxonomy
==================================================================================================*/
function vehicles_taxonomy() 
{
    $labels = array(
        'name'                       => _x( 'Vehicles Categories', 'Taxonomy General Name', 'vehicle-booking' ),
        'singular_name'              => _x( 'Vehicles Category', 'Taxonomy Singular Name', 'vehicle-booking' ),
        'menu_name'                  => __( 'Vehicles Categories', 'vehicle-booking' ),
        'all_items'                  => __( 'All Items', 'vehicle-booking' ),
        'parent_item'                => __( 'Parent Item', 'vehicle-booking' ),
        'parent_item_colon'          => __( 'Parent Item:', 'vehicle-booking' ),
        'new_item_name'              => __( 'New Item Name', 'vehicle-booking' ),
        'add_new_item'               => __( 'Add New Item', 'vehicle-booking' ),
        'edit_item'                  => __( 'Edit Item', 'vehicle-booking' ),
        'update_item'                => __( 'Update Item', 'vehicle-booking' ),
        'view_item'                  => __( 'View Item', 'vehicle-booking' ),
        'separate_items_with_commas' => __( 'Separate items with commas', 'vehicle-booking' ),
        'add_or_remove_items'        => __( 'Add or remove items', 'vehicle-booking' ),
        'choose_from_most_used'      => __( 'Choose from the most used', 'vehicle-booking' ),
        'popular_items'              => __( 'Popular Items', 'vehicle-booking' ),
        'search_items'               => __( 'Search Items', 'vehicle-booking' ),
        'not_found'                  => __( 'Not Found', 'vehicle-booking' ),
        'no_terms'                   => __( 'No items', 'vehicle-booking' ),
        'items_list'                 => __( 'Items list', 'vehicle-booking' ),
        'items_list_navigation'      => __( 'Items list navigation', 'vehicle-booking' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy( 'vehicles_taxonomy', array( 'vehicles' ), $args );
}
add_action( 'init', 'vehicles_taxonomy', 0 );


/*==================================================================================================
===================== Adds a metabox to the right side of the screen above the Publish box
==================================================================================================*/
add_action( 'add_meta_boxes_vehicles', 'meta_box_for_vehicles' );
function meta_box_for_vehicles( $post )
{
    add_meta_box( 
        'vehicles_meta_box_id', 
        __( 'starting price per day', 'vehicle-booking' ), 
        'vehicles_meta_box_html_output', 
        'vehicles', 
        'side', 
        'high' 
    );
}
/**
 * Output the HTML for the metabox.
 */
function vehicles_meta_box_html_output( $post ) 
{
    wp_nonce_field( basename( __FILE__ ), 'vehicles_meta_box_nonce' ); //used later for security

    // Get the location data if it's already been entered
    $starting_price_per_day = get_post_meta( $post->ID, 'starting_price_per_day', true );

    echo '<p><input type="text" name="starting_price_per_day" value="'.$starting_price_per_day.'" class="widefat" /></p>';
}
/**
 * Save the metabox data
 */
add_action( 'save_post', 'vehicles_save_meta_boxes_data', 10, 2 );
function vehicles_save_meta_boxes_data( $post_id ){

    // check for correct user capabilities - stop internal xss from customers
    if ( ! current_user_can( 'edit_post', $post_id ) )
    {
        return;
    }

    // check for nonce to top xss
    if ( !isset( $_POST['vehicles_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['vehicles_meta_box_nonce'], basename( __FILE__ ) ) ){
        return;
    }

    // update fields
    if ( isset( $_REQUEST['starting_price_per_day'] ) ) 
    {
        update_post_meta( $post_id, 'starting_price_per_day', sanitize_text_field( $_POST['starting_price_per_day'] ) );
    }
}



/*==================================================================================================
===================== Create The DataBase On Plugin Activation
==================================================================================================*/
global $vehicle_booking_db_version;
$vehicle_booking_db_version = '1.1'; // version changed from 1.0 to 1.1

function vehicle_booking_options_install()
{
    global $wpdb;
    global $vehicle_booking_db_version;

    $table_name = $wpdb->prefix . 'vehicle_booking'; // do not forget about tables prefix
    $sql = "CREATE TABLE " . $table_name . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        fname varchar(100) NOT NULL,
        lname varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(100) NOT NULL,
        vehicle_type varchar(100) NOT NULL,
        vehicle varchar(100) NOT NULL,
        vehicle_price varchar(100) NOT NULL,
        message text NOT NULL,
        booking_status varchar(100) NOT NULL,
        date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    );";

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for later use (on upgrade)
    add_option('vehicle_booking_db_version', $vehicle_booking_db_version);

    /**
     * [OPTIONAL] Example of updating to 1.1 version
     *
     * If you develop new version of plugin just increment $vehicle_booking_db_version variable and add following block of code
     * must be repeated for each new version
     * in version 1.1 we change email field
     * to contain 200 chars rather 100 in version 1.0
     * and again we are not executing sql
     * we are using dbDelta to migrate table changes
     */
    $installed_ver = get_option('vehicle_booking_db_version');
    if ($installed_ver != $vehicle_booking_db_version) 
    {
        $sql = "CREATE TABLE " . $table_name . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        fname varchar(100) NOT NULL,
        lname varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(100) NOT NULL,
        vehicle_type varchar(100) NOT NULL,
        vehicle varchar(100) NOT NULL,
        vehicle_price varchar(100) NOT NULL,
        message text NOT NULL,
        booking_status varchar(100) NOT NULL,
        date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // notice that we are updating option, rather than adding it
        update_option('vehicle_booking_db_version', $vehicle_booking_db_version);
    }

}
register_activation_hook(__FILE__, 'vehicle_booking_options_install');

/**
 * Trick to update plugin database
 */
function vehicle_booking_update_db_check()
{
    global $vehicle_booking_db_version;
    if (get_site_option('vehicle_booking_db_version') != $vehicle_booking_db_version) 
    {
        vehicle_booking_options_install();
    }
}
add_action('plugins_loaded', 'vehicle_booking_update_db_check');

/**
 * delete The DataBase On Plugin deactivation
 */
register_deactivation_hook( __FILE__, 'vehicle_booking_remove_database' );
function vehicle_booking_remove_database() 
{
     global $wpdb;
     $table_name = $wpdb->prefix . 'vehicle_booking';
     $sql = "DROP TABLE IF EXISTS $table_name";
     $wpdb->query($sql);
     delete_option("my_plugin_db_version");
}
?>