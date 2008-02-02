<?php

require_once('../../../../wp-config.php');
require_once(ABSPATH.'/wp-admin/admin.php');

auth_redirect();

//check for correct capability
if ( !is_user_logged_in() )
	die('-1');

//check for correct capability
if ( !current_user_can('NextGEN Manage gallery') ) 
	die('-1');

function get_out_now() { exit; }
add_action( 'shutdown', 'get_out_now', -1 );

//check for correct nonce 
check_admin_referer('ngg_swfupload');

//check for nggallery
if ( !defined('NGGALLERY_ABSPATH') )
	die('-1');
	
include_once (NGGALLERY_ABSPATH. 'admin/functions.php');

// get the gallery
$galleryID = (int) $_POST['galleryselect'];

echo nggAdmin::swfupload_image($galleryID);

?>