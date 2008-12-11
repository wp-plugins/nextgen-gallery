<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * ngg_upgrade() - update routine for older version
 * 
 * @return Success message
 */
function ngg_upgrade() {
	
	global $wpdb, $user_ID;
	
	$nggpictures					= $wpdb->prefix . 'ngg_pictures';
	$nggallery						= $wpdb->prefix . 'ngg_gallery';

	// get the current user ID
	get_currentuserinfo();

	// Be sure that the tables exist
	if($wpdb->get_var("show tables like '$nggpictures'") == $nggpictures) {

		$installed_ver = get_option( "ngg_db_version" );

		// v0.33 -> v.071
		if (version_compare($installed_ver, '0.71', '<')) {
			$wpdb->query("ALTER TABLE ".$nggpictures." CHANGE pid pid BIGINT(20) NOT NULL AUTO_INCREMENT ");
			$wpdb->query("ALTER TABLE ".$nggpictures." CHANGE galleryid galleryid BIGINT(20) NOT NULL ");
			$wpdb->query("ALTER TABLE ".$nggallery." CHANGE gid gid BIGINT(20) NOT NULL AUTO_INCREMENT ");
			$wpdb->query("ALTER TABLE ".$nggallery." CHANGE pageid pageid BIGINT(20) NULL DEFAULT '0'");
			$wpdb->query("ALTER TABLE ".$nggallery." CHANGE previewpic previewpic BIGINT(20) NULL DEFAULT '0'");
			$wpdb->query("ALTER TABLE ".$nggallery." CHANGE gid gid BIGINT(20) NOT NULL AUTO_INCREMENT ");
			$wpdb->query("ALTER TABLE ".$nggallery." CHANGE description galdesc MEDIUMTEXT NULL");
		}
		// v0.71 -> v0.84
		if (version_compare($installed_ver, '0.84', '<')) {
			$wpdb->query("ALTER TABLE ".$nggpictures." ADD sortorder BIGINT(20) DEFAULT '0' NOT NULL AFTER exclude");
		}

		// v0.84 -> v0.95
		if (version_compare($installed_ver, '0.95', '<')) {
			// first add the author field and set it to the current administrator
			$wpdb->query("ALTER TABLE ".$nggallery." ADD author BIGINT(20) NOT NULL DEFAULT '$user_ID' AFTER previewpic");
			// switch back to zero
			$wpdb->query("ALTER TABLE ".$nggallery." CHANGE author author BIGINT(20) NOT NULL DEFAULT '0'");
		}

		// v0.95 -> v0.99 
		if (version_compare($installed_ver, '0.96', '<')) {
			// Convert into WordPress Core taxonomy scheme
			ngg_convert_tags();
			// Drop tables, we don't need them anymore
			$wpdb->query("DROP TABLE " . $wpdb->prefix . "ngg_tags");
			$wpdb->query("DROP TABLE " . $wpdb->prefix . "ngg_pic2tags");
			ngg_convert_filestructure();
			
			// New capability for administrator role
			$role = get_role('administrator');
			$role->add_cap('NextGEN Manage tags');
			
			// Add new option
			$ngg_options = get_option('ngg_options');
			$ngg_options['graphicLibrary']  = 'gd';
			update_option('ngg_options', $ngg_options);	
			
		}
		
		if (version_compare($installed_ver, '0.97', '<')) {
			$wpdb->query("ALTER TABLE ".$nggpictures." ADD imagedate DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER alttext");
			ngg_import_date_time();
		}
		
		update_option( "ngg_db_version", NGG_DBVERSION );
		return __('Update database structure', 'nggallery');
	}
}

/**
 * ngg_convert_tags() - Import the tags into the wp tables (only required for pre V1.00 versions)
 * 
 * @return Success Message
 */
function ngg_convert_tags() {
	global $wpdb, $wp_taxonomies;
		
	// get the obsolete tables
	$wpdb->nggtags						= $wpdb->prefix . 'ngg_tags';
	$wpdb->nggpic2tags					= $wpdb->prefix . 'ngg_pic2tags';
	
	$picturelist = $wpdb->get_col("SELECT pid FROM $wpdb->nggpictures");
	if ( is_array($picturelist) ) {
		foreach($picturelist as $id) {
			$tags = array();
			$tagarray = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggpic2tags AS t INNER JOIN $wpdb->nggtags AS tt ON t.tagid = tt.id WHERE t.picid = '$id' ORDER BY tt.slug ASC ");
			if (!empty($tagarray)){
				foreach($tagarray as $element) {
					$tags[$element->id] = $element->name;
				}
				wp_set_object_terms($id, $tags, 'ngg_tag');
			}
		}
	}
}

