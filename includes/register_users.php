<?php

// Add hook to process submissions for form with id 59
add_action( 'gform_after_submission_59', 'register_new_user_learn_dash', 10, 2 );



function register_new_user_learn_dash( $entry, $form ) {

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
        'role'          => 'subscriber' 
    );

    // Register the user in wordpress
    $wp_user_id = wp_insert_user($userdata);

    if (is_wp_error($wp_user_id)) {
    
        do_action( 'qm/debug', "Error creating wordpress user" );
    
    }else{
        
        do_action( 'qm/debug', "Registered successfully" );
    }   

    

// Only proceed if required values are set
if ( $email && $ac_tag && $course_name && $first_name && $last_name && $password ) {

    
    $enrollment_date = calculate_next_monday();

    // Construct the webhook URL with the form values
    $webhook_url = "https://hook.us1.make.com/glkpa7x5tf27y2zc1iq8f4467nipgs7p";
    $webhook_url .= "?email=" . urlencode( $email );
    $webhook_url .= "&tag=" . urlencode( $ac_tag );
    $webhook_url .= "&course_name=" . urlencode( $course_name );
    $webhook_url .= "&first_name=" . urlencode( $first_name );
    $webhook_url .= "&last_name=" . urlencode( $last_name );
    $webhook_url .= "&password=" . urlencode( $password );
    $webhook_url .= "&user_id=" . urlencode( $wp_user_id );
    $webhook_url .= "&course_id=" . urlencode( $course_id );
    $webhook_url .= "&enrollment_date=" . urlencode( $enrollment_date );


    // Send the data to the webhook URL
    $response = wp_remote_get( $webhook_url );

    // Log the response for debugging
    if ( is_wp_error( $response ) ) {
        do_action( 'qm/debug', "Webhook Error: " . $response->get_error_message() );
    }

 } else { do_action( 'qm/debug', "Missing required fields for webhook." ); }

}


// Helper function to calculate the next Tuesday
function calculate_next_tuesday() {
    $today = new DateTime(); // Current date
    $today_day_of_week = $today->format('N'); // Numeric day of the week (1 = Monday, 7 = Sunday)

    // Calculate the number of days to the next Tuesday
    $days_to_next_tuesday = ($today_day_of_week <= 2) 
        ? (2 - $today_day_of_week) + 7 // If today is Monday (1) or Tuesday (2), add 7
        : 9 - $today_day_of_week;      // For other days, calculate the difference to next Tuesday

    // Add the days to today to get the next Tuesday
    $next_tuesday = $today->modify("+$days_to_next_tuesday days");

    return $next_tuesday->format('Y-m-d'); // Return the date in MySQL format
}

function calculate_next_monday() {
    $today = new DateTime(); // Current date
    $today_day_of_week = $today->format('N'); // Numeric day of the week (1 = Monday, 7 = Sunday)

    // Calculate the number of days to the next Monday
    $days_to_next_monday = ($today_day_of_week <= 1) 
        ? (1 - $today_day_of_week) + 7 // If today is Monday (1), add 7
        : 8 - $today_day_of_week;      // For other days, calculate the difference to next Monday

    // Add the days to today to get the next Monday
    $next_monday = $today->modify("+$days_to_next_monday days");

    return $next_monday->format('Y-m-d'); 
}
