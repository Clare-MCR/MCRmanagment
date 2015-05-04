<?php
/**
 * Plugin Name: mcr_user_managment
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: MCR database user managment.
 * Version: 0.0.2
 * Author: Richard Gunning, rjg70
 * Author URI: http://mcr.clare.cam.ac.uk/author/rjg70
 * Text Domain: Optional. Plugin's text domain for localization. Example: mytextdomain
 * Domain Path: Optional. Plugin's relative directory path to .mo files. Example: /locale/
 * Network: True
 * License: A short license name. Example: GPL2
 */

 /*  Copyright 2015  Richard Gunning  (email : rjg70@cam.ac.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

require_once('user.class.php');
global $mcr_db_version;
$mcr_db_version = '1.0';

function mcr_install () {
	global $wpdb;
	global $mcr_db_version;
	$installed_ver = get_option( "mcr_db_version" );
	if ( $installed_ver != $mcr_db_version ) {
		$table_name = $wpdb->prefix . "mcraccess";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  crsid varchar(10) NOT NULL,
		  e_view tinyint(1) DEFAULT '0' Not NULL,
		  e_book tinyint(1) DEFAULT '0' Not NULL,
		  e_adm tinyint(1) DEFAULT '0' Not NULL,
		  p_view tinyint(1) DEFAULT '0' Not NULL,
		  p_book tinyint(1) DEFAULT '0' Not NULL,
		  p_adm tinyint(1) DEFAULT '0' Not NULL,
		  mcr_member tinyint(1) DEFAULT '0' Not NULL,
		  associate_member tinyint(1) DEFAULT '0' Not NULL,
		  cra tinyint(1) DEFAULT '0' Not NULL,
		  college_bill tinyint(1) DEFAULT '0' Not NULL,
		  type tinyint(1) DEFAULT '0' Not NULL,
		  enabled tinyint(1) DEFAULT '0' Not NULL,
		  UNIQUE KEY id (id),
		  UNIQUE KEY crsid (crsid)
		) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'mcr_db_version', $mcr_db_version );

	}

}

function mcr_install_data() {
	global $wpdb;

	$crsid = 'rjg70';

	$table_name = $wpdb->prefix . 'mcraccess';

	$wpdb->insert(
		$table_name,
		array(
			'crsid' => $crsid,
		)
	);
}

register_activation_hook( __FILE__, 'mcr_install' );


function myplugin_update_db_check() {
    global $mcr_db_version;
    if ( get_site_option( 'mcr_db_version' ) != $mcr_db_version ) {
        mcr_install();
    }
}
add_action( 'plugins_loaded', 'myplugin_update_db_check' );

/** Step 2 (from text above). */
add_action( 'admin_menu', 'my_plugin_menu' );

/** Step 1. */
function my_plugin_menu() {
	add_menu_page( 'MCR db access', 'Clare MCR', 'manage_options', 'clare-mcr-access', 'my_plugin_options', plugins_url('Files/favicon.ico', __FILE__ ) );
}

/** Step 3. */
function my_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$user = new User('newuser1');
	$user->commit();
	global $wpdb;
	$table_name = $wpdb->prefix . "mcraccess";
	$results = $wpdb->get_results( "SELECT crsid FROM $table_name", OBJECT );

	echo '<div class="wrap">';
	echo '<img src="'.plugins_url('Files/logo.png',__FILE__ ).'" alt="Logo">';
	echo '<table>';
	foreach ($results as $row )
	{
		echo '<tr><td>'.$row->crsid .'</tr></td>';
	}
	echo '</table';
	echo '<br class="clear">';

	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
}

?>