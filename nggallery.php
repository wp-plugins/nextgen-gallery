<?php
/*
Plugin Name: NextGEN Gallery
Plugin URI: http://alexrabe.boelinger.com/?page_id=80
Description: A NextGENeration Photo gallery for the WEB2.0(beta). At the moment only poor Web1.0 :-(
Author: Alex Rabe
Version: 0.33a

Author URI: http://alexrabe.boelinger.com/

Copyright 2007 by Alex Rabe

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

// Version and path to check version
define('NGGVERSION', "0.33");
define('NGGURL', "http://nextgen.boelinger.com/version.php");

// define URL
$myabspath = str_replace("\\","/",ABSPATH);  // required for Windows & XAMPP
define('WINABSPATH', $myabspath);
define('NGGALLERY_ABSPATH', $myabspath.'wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');
define('NGGALLERY_URLPATH', get_option('siteurl').'/wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');

// look for imagerotator
define('NGGALLERY_IREXIST', file_exists(NGGALLERY_ABSPATH.'imagerotator.swf'));

//get value for safe mode
define('SAFE_MODE', ini_get('safe_mode'));

global $wpdb;

//read the options
$ngg_options = get_option('ngg_options');

// database 
$wpdb->nggpictures					= $wpdb->prefix . 'ngg_pictures';
$wpdb->nggallery					= $wpdb->prefix . 'ngg_gallery';
$wpdb->nggalbum						= $wpdb->prefix . 'ngg_album';

// Load language
function nggallery_init ()
{
load_plugin_textdomain('nggallery','wp-content/plugins/nggallery/lang');
}

// Load admin panel
include_once ("ngginstall.php");
include_once ("nggfunctions.php");
include_once ("admin/admin.php");

// add header to theme
function integrate_nggheader() {
	global $ngg_options;
	echo "<meta name='NextGEN' content='".NGGVERSION."' />\n";
	echo "\n".'<style type="text/css" media="screen">@import "'.NGGALLERY_URLPATH.'css/'.$ngg_options[CSSfile].'";</style>';
	if ($ngg_options[thumbEffect] == "thickbox") {
	echo "\n".'<script type="text/javascript" src="'.NGGALLERY_URLPATH.'admin/js/jquery.js"></script>';
	echo "\n".'<script type="text/javascript"> var LoadingImage = "'.NGGALLERY_URLPATH.'thickbox/loadingAnimation.gif";</script>';
	echo "\n".'<script type="text/javascript" src="'.NGGALLERY_URLPATH.'thickbox/thickbox.js"></script>';
	echo "\n".'<style type="text/css" media="screen">@import "'.NGGALLERY_URLPATH.'thickbox/thickbox.css";</style>'."\n";
	if (!function_exists('integrate_swfobject'))
	echo "\n".'<script type="text/javascript" src="'.NGGALLERY_URLPATH.'js/swfobject.js'.'"></script>'."\n";
	}
}
// Filter hook to activate CSS in header
if ($ngg_options[activateCSS]) add_filter('wp_head', 'integrate_nggheader');

add_action('admin_head', 'ngg_nocache');
// add header to theme
function ngg_nocache() {
	echo "\n".'<meta name="NextGEN" content="'.NGGVERSION.'" />';
	echo "\n".'<meta http-equiv="pragma" content="no-cache" />'."\n";
}

// load language file
add_action('init', 'nggallery_init');

add_action('activate_nggallery/nggallery.php', 'ngg_install');
// init wpTable in wp-database if plugin is activated
function ngg_install() {
	nggallery_install();
}

// Action calls for all functions 
add_filter('the_content', 'searchnggallerytags');
add_filter('the_excerpt', 'searchnggallerytags');

//#################################################################
/*
upload_files_(tab) 
Runs to print a screen on the upload files admin screen; 
"tab" is the name of the custom action tab. 
Define custom tabs using the wp_upload_tabs filter  

wp_upload_tabs
applied to the list of custom tabs to display on the upload management admin screen. 
Use action upload_files_(tab) to display a page for your custom tab
*/

//TODO: Integrate all galleries in Upload panel
// add_action('upload_files_ngg_test', 'ngg_action_upload_Tab');
// add_filter('wp_upload_tabs', 'ngg_wp_upload_tabs');

function ngg_action_upload_Tab() {
	// execute when click on the tab
	add_action('admin_print_scripts', 'ngg_upload_tabs_script');
}

function ngg_upload_tabs_script() {

}

function ngg_wp_upload_tabs ($array) {

    /* THX to SilasFlickrPlugin
    0 => tab display name, 
    1 => required cap / role, 
    2 => function that produces tab content, 
    3 => total number objects OR array(total, objects per page), 
    4 => add_query_args
	*/
	
	include_once ("nggadmintab.php");

	$tab = array(
            'ngg_test' => array('Gallery', 'upload_files', 'ngg_upload_tab_content', 0)
    );

    return array_merge($array,$tab);
}
//#################################################################

?>