<?php
/**
 * Plugin Name: Untold Spektrix System 
 * Plugin URI:  https://wearehdk.com
 * Description: Custom Plugin for Untold (integrates the Spektrix Ticketing System)
 * Version:     1.0
 * Author:      Chad Rossouw for HdK
 * Author URI:  https://wearehdk.com
 */

defined( 'ABSPATH' ) or exit;

register_activation_hook(__FILE__, 'add_initiate_option' );
/*function create_tables(){
    require  __DIR__.'/inc/class-builder.php';
    $HdKSpInit = new HdKSpBuild;
    $HdKSpInit->createTables();
    
}*/

function add_initiate_option(){
    add_option('hdk-initiate-plugin','0');
    add_option('hdk-populate-tables','0');
    add_option('hdk-settings-submitted','0');
    add_option('hdk-attributes-submitted','0');
}

register_deactivation_hook( __FILE__, 'spektrix_deactivate' ); 
 
function spektrix_deactivate() {
    $timestamp = wp_next_scheduled( 'spektrix_cron_hook' );
    wp_unschedule_event( $timestamp, 'spektrix_cron_hook' );
    delete_option('hdk-initiate-plugin');
    delete_option('hdk-populate-tables');
    delete_option('hdk-settings-submitted');
    delete_option('hdk-attributes-submitted');
    delete_option('hdk-activate');
}

function hdk_spektrix_load_plugin(){
    define( 'HDK_SPEKTRIX_DIR', __DIR__ . '/' );
	define( 'HDK_SPEKTRIX_FILE', __FILE__ );
    define( 'HDK_SPEKTRIX_VERSION', '2.1' );
    require HDK_SPEKTRIX_DIR . 'inc/class-utilities.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-spektrix.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-actions.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-builder.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-front-loader.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-init.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-settings.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-populate.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-api-calls.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-forms.php';
    require HDK_SPEKTRIX_DIR . 'inc/cli/class-cli.php';
    require HDK_SPEKTRIX_DIR . 'inc/event/admin.php';
    require HDK_SPEKTRIX_DIR . 'inc/models/class-event.php';
    require HDK_SPEKTRIX_DIR . 'inc/models/class-event-spektrix.php';
    require HDK_SPEKTRIX_DIR . 'inc/models/class-event-external.php';
    require HDK_SPEKTRIX_DIR . 'inc/class-user.php';


    $Spektrix = new HdKSpInit();
}

add_action( 'plugins_loaded', 'hdk_spektrix_load_plugin', 8 );

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'hdk_add_plugin_page_settings_link');
function hdk_add_plugin_page_settings_link( $actions ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page=hdk_spektrix' ) .
		'">' . __('Settings') . '</a>';
    $settings = array('settings' => '<a href="admin.php?page=hdk_spektrix">Settings</a>');
    $actions = array_merge($settings, $actions);
	return $actions;
}