/**
 * ngg_convert_filestructure() - converter for old thumnail folder structure
 * 
 * @return void
 */
function ngg_convert_filestructure() {
	global $wpdb;
	
	$gallerylist = $wpdb->get_results("SELECT * FROM $wpdb->nggallery ORDER BY gid ASC", OBJECT_K);
	if ( is_array($gallerylist) ) {
		$errors = array();
		foreach($gallerylist as $gallery) {
			$gallerypath = WINABSPATH.$gallery->path;

			// old mygallery check, convert the wrong folder/ file name now
			if (@is_dir($gallerypath."/tumbs")) {
				if ( !rename($gallerypath."/tumbs", $gallerypath."/thumbs") )
					$errors[] = $gallery->path . "/thumbs";
				// read list of images
				$imageslist = nggAdmin::scandir($gallerypath."/thumbs");
				if ( !empty($imageslist)) {
					foreach($imageslist as $image) {
						$purename = substr($image, 4);
						if ( !rename($gallerypath."/thumbs/".$image, $gallerypath."/thumbs/"."thumbs_".$purename ))
							$errors[] = $gallery->path . "/thumbs/"."thumbs_".$purename ;
					}
				}
			}
		}
		if (!empty($errors)) {
			echo "<div class='error_inline'><p>". __('Some folders/files could not renamed, please recheck the permission and rescan the folder in the manage gallery section.', 'nggallery') ."</p>";
			foreach($errors as $value) {
				echo __('Rename failed', 'nggallery') . " : <strong>" . $value . "</strong><br />\n";
			}
			echo "</div>";
		}
	}
}

/**
 * ngg_import_date_time() - Read the timestamp from exif and insert it into the database
 * 
 * @return void
 */
function ngg_import_date_time() {
	global $wpdb;
	
	$imagelist = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid ORDER BY tt.pid ASC");
	if ( is_array($imagelist) ) {
		foreach ($imagelist as $image) {
			$picture = new nggImage($image, $image);
			$meta = new nggMeta($picture->imagePath);
			$date = $meta->get_date_time();
			$wpdb->query("UPDATE $wpdb->nggpictures SET imagedate = '$date' WHERE pid = '$picture->pid'");
		}		
	}	
}

/**
 * nggallery_upgrade_page() - This page showsup , when the database version doesn't fir to the script NGG_DBVERSION constant.
 * 
 * @return Upgrade Message
 */
function nggallery_upgrade_page()  {	
	$filepath    = admin_url() . 'admin.php?page='.$_GET['page'];
	
	if ($_GET['upgrade'] == 'now') {
		nggallery_start_upgrade($filepath);
		return;
	}
?>
<div class="wrap">
	<h2><?php _e('Upgrade NextGEN Gallery', 'nggallery') ;?></h2>
	<p><?php _e('The script detect that you upgrade from a older version.', 'nggallery') ;?>
	   <?php _e('Your database tables for NextGEN Gallery is out-of-date, and must be upgraded before you can continue.', 'nggallery'); ?>
       <?php _e('If you would like to downgrade later, please make first a complete backup of your database and the images.', 'nggallery') ;?></p>
	<p><?php _e('The upgrade process may take a while, so please be patient.', 'nggallery'); ?></p>
	<h3><a href="<?php echo $filepath;?>&amp;upgrade=now"><?php _e('Start upgrade now', 'nggallery'); ?>...</a></h3>      
</div>
<?php
}

/**
 * nggallery_start_upgrade() - Proceed the upgrade routine
 * 
 * @param mixed $filepath
 * @return void
 */
function nggallery_start_upgrade($filepath) {
	global $wpdb;
?>
<div class="wrap">
	<h2><?php _e('Upgrade NextGEN Gallery', 'nggallery') ;?></h2>
	<p><?php echo ngg_upgrade();?></p>
	<p><?php _e('Upgrade sucessfull', 'nggallery') ;?></p>
	<h3><a href="<?php echo $filepath;?>"><?php _e('Continue', 'nggallery'); ?>...</a></h3>
</div>
<?php
} 
?>