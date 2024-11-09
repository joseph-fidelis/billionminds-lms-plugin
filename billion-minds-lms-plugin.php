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
 * Version:     0.0.1
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
require_once plugin_dir_path( __FILE__ ) . 'includes/generate-pdf.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/active-campaign.php';
