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

// require_once dirname( __FILE__ ). '/inc/functions.php';
require_once dirname( __FILE__ ). '/inc/block-exporter.php';

require_once dirname( __FILE__ ) . '/admin/mailpoet-to-blocks-converter-admin.php';
require_once dirname( __FILE__ ) . '/admin/mailpoet-to-blocks-converter-builder.php';
