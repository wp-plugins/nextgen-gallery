<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

//required database version
$ngg_db_version = "0.71";

function nggallery_install () {
	
   	global $wpdb , $wp_roles, $wp_version;
   	global $ngg_db_version;

	// Check for capability
	if ( !current_user_can('activate_plugins') ) 
		return;
	
	// Set the capabilities for the administrator
	$role = get_role('administrator');
	$role->add_cap('NextGEN Gallery overview');
	$role->add_cap('NextGEN Use TinyMCE');
	$role->add_cap('NextGEN Upload images');
	$role->add_cap('NextGEN Manage gallery');
	$role->add_cap('NextGEN Edit album');
	$role->add_cap('NextGEN Change style');
	$role->add_cap('NextGEN Change options');
	
	// upgrade function changed in WordPress 2.3	
	if (version_compare($wp_version, '2.3-beta', '>='))		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	else
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		
   	$nggpictures					= $wpdb->prefix . 'ngg_pictures';
	$nggallery						= $wpdb->prefix . 'ngg_gallery';
	$nggalbum						= $wpdb->prefix . 'ngg_album';
	$nggtags						= $wpdb->prefix . 'ngg_tags';
	$nggpic2tags					= $wpdb->prefix . 'ngg_pic2tags';
   
	if($wpdb->get_var("show tables like '$nggpictures'") != $nggpictures) {
      
		$sql = "CREATE TABLE " . $nggpictures . " (
		pid BIGINT(20) NOT NULL AUTO_INCREMENT ,
		galleryid BIGINT(20) DEFAULT '0' NOT NULL ,
		filename VARCHAR(255) NOT NULL ,
		description MEDIUMTEXT NULL ,
		alttext MEDIUMTEXT NULL ,
		exclude TINYINT NULL DEFAULT '0' ,
		PRIMARY KEY pid (pid)
		);";
	
      dbDelta($sql);
 
 		ngg_default_options();
		add_option("ngg_db_version", $ngg_db_version);
   }

	if($wpdb->get_var("show tables like '$nggallery'") != $nggallery) {
      
		$sql = "CREATE TABLE " . $nggallery . " (
		gid BIGINT(20) NOT NULL AUTO_INCREMENT ,
		name VARCHAR(255) NOT NULL ,
		path MEDIUMTEXT NULL ,
		title MEDIUMTEXT NULL ,
		galdesc MEDIUMTEXT NULL ,
		pageid BIGINT(20) NULL DEFAULT '0' ,
		previewpic BIGINT(20) NULL DEFAULT '0' ,
		PRIMARY KEY gid (gid)
		);";
	
      dbDelta($sql);
   }

	if($wpdb->get_var("show tables like '$nggalbum'") != $nggalbum) {
      
		$sql = "CREATE TABLE " . $nggalbum . " (
		id BIGINT(20) NOT NULL AUTO_INCREMENT ,
		name VARCHAR(255) NOT NULL ,
		sortorder LONGTEXT NOT NULL,
		PRIMARY KEY id (id)
		);";
	
      dbDelta($sql);
    }
    
    // new since version 0.70
    if($wpdb->get_var("show tables like '$nggtags'") != $nggtags) {
      
		$sql = "CREATE TABLE " . $nggtags . " (
		id BIGINT(20) NOT NULL AUTO_INCREMENT ,
		name VARCHAR(55) NOT NULL ,
		slug VARCHAR(200) NOT NULL,
		PRIMARY KEY id (id),
		UNIQUE KEY slug (slug)
		);";
	
      dbDelta($sql);
    }
    
    if($wpdb->get_var("show tables like '$nggpic2tags'") != $nggpic2tags) {
      
		$sql = "CREATE TABLE " . $nggpic2tags . " (
		 picid BIGINT(20) NOT NULL DEFAULT 0,
		 tagid BIGINT(20) NOT NULL DEFAULT 0,
		 PRIMARY KEY  (picid, tagid),
		 KEY tagid (tagid)
		);";
	
      dbDelta($sql);
    }
   
   // update routine
    $installed_ver = get_option( "ngg_db_version" );
	if( $installed_ver != $ngg_db_version ) {
		
		// v0.33 -> v.071
		$wpdb->query("ALTER TABLE ".$nggpictures." CHANGE pid pid BIGINT(20) NOT NULL AUTO_INCREMENT ");
		$wpdb->query("ALTER TABLE ".$nggpictures." CHANGE galleryid galleryid BIGINT(20) NOT NULL ");
		$wpdb->query("ALTER TABLE ".$nggallery." CHANGE gid gid BIGINT(20) NOT NULL AUTO_INCREMENT ");
		$wpdb->query("ALTER TABLE ".$nggallery." CHANGE pageid pageid BIGINT(20) NULL DEFAULT '0'");
		$wpdb->query("ALTER TABLE ".$nggallery." CHANGE previewpic previewpic BIGINT(20) NULL DEFAULT '0'");
		$wpdb->query("ALTER TABLE ".$nggallery." CHANGE gid gid BIGINT(20) NOT NULL AUTO_INCREMENT ");
		$wpdb->query("ALTER TABLE ".$nggallery." CHANGE description galdesc MEDIUMTEXT NULL");

		update_option( "ngg_db_version", $ngg_db_version );
	}

}

