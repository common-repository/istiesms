<?php
/*
Plugin Name: Istie SMS
Description: Plugin waarmee alle gebruikers van je website sms-berichten kunnen sturen of in kunnen plannen om ze op een later tijdstip of datum via Messagebird gateway te verzenden. Een Messagebird account is dus vereist.
Plugin URI:  http://www.istiecool.nl/ic-plugins/istie-sms/
Version:     0.5.7
Author:      Istiecool
Author URI:  http://www.istiecool.nl

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/
//hide/show warnings
@ini_set('display_errors', 1); // 0/1

//  Version check
global $wp_version;
$exit_msg = __( 'Deze plugin vereist WordPress version 3.1 of nieuwer; Update je systeem eerst a.u.b.', 'istie_sms' );
	if (version_compare($wp_version,"3.1","<")) {
		exit ($exit_msg);
	}

//	Textdomain for the translation
$textdomain = 'istie_sms';
//	Load translation
load_plugin_textdomain($textdomain, false, dirname( plugin_basename(__FILE__) ) . '/lang');
//	plugin's version
define('ISTIE_SMS_VER','0.5.7');
define('ICSMS_PLUGIN_DIR', WP_PLUGIN_DIR . '/istiesms/');
define('ICSETTINGS_PLUGIN_DIR', WP_PLUGIN_DIR . '/istie-settings/');
// load extra functions
require_once(''. WP_PLUGIN_DIR . '/istiesms/includes/icsms_functions.php');

//--- adds admin menu -------------------------------------------------------------------*/	
// add_menu_page($pagetitle,$menutitle,$capability,$menu_slug,$function,$icon-url,$position);
// $capability: read, add_users, manage_options
add_action('admin_menu', 'icsms_menu_admin');	
	function icsms_menu_admin() {	
		if(is_plugin_active('istie-settings/istie-settings.php')) {
			// If istie-settings plugin exists create submenu
			if (function_exists('add_submenu_page')) {
				add_submenu_page('istie-settings/info.php', 'IstieSMS', 'IstieSMS', 'administrator', 'istiesms/admin/sms_options.php');						
			}			
		} else {
			// Create new mainmenu
			if (function_exists('add_menu_page')) {
				add_menu_page('IstieSMS menu', 'IstieSMS', 'administrator', 'istiesms/admin/sms_options.php', '', WP_PLUGIN_URL . ('/istiesms/images/istie-16x16.png'), 82);
			}
			//if (function_exists('add_submenu_page')) {
			//	add_submenu_page('istiesms/sms_options.php', 'Instellingen', 'Instellingen', 'read', //'istiesms/sms_options.php');						
			//}			
		} // end check for istie-settings plugin exists
	} // end function icsms_Menu_istie_sms
	
	
//--- adds user menu -------------------------------------------------------------------*/	
add_action('admin_menu', 'icsms_menu_user');	
	function icsms_menu_user() {		
		if ( is_user_logged_in() ){
			if (function_exists('add_menu_page')) {
				add_menu_page('IstieSMS menu', 'Stuur SMS', 'read', 'istiesms/sms_send.php', '', WP_PLUGIN_URL . ('/istiesms/images/istie-16x16.png'), 41);
			}
			if (function_exists('add_submenu_page')) {
				add_submenu_page('istiesms/sms_send.php', 'SMS Credits', 'SMS Credits', 'read', 'istiesms/sms_credits.php');
				add_submenu_page('istiesms/sms_send.php', 'Overzicht', 'Overzicht', 'read', 'istiesms/sms_history.php');	
				//add_submenu_page('istiesms/sms_send.php', 'Contacten', 'Contacten', 'read', 'istiesms/sms_contacts.php');				
			}
		}
	}	

//--- adds extra info link on plugin page -----------------------------------------------*/
if (!function_exists('icsms_plugin_add_link')) {	
	function icsms_plugin_add_link($links) {
		//$sms_link = '<a href="tools.php?page=istiesms-page">Extra Info</a>';
		$sms_link = '<a href="admin.php?page=istiesms/admin/sms_options.php">Extra Info</a>';
		array_push( $links, $sms_link );
		return $links;
	}
}
$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'icsms_plugin_add_link' );

