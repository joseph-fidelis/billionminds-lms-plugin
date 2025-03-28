<?php
// Hook to register settings and sections
add_action( 'admin_init', 'billionminds_lms_register_settings' );



function billionminds_lms_register_settings() {
    // ---------------- Main Settings Registration ---------------- //
    register_setting( 'billionminds_lms_settings_group', 'ACTIVECAMPAIGN_API_URL' );
    register_setting( 'billionminds_lms_settings_group', 'ACTIVECAMPAIGN_API_KEY' );
    register_setting( 'billionminds_lms_settings_group', 'ACCREDIBLE_API_URL' );
    register_setting( 'billionminds_lms_settings_group', 'ACCREDIBLE_API_KEY' );
    register_setting( 'billionminds_lms_settings_group', 'BILLIONMIND_BACKEND_URL' );

    // ---------------- Do Exercise Registration ---------------- //
    register_setting( 'billionminds_lms_exercise_group', 'DO_EXERCISE_LIST' );
    
    // ---------------- Main Settings Section ---------------- //
    add_settings_section(
        'billionminds_lms_settings_section',           // ID
        'Settings Section',                            // Title
        'billionminds_lms_settings_section_callback',  // Callback for description
        'billionminds-lms'                             // Page slug for main settings page
    );
    
    // Fields for the main settings section
    add_settings_field(
        'activecampaign_api_url',       // Field ID
        'ActiveCampaign API URL',         // Field Title
        'billionminds_lms_text_field_callback', // Callback to render the field
        'billionminds-lms',             // Page slug
        'billionminds_lms_settings_section',   // Section ID
        array(
            'label_for'   => 'activecampaign_api_url',
            'option_name' => 'ACTIVECAMPAIGN_API_URL'
        )
    );
    add_settings_field(
        'activecampaign_api_key',
        'ActiveCampaign API Key',
        'billionminds_lms_text_field_callback',
        'billionminds-lms',
        'billionminds_lms_settings_section',
        array(
            'label_for'   => 'activecampaign_api_key',
            'option_name' => 'ACTIVECAMPAIGN_API_KEY'
        )
    );
    add_settings_field(
        'accredible_api_url',
        'Accredible API URL',
        'billionminds_lms_text_field_callback',
        'billionminds-lms',
        'billionminds_lms_settings_section',
        array(
            'label_for'   => 'accredible_api_url',
            'option_name' => 'ACCREDIBLE_API_URL'
        )
    );
    add_settings_field(
        'accredible_api_key',
        'Accredible API Key',
        'billionminds_lms_text_field_callback',
        'billionminds-lms',
        'billionminds_lms_settings_section',
        array(
            'label_for'   => 'accredible_api_key',
            'option_name' => 'ACCREDIBLE_API_KEY'
        )
    );
    add_settings_field(
        'billionminds_backend_url',
        'BillionMinds Backend Url',
        'billionminds_lms_text_field_callback',
        'billionminds-lms',
        'billionminds_lms_settings_section',
        array(
            'label_for'   => 'billionminds_backend_url',
            'option_name' => 'BILLIONMIND_BACKEND_URL'
        )
    );

    // ---------------- Do Exercise Section (Standalone Page) ---------------- //
    add_settings_section(
        'billionminds_lms_exercise_section',          // ID
        'Do Exercise Section',                        // Title
        'billionminds_lms_exercise_section_callback', // Callback for description
        'billionminds-lms-exercise'                   // Page slug for exercise page
    );
    
    add_settings_field(
        'do_exercise_list',
        'Select Gravity Forms',
        'billionminds_lms_exercise_field_callback',   // Custom callback for checkbox list
        'billionminds-lms-exercise',
        'billionminds_lms_exercise_section',
        array(
            'label_for'   => 'do_exercise_list',
            'option_name' => 'DO_EXERCISE_LIST'
        )
    );
}

// Callback for the main settings section description
function billionminds_lms_settings_section_callback() {
    echo '<p>Enter your settings information below:</p>';
}

// Callback for the Do Exercise section description on its standalone page
function billionminds_lms_exercise_section_callback() {
    echo '<p>Select the Gravity Forms you want to include in the exercise list:</p>';
}

// Generic callback to render text input fields for main settings
function billionminds_lms_text_field_callback( $args ) {
    $option_name = $args['option_name'];
    $value = get_option( $option_name, '' );
    echo '<input type="text" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $value ) . '" />';
}

// Callback to render the Do Exercise field as a list of checkboxes for all Gravity Forms
function billionminds_lms_exercise_field_callback( $args ) {
    $option_name = $args['option_name'];
    $saved = get_option( $option_name, array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }
    
    // Ensure Gravity Forms is installed and activated
    if ( ! class_exists( 'GFAPI' ) ) {
        echo '<p>Gravity Forms is not installed or activated.</p>';
        return;
    }
    
    // Retrieve all Gravity Forms
    $forms = GFAPI::get_forms();
    if ( is_wp_error( $forms ) ) {
        echo '<p>There was an error retrieving forms.</p>';
        return;
    }
    
    if ( ! empty( $forms ) ) {
        foreach ( $forms as $form ) {
            $form_id    = $form['id'];
            $form_title = $form['title'];
            $checkbox_id = 'do_exercise_form_' . $form_id;
            $checked = in_array( $form_id, $saved ) ? 'checked' : '';
            echo '<label for="' . esc_attr( $checkbox_id ) . '">';
            echo '<input type="checkbox" id="' . esc_attr( $checkbox_id ) . '" name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $form_id ) . '" ' . $checked . ' />';
            echo ' ' . esc_html( $form_title );
            echo '</label><br />';
        }
    } else {
        echo '<p>No Gravity Forms found.</p>';
    }
}


