<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/* ======= Get the all List of Live Rates  =========== */

function add_new_vehicle_booking()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'vehicle_booking'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'fname' => '',
        'lname'    => '',
        'email' => '',
        'phone' => '',
        'vehicle_type' => '',
        'vehicle' => '',
        'vehicle_price' => '',
        'message' => '',
        'booking_status' => '',
    );

    // here we are verifying does this request is post back and have correct nonce
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) 
    {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update

        if ($item['id'] == 0) 
        {
            $result = $wpdb->insert($table_name, $item);
            $item['id'] = $wpdb->insert_id;

            if ($result) 
            {                     
                $message = __('Vehicle booking was successfully Saved', 'vehicle-booking');
            } 
            else 
            {
                $notice = __('There was an error while saving Vehicle booking', 'vehicle-booking');
            }
        } 
        else 
        {
            $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
            if ($result) 
            {
                $message = __('Vehicle booking was successfully updated', 'vehicle-booking');

                $to = $_POST['email'];
                // $multiple_recipients = array(
                //     get_option( 'admin_email' ),
                //     $_POST['email']
                // );

                if ($_POST['booking_status'] == 'Complete') 
                {
                    $subject =  "Thankyou Email";

                    $message = sprintf(__( "Hello %s!" ), $_POST['fname'] ) . "\r\n";
                    $message .= sprintf(__( "Thankyou for your booking!" ) ) . "\r\n";
                    $message .= __('');
                }
                else 
                {
                    $subject =  "Request For Vehicle Booking";

                    $message = sprintf(__( "Hello %s!" ), $_POST['fname'] ) . "\r\n";
                    $message .= sprintf(__( "Your Booking Status is: %s!" ), $_POST['booking_status'] ) . "\r\n";
                    $message .= __('');
                }
                
                // $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($to, $subject, $message);
            } 
            else 
            {
                $notice = __('There was an error while updating Vehicle booking', 'vehicle-booking');
            }
        }
    }
    else 
    {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['booking_id'])) 
        {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['booking_id']), ARRAY_A);
            if (!$item) 
            {
                $item = $default;
                $notice = __('Vehicle booking not found', 'vehicle-booking');
            }
        }
    }

    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <?php
        if (isset($_REQUEST['booking_id'])) 
        {
            ?>
            <h1><?php esc_html_e('Update Vehicle Booking', 'vehicle-booking')?></h1>
            <?php
        }
        else
        {
            ?>
            <h1><?php esc_html_e('Add Vehicle Booking', 'vehicle-booking')?></h1>
            <?php
        }
        ?>

        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

            <div id="post-body">
                <div id="post-body-content">
                    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
                        <tbody>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="booking_status"><?php _e('Booking Status:'); ?></label>
                                </th>
                                <td>
                                    <?php $booking_status = esc_attr($item['booking_status']); ?>
                                    <select name="booking_status">
                                        <option value="Pending" <?php if("Pending" == $booking_status){ echo 'selected="selected"';} ?>>Pending</option>
                                        <option value="Approved" <?php if("Approved" == $booking_status){ echo 'selected="selected"';} ?>>Approved</option>
                                        <option value="Reject" <?php if("Reject" == $booking_status){ echo 'selected="selected"';} ?>>Reject</option>
                                        <option value="On the way" <?php if("On the way" == $booking_status){ echo 'selected="selected"';} ?>>On the way</option>
                                        <option value="Complete" <?php if("Complete" == $booking_status){ echo 'selected="selected"';} ?>>Complete</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="fname"><?php _e('First name:'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="fname" id="fname" style="width: 50%" value="<?php echo esc_attr($item['fname']);?>" />
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="lname"><?php _e('Last name:', 'custom-login-register'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="lname" id="lname" style="width: 50%" value="<?php echo esc_attr($item['lname']);?>"  />
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="email"><?php _e('Email:', 'custom-login-register'); ?></label>
                                </th>
                                <td>
                                    <input type="email" name="email" id="email" style="width: 50%" value="<?php echo esc_attr($item['email']);?>"  />
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="phone"><?php _e('Phone:', 'custom-login-register'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="phone" id="phone" style="width: 50%" value="<?php echo esc_attr($item['phone']);?>"  />
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="vehicle_type"><?php _e('Select vehicle type:', 'custom-login-register'); ?></label>
                                </th>
                                <td>
                                    <?php
                                    $vehicle_type = esc_attr($item['vehicle_type']);
                                    $terms = get_terms('vehicles_taxonomy');
                                    if ( !empty( $terms ) && !is_wp_error( $terms ) ){
                                        echo "<select name='vehicle_type' id='vehicle_type'>";
                                        foreach ( $terms as $term ) 
                                        {
                                            ?>
                                            <option value='<?php echo $term->name; ?>' <?php if($term->name == $vehicle_type){ echo 'selected="selected"';} ?>><?php echo $term->name; ?></option>
                                            <?php
                                        }
                                        echo "</select>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="vehicle"><?php _e('Select vehicle:', 'custom-login-register'); ?></label>
                                </th>
                                <td>
                                    <?php
                                    $vehicle = esc_attr($item['vehicle']);

                                    $posts = get_posts(array('post_type'=> 'vehicles', 'post_status'=> 'publish', 'posts_per_page'=>-1,'order' => 'ASC',));

                                    echo '<select name="vehicle" id="vehicle">';
                                    foreach ($posts as $post) 
                                    {
                                        ?>
                                        <option value='<?php echo $post->ID; ?>' <?php if($post->ID == $vehicle){ echo 'selected="selected"';} ?>><?php echo $post->post_title; ?></option>
                                        <?php
                                    }
                                    echo '</select>';

                                   ?>
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="vehicle_price"><?php _e('vehicle price:', 'custom-login-register'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="vehicle_price" id="vehicle_price" style="width: 50%" value="<?php echo esc_attr($item['vehicle_price']);?>"  />
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th valign="top" scope="row">
                                    <label for="message"><?php _e('Message:', 'custom-login-register'); ?></label>
                                </th>
                                <td>
                                    <textarea name="message" class="message" id="message"><?php echo esc_attr($item['message']);?></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="submit" value="<?php esc_html_e('Add Booking', 'vehicle-booking')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </form>
    </div>
    <?php
}
?>