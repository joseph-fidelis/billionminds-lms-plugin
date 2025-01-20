<?php

// register_activation_hook( __FILE__, 'create_billionminds_enrollment_table' );
add_action('learndash_init', 'create_billionminds_enrollment_table',99);

function create_billionminds_enrollment_table() {
    do_action( 'qm/debug', "Creating Billion Minds Table" );
    global $wpdb;

    $table_name = $wpdb->prefix . 'custom_table';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        name tinytext NOT NULL,
        text text NOT NULL,
        url varchar(55) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    do_action( 'qm/debug', "Billion Minds Custom Table Created ". $table_name );
}
