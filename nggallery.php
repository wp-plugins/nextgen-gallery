<?php
/*
Plugin Name: NextGEN Gallery
Plugin URI: http://alexrabe.boelinger.com/?page_id=80
Description: A NextGENeration Photo gallery for the WEB2.0(beta).
Author: NextGEN DEV-Team
Version: 0.73

Author URI: http://alexrabe.boelinger.com/

Copyright 2007 by Alex Rabe & NextGEN DEV-Team

The NextGEN button is taken from the Silk set of FamFamFam. See more at 
http://www.famfamfam.com/lab/icons/silk/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/ 

//#################################################################
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

//#################################################################
// Let's Go

global $wpdb, $wp_version;

// ini_set('display_errors', '1');
// ini_set('error_reporting', E_ALL);

//This works only in WP2.1 or higher
if (version_compare($wp_version, '2.1', '>=')) {

// Permission settings ############################################
define('NGGFOLDER_PERMISSION', 0777);
define('NGGFILE_PERMISSION', 0666);
// ################################################################

// Version and path to check version
define('NGGVERSION', "0.73");
define('NGGURL', "http://nextgen.boelinger.com/version.php");

// define URL
$myabspath = str_replace("\\","/",ABSPATH);  // required for Windows & XAMPP
define('WINABSPATH', $myabspath);
define('NGGFOLDER', dirname(plugin_basename(__FILE__)));
define('NGGALLERY_ABSPATH', $myabspath.'wp-content/plugins/' . NGGFOLDER .'/');
define('NGGALLERY_URLPATH', get_option('siteurl').'/wp-content/plugins/' . NGGFOLDER.'/');

// look for imagerotator
define('NGGALLERY_IREXIST', file_exists(NGGALLERY_ABSPATH.'imagerotator.swf'));

// get value for safe mode
if ((gettype(ini_get('safe_mode')) == 'string')) {
	// if sever did in in a other way
	if (ini_get('safe_mode') == 'off') define('SAFE_MODE', FALSE);
	else define('SAFE_MODE', ini_get('safe_mode'));
} else
define('SAFE_MODE', ini_get('safe_mode'));

//read the options
$ngg_options = get_option('ngg_options');

// add database pointer 
$wpdb->nggpictures					= $wpdb->prefix . 'ngg_pictures';
$wpdb->nggallery					= $wpdb->prefix . 'ngg_gallery';
$wpdb->nggalbum						= $wpdb->prefix . 'ngg_album';
$wpdb->nggtags						= $wpdb->prefix . 'ngg_tags';
$wpdb->nggpic2tags					= $wpdb->prefix . 'ngg_pic2tags';

// Load language
function nggallery_init ()
{
	load_plugin_textdomain('nggallery','wp-content/plugins/' . NGGFOLDER.'/lang');
}

// Load admin panel
include_once (dirname (__FILE__)."/ngginstall.php");
include_once (dirname (__FILE__)."/nggfunctions.php");
include_once (dirname (__FILE__)."/admin/admin.php");

// load gallery class
require_once (dirname (__FILE__).'/lib/nggallery.lib.php');
$nggallery = new nggallery();

// add javascript to header
add_action('wp_head', 'ngg_addjs', 1);
function ngg_addjs() {
    global $wp_version, $ngg_options;
    
	echo "<meta name='NextGEN' content='".NGGVERSION."' />\n";
	if ($ngg_options['activateCSS']) 
		echo "\n".'<style type="text/css" media="screen">@import "'.NGGALLERY_URLPATH.'css/'.$ngg_options[CSSfile].'";</style>';
	if ($ngg_options['thumbEffect'] == "thickbox") {
		echo "\n".'<script type="text/javascript"> var tb_pathToImage = "'.NGGALLERY_URLPATH.'thickbox/'.$ngg_options[thickboxImage].'";</script>';
		echo "\n".'<style type="text/css" media="screen">@import "'.NGGALLERY_URLPATH.'thickbox/thickbox.css";</style>'."\n";
	    if ($wp_version < "2.3") {
	    	if ($wp_version > "2.1.3") wp_deregister_script('jquery'); 
	    	wp_enqueue_script('jquery', NGGALLERY_URLPATH .'admin/js/jquery.js', FALSE, '1.1.3.1');
		} 
	    	wp_enqueue_script('thickbox', NGGALLERY_URLPATH .'thickbox/thickbox-pack.js', array('jquery'), '3.1.1');

    	// add NextGEN jQuery Plugin
		if ($ngg_options['galUsejQuery'])
			wp_enqueue_script('nextgen', NGGALLERY_URLPATH .'admin/js/jquery.nextgen.pack.js', array('jquery'), '0.5');
			//TODO: NEW AJAX Version
			// wp_enqueue_script('nextgen-ajax', NGGALLERY_URLPATH .'admin/js/jquery.nextgen.ajax.js', array('jquery'), '0.1');
			// wp_enqueue_script('blockui', NGGALLERY_URLPATH .'admin/js/jquery.blockUI.js', array('jquery'), '0.1');
	    }
	    
	// test for wordTube function
	if (!function_exists('integrate_swfobject')) {
		wp_enqueue_script('swfobject', NGGALLERY_URLPATH .'js/swfobject.js', FALSE, '1.5');
	}
}

// load language file
add_action('init', 'nggallery_init');

add_action('activate_' . NGGFOLDER.'/nggallery.php', 'ngg_install');
// init tables in wp-database if plugin is activated
function ngg_install() {
	nggallery_install();
}

// Action calls for all functions 
add_filter('the_content', 'searchnggallerytags');
add_filter('the_excerpt', 'searchnggallerytags');

//#################################################################
// add action/filter for the upload tab 
add_action('upload_files_ngg_gallery', 'ngg_action_upload_Tab');
add_filter('wp_upload_tabs', 'ngg_wp_upload_tabs');

function ngg_action_upload_Tab() {
	// execute when click on the tab
	global $style;
	if ( 'inline' == $style )
		wp_enqueue_script('nggadmintab', NGGALLERY_URLPATH .'js/nggadmintab-js.php', array('prototype'), '0.6');
}

function ngg_wp_upload_tabs ($array) {
	
	global $wpdb;
	
    /* 
    0 => tab display name, 
    1 => required cap / role, 
    2 => function that produces tab content, 
    3 => total number objects OR array(total, objects per page), 
    4 => add_query_args
	*/
	include_once (dirname (__FILE__)."/nggadmintab.php");

	// Create navigation
	$total = 1;
	if ($_GET['select_gal']){
		$galleryID = $_GET['select_gal'];
		$total = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures WHERE galleryid = '$galleryID'");
	}

	$tab = array(
            'ngg_gallery' => array(__('Gallery','nggallery'), 'NextGEN Use TinyMCE', 'ngg_upload_tab_content', array($total, 10))
    );

    return array_merge($array,$tab);
}
//#################################################################
// TinyMCE Button Integration

