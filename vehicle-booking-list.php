<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Defining Custom Table List
 * ============================================================================
 *
 * In this part you are going to define custom table list class,
 * that will display your database records in nice looking table
 *
 */

if (!class_exists('WP_List_Table')) 
{
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * vehicle_bookings_List_Table class that will display our custom table
 * records in nice table
 */
class vehicle_bookings_List_Table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'vehicle-booking', //singular name of the listed records
            'plural' => 'vehicle-bookings', //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     */
    function column_default($item, $column_name)
    {
        switch($column_name)
        {
            case 'fname':
            case 'lname':
            case 'email':
            case 'phone':
            case 'vehicle_type':
            case 'vehicle':
            case 'vehicle_price':
            case 'booking_status':
            case 'date':
            return $item[$column_name];
            default:
            return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     * method name must be like this: "column_[column_name]"
     */
    function column_date($item)
    {
        return '<em>' . $item['date'] . '</em>'; 
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     */
    function column_vehicle($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &softechzone=2
        echo get_the_title( $item['vehicle'] );
    }

    function column_fname($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &softechzone=2
        $actions = array(
            'edit' => sprintf('<a href="?page=add-new-vehicle-booking&booking_id=%s">%s</a>', $item['id'], __('Edit', 'vehicle-booking')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'vehicle-booking')),
        );

        return sprintf('%s %s',
            $item['fname'],
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'fname' => __('First Name', 'vehicle-booking'),
            'lname' => __('Last Name', 'vehicle-booking'),
            'email' => __('Enail', 'vehicle-booking'),
            'phone' => __('Phone', 'vehicle-booking'),
            'vehicle_type' => __('Vehicle Type', 'vehicle-booking'),
            'vehicle' => __('Vehicle', 'vehicle-booking'),
            'vehicle_price' => __('Vehicle Price', 'vehicle-booking'),
            'booking_status' => __('Booking Status', 'vehicle-booking'),
            'date' => __('Booking Date', 'vehicle-booking'),
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'fname' => array('fname', true),
            'lname' => array('lname', true),
            'email' => array('email', true),
            'phone' => array('phone', true),
            'vehicle_type' => array('vehicle_type', true),
            'vehicle' => array('vehicle', true),
            'vehicle_price' => array('vehicle_price', true),
            'booking_status' => array('booking_status', true),
            'date' => array('date', true),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vehicle_booking'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) 
        {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) 
            {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vehicle_booking'; // do not forget about tables prefix
        $per_page = $this->get_items_per_page( 'vehicle_bookings_per_page' ); // constant, how much records will be shown per page
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'fname';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}


/**
 * Set screen option for List page
*/

function vehicle_bookings_screen_option_list() 
{
    global $vehicle_bookings_screen_hook;
    $screen = get_current_screen();
 
    // get out of here if we are not on our settings page
    if(!is_object($screen) || $screen->id != $vehicle_bookings_screen_hook)
        return;
 
    $args = array(
        'label' => __('Booking per page', 'vehicle-booking'),
        'default' => 10,
        'option' => 'vehicle_bookings_per_page'
    );
    add_screen_option( 'per_page', $args );
}

function vehicle_bookings_set_screen_option($status, $option, $value) 
{
    if ( 'vehicle_bookings_per_page' == $option ) return $value;
}
add_filter('set-screen-option', 'vehicle_bookings_set_screen_option', 10, 3);

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features as you want.
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function vehicle_bookings_list()
{
    global $wpdb;

    $table = new vehicle_bookings_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(esc_html__('Items deleted: %d', 'vehicle-booking'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
    <div class="wrap">

        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h1><?php esc_html_e('Vehicle Bookings', 'vehicle-booking')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=add-new-vehicle-booking');?>"><?php _e('Add new', 'vehicle-booking')?></a>
        </h1>
        <?php echo $message; ?>

        <form id="vehicle-booking-table" method="GET">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
            <?php $table->display() ?>
        </form>

    </div>
    <?php
}
?>