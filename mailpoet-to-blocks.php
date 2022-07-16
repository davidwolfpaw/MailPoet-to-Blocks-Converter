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
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mailpoet-to-blocks
 * Domain Path:       /languages
 */

require_once dirname( __FILE__ ). '/inc/functions.php';
require_once dirname( __FILE__ ). '/inc/hooks.php';
/**
* Setup plugin updater
*/
add_action( 'plugins_loaded', function(){
	require_once dirname( __FILE__ ). '/inc/MailPoetToBlocksConverterUpdater.php';
	new \MailPoetToBlocksConverterUpdater( '0.0.1', plugin_basename( __DIR__ ), plugin_basename( __FILE__ ) );
});

require_once dirname( __FILE__ ) . '/admin/mailpoet-to-block-converter/init.php';
