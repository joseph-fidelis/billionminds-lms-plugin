<?php
/**
 * Billionminds Extension Plugin for LearnDash and GravityPDF.
 *
 * @package BillionmindsLMS
 *
 * @wordpress-plugin
 * Plugin Name: BillionMinds LMS  
 * Plugin URI:  https://billionminds.com
 * Description: Custom Plugin for billionminds.
 * Version:     0.0.8
 * Author:      Fidelis Uwem Joseph 
 * Author URI:  https://github.com/joseph-fidelis
 * License:     GPL v3
 * Text Domain: billionminds-lms
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include other files
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-menu.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/journal.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/accreditation.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/active-campaign-endpoints.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/learndash-endpoints.php';