<?php
// Use this to automate all form submissions 
// add_action( 'gform_after_submission', 'after_submission', 10, 2 );

// Add hook to process submissions for form with id 59
add_action( 'gform_after_submission_59', 'enroll_in_learndash_course', 10, 2 );

function enroll_in_learndash_course( $entry, $form ) {

    do_action( 'qm/debug', "Enter Form" );

    // Directly retrieve specific form values using known field IDs
    $course_id   = rgar( $entry, '8' );   
    $course_name = rgar( $entry, '9' ); 
    $ac_tag      = rgar( $entry, '10' ); 
    $first_name  = rgar( $entry, '1' );  
    $last_name   = rgar( $entry, '3' );  
    $email       = rgar( $entry, '6' ); 
    $password    = rgar( $entry, '11' ); 

    // Create username using firstname and lastname in form 
    $username = $first_name . $last_name ;

    // Register the user in wordpress
    $wp_user_id = create_new_user($username, $password, $email, $first_name, $last_name);
    
    
    enroll_user_in_course($course_id);


    // Log debug information
    do_action( 'qm/debug', "Course ID -> " . $course_id );
    do_action( 'qm/debug', "Course Name -> " . $course_name );
    do_action( 'qm/debug', "Active Campaign Tag -> " . $ac_tag );
    do_action( 'qm/debug', "First Name -> " . $first_name );
    do_action( 'qm/debug', "Last Name -> " . $last_name );
    do_action( 'qm/debug', "Email -> " . $email );



    // Only proceed if required values are set
    if ( $email && $ac_tag && $course_name && $first_name && $last_name ) {
        // Construct the webhook URL with the form values
        $webhook_url = "https://hook.us1.make.com/mt9xqq5nm04p4jgo9kt08ocpdnnjmw0x";
        $webhook_url .= "?email=" . urlencode( $email );
        $webhook_url .= "&tag=" . urlencode( $ac_tag );
        $webhook_url .= "&course_name=" . urlencode( $course_name );
        $webhook_url .= "&first_name=" . urlencode( $first_name );
        $webhook_url .= "&last_name=" . urlencode( $last_name );

        // Send the data to the webhook URL
        $response = wp_remote_get( $webhook_url );

        // Log the response for debugging
        if ( is_wp_error( $response ) ) {
            do_action( 'qm/debug', "Webhook Error: " . $response->get_error_message() );
        } else {
            do_action( 'qm/debug', "Webhook Response: " . wp_remote_retrieve_body( $response ) );
        }
    } else {
        do_action( 'qm/debug', "Missing required fields for webhook." );
    }
}


// Enroll user in learndash 
function enroll_user_in_course( $user_id, $course_id ) {

    // $user_id = get_current_user_id();    

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


function create_new_user($username, $password, $email, $first_name, $last_name) {
    // Define user data
    $userdata = array(
        'user_login'    => $username, 
        'user_pass'     => $password,  
        'user_email'    => $email, 
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'role'          => 'subscriber' // You can set roles like 'administrator', 'editor', 'author', etc.
    );

    // Insert the user and get the user ID
    $user_id = wp_insert_user($userdata);

    // Check for errors
    if (is_wp_error($user_id)) {
        // Return error message if user creation fails
        return $user_id-> get_error_message();
    }

    // Return the new user ID
    return $user_id;
}