// Load the Script for the Button
function insert_nextgen_script() {	
 
 	//TODO: Do with WP2.1 Script Loader
 	// Thanks for this idea to www.jovelstefan.de
	echo "\n"."
	<script type='text/javascript'> 
		function ngg_buttonscript()	{ 
		if(window.tinyMCE) {

			var template = new Array();
	
			template['file'] = '".NGGALLERY_URLPATH."nggbutton.php';
			template['width'] = 360;
			template['height'] = 210;
	
			args = {
				resizable : 'no',
				scrollbars : 'no',
				inline : 'yes'
			};
	
			tinyMCE.openWindow(template, args);
			return true;
		} 
	} 
	</script>"; 
	return;
}

function ngg_addbuttons() {
 
	global $wp_db_version;

	// Don't bother doing this stuff if the current user lacks permissions
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
	
	// Check for NextGEN capability
	if ( !current_user_can('NextGEN Use TinyMCE') ) return;
	 
	// Add only in Rich Editor mode
	if ( get_user_option('rich_editing') == 'true') {
	 
	// add the button for wp21 in a new way
		add_filter("mce_plugins", "nextgen_button_plugin", 5);
		add_filter('mce_buttons', 'nextgen_button', 5);
		add_action('tinymce_before_init','nextgen_button_script');
		}
}

// used to insert button in wordpress 2.1x editor
function nextgen_button($buttons) {

	array_push($buttons, "separator", "NextGEN");
	return $buttons;

}

// Tell TinyMCE that there is a plugin (wp2.1)
function nextgen_button_plugin($plugins) {    

	array_push($plugins, "-NextGEN");    
	return $plugins;
}

// Load the TinyMCE plugin : editor_plugin.js (wp2.1)
function nextgen_button_script() {	
 
 	$pluginURL =  NGGALLERY_URLPATH.'js/';
	echo 'tinyMCE.loadPlugin("NextGEN", "'.$pluginURL.'");' . "\n"; 
	return;
}

// init process for button control
add_action('init', 'ngg_addbuttons');
add_action('edit_page_form', 'insert_nextgen_script');
add_action('edit_form_advanced', 'insert_nextgen_script');

//#################################################################

} else {
	add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error fade"><p><strong>' . __('Sorry, NextGEN Gallery works only under WordPress 2.1 or higher',"nggallery") . '</strong></p></div>\';'));
}// End Check for WP 2.1
?>