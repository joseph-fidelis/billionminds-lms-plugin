<?php
// Add hook to process submissions for form with id 59 change later 
add_action( 'gform_after_submission_1000', 'enroll_in_learndash_course', 10, 2 );

// Add filter to enable password field
add_filter( 'gform_enable_password_field', '__return_true' );

function enroll_in_learndash_course( $entry, $form ) {

    do_action( 'qm/debug', "Enter Form" );

    // Directly retrieve specific form values using known field IDs
    $course_id   = rgar( $entry, '8' );   
    $course_name = rgar( $entry, '9' ); 
    $ac_tag      = rgar( $entry, '10' ); 
    $first_name  = rgar( $entry, '1' );  
    $last_name   = rgar( $entry, '3' );  
    $email       = rgar( $entry, '6' ); 
    $password    = rgar( $entry, '14' ); 

    // Create username using firstname and lastname in form 
    $username = $first_name . $last_name ;

    // Set userdata
    $userdata = array(
        'user_login'    => $username, 
        'user_pass'     => $password,  
        'user_email'    => $email, 
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'role'          => 'subscriber' // You can set roles like 'administrator', 'editor', 'author', etc.
    );

    // Register the user in wordpress
    $wp_user_id = wp_insert_user($userdata);

    if (is_wp_error($wp_user_id)) {
        do_action( 'qm/debug', "Error creating wordpress user" );
    }else{
        // enroll user in coure
        enroll_user_in_course($wp_user_id,$course_id);
    }
    
    

    // Only proceed if required values are set
    if ( $email && $ac_tag && $course_name && $first_name && $last_name && $password ) {
        // Construct the webhook URL with the form values
        $webhook_url = "https://hook.us1.make.com/mt9xqq5nm04p4jgo9kt08ocpdnnjmw0x";
        $webhook_url .= "?email=" . urlencode( $email );
        $webhook_url .= "&tag=" . urlencode( $ac_tag );
        $webhook_url .= "&course_name=" . urlencode( $course_name );
        $webhook_url .= "&first_name=" . urlencode( $first_name );
        $webhook_url .= "&last_name=" . urlencode( $last_name );
        $webhook_url .= "&password=" . urlencode( $password );


        // Send the data to the webhook URL
        $response = wp_remote_get( $webhook_url );

        // Log the response for debugging
        if ( is_wp_error( $response ) ) {
            do_action( 'qm/debug', "Webhook Error: " . $response->get_error_message() );
        }

    } else {
        do_action( 'qm/debug', "Missing required fields for webhook." );
    }
}


// Enroll user in learndash 
function enroll_user_in_course( $user_id, $course_id ) {


    // Ensure both $user_id and $course_id are valid
    if ( ! $user_id || ! $course_id ) {
        do_action( 'qm/debug', "Invalid user ID or course ID." );
        return false;
    }
    
    if ( ! function_exists( 'ld_update_course_access' ) ) {
        // LearnDash function not available
        do_action( 'qm/debug', "LearnDash function not available" );
        return false;
    }

    // Enroll the user in the course
    ld_update_course_access( $user_id, $course_id, $remove = false );

    // Log for debugging
    do_action( 'qm/debug', "User ID {$user_id} enrolled in Course ID {$course_id}" );
        
    return true;
}