/*--- Create database when activating plugin --------------------------------------------*/
function icsms_install() {
	global $wpdb; // , $wp_roles, $wp_version;
	
	//$table_name = $wpdb->prefix . "liveshoutbox";
	
	// add charset & collate like wp core
	//$charset_collate = '';
	//if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
	//	if ( ! empty($wpdb->charset) )
	//		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	//	if ( ! empty($wpdb->collate) )
	//		$charset_collate .= " COLLATE $wpdb->collate";
	//}	
	
	// add database pointer
	$ic_smscustomers = $wpdb->prefix . 'ic_smscustomers';
	$ic_smscontacts = $wpdb->prefix . 'ic_smscontacts';
	$ic_smsmessages = $wpdb->prefix . 'ic_smsmessages';	

	// check if table exists
	/*
	if( !$wpdb->get_var( "SHOW TABLES LIKE '$ic_smscustomers'" ) ) {
		$customers = "CREATE TABLE $ic_smscustomers (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		userlocaltime VARCHAR(75) DEFAULT '' NOT NULL,
		IDcustomer VARCHAR(40) DEFAULT '' NOT NULL,
		loginname VARCHAR(40) DEFAULT '' NOT NULL,
		email VARCHAR(40) DEFAULT '' NOT NULL,
		password VARCHAR(40) DEFAULT '' NOT NULL,
		firstname VARCHAR(40) DEFAULT '' NOT NULL,
		middlename VARCHAR(40) DEFAULT '' NOT NULL,
		lastname VARCHAR(40) DEFAULT '' NOT NULL,
		street VARCHAR(50) DEFAULT '' NOT NULL,
		number VARCHAR(10) DEFAULT '' NOT NULL,
		postal VARCHAR(6) DEFAULT '' NOT NULL,
		city VARCHAR(50) DEFAULT '' NOT NULL,
		telephone VARCHAR(20) DEFAULT '' NOT NULL,
		mobile VARCHAR(15) DEFAULT '' NOT NULL,
		SMS_account VARCHAR(50) DEFAULT '' NOT NULL,
		extra_2 VARCHAR(10) DEFAULT '' NOT NULL,
		extra_3 VARCHAR(6) DEFAULT '' NOT NULL,
		discount VARCHAR(4) DEFAULT '' NOT NULL,
		credits VARCHAR(4) DEFAULT '' NOT NULL,
		PRIMARY  KEY id (id),
		UNIQUE KEY id (id)
		);";
		//) $charset_collate;";		  
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($customers);		
    }
	// check if contacts table exists
	if( !$wpdb->get_var( "SHOW TABLES LIKE '$ic_smscontacts'" ) ) {
		//create customer accounts table
		$contacts = "CREATE TABLE $ic_smscontacts (
		cid mediumint(9) NOT NULL AUTO_INCREMENT,
		timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		loginname VARCHAR(50) DEFAULT '' NOT NULL,
		email VARCHAR(40) DEFAULT '' NOT NULL,
		recipient_name VARCHAR(40) DEFAULT '' NOT NULL,
		recipient_number VARCHAR(40) DEFAULT '' NOT NULL,
		SMS_message longtext NOT NULL,
		PRIMARY  KEY cid (cid),
		UNIQUE KEY cid (cid)
		);";
		//) $charset_collate;";		  
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($contacts);		
    }
	*/
	// check if messages table exists
	if( !$wpdb->get_var( "SHOW TABLES LIKE '$ic_smsmessages'" ) ) {
		//create customer accounts table
		$messages = "CREATE TABLE $ic_smsmessages (
		mid mediumint(9) NOT NULL AUTO_INCREMENT,
		timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		userlocaldate VARCHAR(75) DEFAULT '' NOT NULL,
		userlocaltime VARCHAR(75) DEFAULT '' NOT NULL,
		loginname VARCHAR(40) DEFAULT '' NOT NULL,
		email VARCHAR(40) DEFAULT '' NOT NULL,
		originator VARCHAR(40) DEFAULT '' NOT NULL,
		recipient_name VARCHAR(40) DEFAULT '' NOT NULL,
		recipient_number VARCHAR(40) DEFAULT '' NOT NULL,		
		SMS_message longtext NOT NULL,
		delivery_date VARCHAR(40) DEFAULT '' NOT NULL,
		delivery_time VARCHAR(40) DEFAULT '' NOT NULL,
		sms_delivery VARCHAR(50) DEFAULT '' NOT NULL,
		sms_id VARCHAR(50) DEFAULT '' NOT NULL,
		credits smallint(4) NOT NULL,
		PRIMARY  KEY mid (mid),
		UNIQUE KEY mid (mid)
		);";
		//) $charset_collate;";		  
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($messages);		
    }
	// Check for capability
	if ( !current_user_can('activate_plugins') )
		return;
		
	// Set the capabilities for the administrator
	$role = get_role('administrator');
	// We need this role, no other chance
	if ( empty($role) ) {
		update_option( "icsms_check", __('Sorry, IstieSMS works only with a role called administrator',"istie_sms") );
		return;
	}
	// check user roles	
	//if(!$role->has_cap('sms_options')) {
	//	$role->add_cap('sms_options');
	//}	
	
	update_option( "icsms_version", ''. ISTIE_SMS_VER .'');
	update_option( 'icsms_credits_10x', 2.50);
	update_option( 'icsms_credits_20x', 5.75);
	update_option( 'icsms_credits_50x', 10);
	
}
//create the tables on plugin activation
register_activation_hook( __FILE__, 'icsms_install');


/*--- Delete options table entries ONLY when plugin deactivated AND deleted -------------*/
function icsms_uninstall_plugin() {
	global $wpdb;
	// first remove all tables
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ic_smscustomers");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ic_smscontacts");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ic_smsmessages");	
	
	// then remove all options
	delete_option( 'icsms_check');
	delete_option( 'icsms_version');
	delete_option( 'icsms_usersettings');
	delete_option( 'icsms_mollie_username');
	delete_option( 'icsms_mollie_passwd');
	delete_option( 'icsms_credits_10x');
	delete_option( 'icsms_credits_20x');
	delete_option( 'icsms_credits_50x');
	delete_option( 'icsms_credits_usergift');
	delete_option( 'icsms_admin_username');
	delete_option( 'icsms_admin_email');
	delete_option( 'icsms_admin_bankrek');
	delete_option( 'icsms_admin_banktnv');
	delete_option( 'icsms_admin_bankname');
	delete_option( 'icsms_admin_bankplace');

	// now remove the capability
	//remove_cap('sms_options') ;
	
}
// call function when when uninstalling	to clean options table 
register_uninstall_hook(__FILE__, 'icsms_uninstall_plugin');	




?>