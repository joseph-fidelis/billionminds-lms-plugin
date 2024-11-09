<?php
// Hook to admin init
add_action( 'admin_init', 'billionminds_lms_register_settings' );

// Function to register settings
function billionminds_lms_register_settings() {
    // Register MongoDB URL field
    register_setting( 'billionminds_lms_settings_group', 'billionminds_mongodb_url' );
    register_setting( 'billionminds_lms_settings_group', 'billionminds_s3_url' );

    // Add settings section
    add_settings_section(
        'billionminds_lms_section',          // Section ID
        'Connection Settings',                  // Section title
        'billionminds_lms_section_callback', // Section callback function
        'billionminds-lms'                   // Menu slug
    );

    // Add MongoDB URL field
    add_settings_field(
        'billionminds_mongodb_url',          // Field ID
        'MongoDB URL',                       // Field label
        'billionminds_lms_mongodb_url_field',// Field callback function
        'billionminds-lms',                  // Menu slug
        'billionminds_lms_section'           // Section ID
    );

    add_settings_field(
        'billionminds_s3_url',          // Field ID
        'Storage URL',                       // Field label
        'billionminds_lms_s3_url_field',// Field callback function
        'billionminds-lms',                  // Menu slug
        'billionminds_lms_section'           // Section ID
    );
}

// Section description callback
function billionminds_lms_section_callback() {
    echo '<p>Enter your connection details below:</p>';
}

// Field callback to display the MongoDB URL input
function billionminds_lms_mongodb_url_field() {
    $mongodb_url = get_option( 'billionminds_mongodb_url', '' );
    echo '<input type="text" name="billionminds_mongodb_url" value="' . esc_attr( $mongodb_url ) . '" class="regular-text">';
}

function billionminds_lms_s3_url_field() {
    $s3_url = get_option( 'billionminds_s3_url', '' );
    echo '<input type="text" name="billionminds_s3_url" value="' . esc_attr( $s3_url ) . '" class="regular-text">';
}