function ngg_default_options() {

	$ngg_options['gallerypath']			= "wp-content/gallery/";  		// set default path to the gallery
	$ngg_options['scanfolder']			= false;						// search for new images  (not used)
	$ngg_options['deleteImg']			= false;						// delete Images
	
	// Tags / categories
	$ngg_options['activateTags']		= false;						// append related images
	$ngg_options['appendType']			= "category";					// look for category or tags
	$ngg_options['maxImages']			= 7;  							// number of images toshow
	
	// Thumbnail Settings
	$ngg_options['thumbwidth']			= 100;  						// Thumb Width
	$ngg_options['thumbheight']			= 75;  							// Thumb height
	$ngg_options['thumbfix']			= true;							// Fix the dimension
	$ngg_options['thumbcrop']			= false;						// Crop square thumbnail
	$ngg_options['thumbquality']		= 100;  						// Thumb Quality
	$ngg_options['thumbResampleMode']	= 3;  							// Resample speed value 1 - 5 
		
	// Image Settings
	$ngg_options['imgResize']			= false;						// Activate resize (not used)
	$ngg_options['imgWidth']			= 800;  						// Image Width
	$ngg_options['imgHeight']			= 600;  						// Image height
	$ngg_options['imgQuality']			= 85;							// Image Quality
	$ngg_options['imgResampleMode']		= 4;  							// Resample speed value 1 - 5
	
	// Gallery Settings
	$ngg_options['galImages']			= "20";		  					// Number Of images per page
	$ngg_options['galShowSlide']		= true;							// Show slideshow
	$ngg_options['galTextSlide']		= __('[Show as slideshow]','nggallery'); // Text for slideshow
	$ngg_options['galTextGallery']		= __('[Show picture list]','nggallery'); // Text for gallery
	$ngg_options['galShowOrder']		= "gallery";					// Show order
	$ngg_options['galSort']				= "pid";						// Sort order
	$ngg_options['galSortDir']			= "ASC";						// Sort direction
	$ngg_options['galUsejQuery']   		= false;						// use the jQuery plugin
	$ngg_options['galNoPages']   		= true;							// use no subpages for gallery
	$ngg_options['galShowDesc']			= "none";						// Show a text below the thumbnail
	$ngg_options['galImgBrowser']   	= false;						// Show ImageBrowser, instead effect

	// Thumbnail Effect
	$ngg_options['thumbEffect']			= "thickbox";  					// select effect
	$ngg_options['thumbCode']			= "class=\"thickbox\" rel=\"%GALLERY_NAME%\""; 
	$ngg_options['thickboxImage']		= "loadingAnimationv3.gif";  	// thickbox Loading Image

	// Watermark settings
	$ngg_options['wmPos']				= "botRight";					// Postion
	$ngg_options['wmXpos']				= 5;  							// X Pos
	$ngg_options['wmYpos']				= 5;  							// Y Pos
	$ngg_options['wmType']				= "text";  						// Type : 'image' / 'text'
	$ngg_options['wmPath']				= "";  							// Path to image
	$ngg_options['wmFont']				= "arial.ttf";  				// Font type
	$ngg_options['wmSize']				= 10;  							// Font Size
	$ngg_options['wmText']				= get_option('blogname');		// Text
	$ngg_options['wmColor']				= "000000";  					// Font Color
	$ngg_options['wmOpaque']			= "100";  						// Font Opaque

	// Image Rotator settings
	$ngg_options['irXHTMLvalid']		= false;
	$ngg_options['irAudio']				= "";
	$ngg_options['irWidth']				= 320; 
	$ngg_options['irHeight']			= 240;
 	$ngg_options['irShuffle']			= true;
 	$ngg_options['irLinkfromdisplay']	= true;
	$ngg_options['irShownavigation']	= false;
	$ngg_options['irShowicons']			= false;
	$ngg_options['irWatermark']			= false;
	$ngg_options['irOverstretch']		= "true";
	$ngg_options['irRotatetime']		= 10;
	$ngg_options['irTransition']		= "random";
	$ngg_options['irKenburns']			= false;
	$ngg_options['irBackcolor']			= "000000";
	$ngg_options['irFrontcolor']		= "FFFFFF";
	$ngg_options['irLightcolor']		= "CC0000";	

	// CSS Style
	$ngg_options['activateCSS']			= true;							// activate the CSS file
	$ngg_options['CSSfile']				= "nggallery.css";  			// set default css filename
	
	update_option('ngg_options', $ngg_options);

}

?>