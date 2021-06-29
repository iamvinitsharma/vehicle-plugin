<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/* ======= Register The Plugin Shortcode =========== */

function vehicle_booking_shortcode($atts) 
{
    ?>
    <form method="post" class="vehicle-booking-form" id="vehicle_booking_form">
        <p>
            <label for="fname"><?php _e('First name:'); ?></label>
            <input type="text" name="fname" id="fname" />
        </p>
        <p>
            <label for="lname"><?php _e('Last name:', 'custom-login-register'); ?></label>
            <input type="text" name="lname" id="lname" />
        </p>
        <p>
            <label for="email"><?php _e('Email:', 'custom-login-register'); ?></label>
            <input type="email" name="email" id="email" />
        </p>
        <p>
            <label for="phone"><?php _e('Phone:', 'custom-login-register'); ?></label>
            <input type="text" name="phone" id="phone" />
        </p>
        <p>
            <label for="vehicle_type"><?php _e('Select vehicle type:', 'custom-login-register'); ?></label>
            <?php
            $terms = get_terms('vehicles_taxonomy');
            if ( !empty( $terms ) && !is_wp_error( $terms ) ){
                echo "<select name='vehicle_type' id='vehicle_type'>";
                foreach ( $terms as $term ) 
                {
                    echo "<option value='" . $term->name . "'>" . $term->name . "</option>";
                }
                echo "</select>";
            }
            ?>
        </p>
        <p>
            <label for="vehicle"><?php _e('Select vehicle:', 'custom-login-register'); ?></label>
            <?php

            $posts = get_posts(array('post_type'=> 'vehicles', 'post_status'=> 'publish', 'posts_per_page'=>-1,'order' => 'ASC',));

            echo '<select name="vehicle" id="vehicle">';
            foreach ($posts as $post) 
            {
                $starting_price_per_day = get_post_meta( $post->ID, 'starting_price_per_day', true  );
                echo '<option value="'. $post->ID. '" data-price="'. $starting_price_per_day. '">'. $post->post_title. '</option>';
            }
            echo '</select>';

           ?>
        </p>
        <p>
            <label for="vehicle_price"><?php _e('vehicle price:', 'custom-login-register'); ?></label>
            <input type="text" name="vehicle_price" id="vehicle_price" readonly="" />
        </p>
        <p>
            <label for="message"><?php _e('Message:', 'custom-login-register'); ?></label>
            <textarea name="message" class="message" id="message"></textarea>
        </p>
        <p>
            <input type="hidden" name="custom_vehicle_booking_nonce" id="custom_vehicle_booking_nonce" value="<?php echo wp_create_nonce('vehicle_booking-nonce'); ?>" />
            <input type="hidden" name="custom_redirection" id="custom_redirection" value="<?php echo esc_url( $custom_redirect ); ?>"/>
            <input type="submit" name="book_vehicle" id="book_vehicle" value="<?php _e('Submit', 'custom-login-register'); ?>" />    
        </p>
    </form>
    <?php
}
add_shortcode( 'vehicle_booking', 'vehicle_booking_shortcode');


// save_vehicle_booking data
function save_vehicle_booking() 
{
    if (isset( $_POST["fname"] ) && wp_verify_nonce($_POST['custom_vehicle_booking_nonce'], 'vehicle_booking-nonce')) 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vehicle_booking';
        $data = array(
            'fname' => $_POST['fname'],
            'lname'    => $_POST['lname'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'vehicle_type' => $_POST['vehicle_type'],
            'vehicle' => $_POST['vehicle'],
            'vehicle_price' => $_POST['vehicle_price'],
            'message' => $_POST['message'],
            'booking_status' => 'Pending',
        );

        $success = $wpdb->insert( $table_name, $data );
        if($success)
        {
            $vehicle = get_the_title( $_POST['vehicle'] );

            // Now we are ready to build our welcome email
            $to = get_option( 'admin_email' );
            $multiple_recipients = array(
                get_option( 'admin_email' ),
                $_POST['email']
            );
            
            $subject =  "Request For Vehicle Booking";

            $message = sprintf(__( "First Name: %s" ), $_POST['fname'] ) . "\r\n";
            $message .= sprintf(__( "Last name: %s" ), $_POST['lname'] ) . "\r\n";
            $message .= sprintf(__( "Email: %s" ), $_POST['email'] ) . "\r\n";
            $message .= sprintf(__( "Phone: %s" ), $_POST['phone'] ) . "\r\n";
            $message .= sprintf(__( "vehicle: type %s" ), $_POST['vehicle_type'] ) . "\r\n";
            $message .= sprintf(__( "vehicles: %s" ), $vehicle ) . "\r\n";
            $message .= sprintf(__( "Price: %s" ), $_POST['vehicle_price'] ) . "\r\n";
            $message .= sprintf(__( "Message: %s" ), $_POST['message'] ) . "\r\n";
            $message .= sprintf(__( "Booking Status: %s" ), 'Pending' ) . "\r\n";
            $message .= __('');

            // $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($multiple_recipients, $subject, $message);

            // // send the newly created user to the home page after logging them in
            wp_redirect( site_url() ); 
            exit;
        }
    }
}
add_action('init', 'save_vehicle_booking');
?>