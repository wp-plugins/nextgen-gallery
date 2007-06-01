<?php

//required database version
$ngg_db_version = "0.33";

function nggallery_install () {
	
   	global $wpdb;
   	global $ngg_db_version;

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

   	$nggpictures					= $wpdb->prefix . 'ngg_pictures';
	$nggallery						= $wpdb->prefix . 'ngg_gallery';
	$nggalbum						= $wpdb->prefix . 'ngg_album';
   
	if($wpdb->get_var("show tables like '$nggpictures'") != $nggpictures) {
      
		$sql = "CREATE TABLE " . $nggpictures . " (
		pid MEDIUMINT(9) NOT NULL AUTO_INCREMENT ,
		galleryid MEDIUMINT(9) DEFAULT '0' NOT NULL ,
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
		gid MEDIUMINT(9) NOT NULL AUTO_INCREMENT ,
		name VARCHAR(255) NOT NULL ,
		path MEDIUMTEXT NULL ,
		title MEDIUMTEXT NULL ,
		description MEDIUMTEXT NULL ,
		pageid SMALLINT(6) NULL DEFAULT '0' ,
		previewpic SMALLINT(6) NULL DEFAULT '0' ,
		PRIMARY KEY gid (gid)
		);";
	
      dbDelta($sql);
   }

	if($wpdb->get_var("show tables like '$nggalbum'") != $nggalbum) {
      
		$sql = "CREATE TABLE " . $nggalbum . " (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT ,
		name VARCHAR(255) NOT NULL ,
		sortorder LONGTEXT NOT NULL,
		PRIMARY KEY id (id)
		);";
	
      dbDelta($sql);
    }
   
   // update routine
    $installed_ver = get_option( "ngg_db_version" );
	if( $installed_ver != $ngg_db_version ) {

		// do the update (hopefully I didn't required this sometimes)	
		update_option( "ngg_db_version", $ngg_db_version );
	}

}

function ngg_default_options() {

	$ngg_options[gallerypath]		= "wp-content/gallery/";  		// set default path to the gallery
	$ngg_options[scanfolder]		= false;						// search for new images
	$ngg_options[deleteImg]			= false;						// delete Images
	
	$ngg_options[thumbwidth]		= 100;  						// Thumb Width
	$ngg_options[thumbheight]		= 75;  							// Thumb height
	$ngg_options[thumbfix]			= true;							// Fix the dimension
	$ngg_options[thumbcrop]			= false;						// Crop square thumbnail
	$ngg_options[thumbquality]		= 100;  						// Thumb Quality
	$ngg_options[thumbResampleMode]	= 3;  							// Resample speed value 1 - 5
	
	// Image Settings
	$ngg_options[imgResize]			= false;						// Activate resize
	$ngg_options[imgWidth]			= 800;  						// Image Width
	$ngg_options[imgHeight]			= 600;  						// Image height
	$ngg_options[imgQuality]		= 85;							// Image Quality
	$ngg_options[imgResampleMode]	= 4;  							// Resample speed value 1 - 5
	$ngg_options[imgSinglePicLink]	= false;  						// Add a link to the full size picture
	
	// Gallery Settings
	$ngg_options[galImages]			= "20";		  					// Number Of images per page
	$ngg_options[galShowSlide]		= true;							// Show slideshow
	$ngg_options[galTextSlide]		= __('[Show as slideshow]','nggallery'); // Text for slideshow
	$ngg_options[galTextGallery]	= __('[Show picture list]','nggallery'); // Text for gallery
	$ngg_options[galShowOrder]		= "gallery";					// Show order
	$ngg_options[galSort]			= "pid";						// Sort order

	// Thumbnail Effect
	$ngg_options[thumbEffect]		= "thickbox";  					// select effect
	$ngg_options[thumbCode]			= "class=\"thickbox\" rel=\"%GALLERY_NAME%\""; 
	$ngg_options[thickboxImage]		= "loadingAnimationv3.gif";  	// thickbox Loading Image

	// Watermark settings
	$ngg_options[wmPos]				= "botRight";					// Postion
	$ngg_options[wmXpos]			= 5;  							// X Pos
	$ngg_options[wmYpos]			= 5;  							// Y Pos
	$ngg_options[wmType]			= "text";  						// Type : 'image' / 'text'
	$ngg_options[wmPath]			= "";  							// Path to image
	$ngg_options[wmFont]			= "arial.ttf";  				// Font type
	$ngg_options[wmSize]			= 10;  							// Font Size
	$ngg_options[wmText]			= get_option('blogname');		// Text
	$ngg_options[wmColor]			= "000000";  					// Font Color
	$ngg_options[wmOpaque]			= "100";  						// Font Opaque

	// Image Rotator settings
	
	$ngg_options[irXHTMLvalid]		= false;
	$ngg_options[irWidth]			= 320; 
	$ngg_options[irHeight]			= 240;
 	$ngg_options[irShuffle]			= true;
 	$ngg_options[irLinkfromdisplay]	= true;
	$ngg_options[irShownavigation]	= false;
	$ngg_options[irShowicons]		= false;
	$ngg_options[irOverstretch]		= "true";
	$ngg_options[irRotatetime]		= 10;
	$ngg_options[irTransition]		= "random";
	$ngg_options[irKenburns]		= false;
	$ngg_options[irBackcolor]		= "000000";
	$ngg_options[irFrontcolor]		= "FFFFFF";
	$ngg_options[irLightcolor]		= "CC0000";	

	// CSS Style
	$ngg_options[activateCSS]		= true;							// activate the CSS file
	$ngg_options[CSSfile]			= "nggallery.css";  			// set default css filename
	
	update_option('ngg_options', $ngg_options);

}

?>