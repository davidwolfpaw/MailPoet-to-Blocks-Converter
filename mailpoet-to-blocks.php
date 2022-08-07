<?php
/**
 * Plugin Name: MailPoet to Blocks Converter
 * Plugin URI: https://davidwolfpaw.com/plugins/
 * Description: Convert MailPoet newsletters into WordPress blocks
 * Version: 0.0.1
 * Requires at least: 5.7
 * Requires PHP:      7.1.0
 * Author:            david wolfpaw
 * Author URI:        https://davidwolfpaw.com
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       mailpoet-to-blocks
 * Domain Path:       /languages
 */

if ( ! class_exists( 'MailPoet_to_Blocks_Converter_Admin' ) ) {
	require_once dirname( __FILE__ ) . '/admin/mailpoet-to-blocks-converter-admin.php';
}

if ( ! class_exists( 'MailPoet_to_Blocks_Converter_Builder' ) ) {
	require_once dirname( __FILE__ ) . '/admin/mailpoet-to-blocks-converter-builder.php';
}

if ( ! class_exists( 'WP_Async_Task' ) ) {
	require_once dirname( __FILE__ ) . '/admin/class-wp-async-task.php';
}
