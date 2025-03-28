<?php
// Hook to add admin menu
add_action( 'admin_menu', 'billionminds_lms_add_admin_menu' );

// Function to add the admin menu page
function billionminds_lms_add_admin_menu() {
    add_menu_page(
        'BillionMinds Settings',     // Page title
        'BillionMinds LMS',              // Menu title
        'manage_options',                // Capability
        'billionminds-lms',              // Menu slug
        'billionminds_lms_settings_page' // Callback function
    );

    add_menu_page(
        'DO Exercise',                // Page title
        'DO Exercise',                // Menu title
        'manage_options',             // Capability
        'billionminds-lms-exercise',  // Menu slug
        'billionminds_lms_exercise_page' // Callback function
    );
}

// Function to display settings page content
function billionminds_lms_settings_page() {
    ?>
    <div class="wrap">
        <h1>BillionMinds LMS</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'billionminds_lms_settings_group' );
            do_settings_sections( 'billionminds-lms' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function billionminds_lms_exercise_page() {
    ?>
    <div class="wrap">
        <h1>DO Exercise</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'billionminds_lms_exercise_group' );
            do_settings_sections( 'billionminds-lms-exercise' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
