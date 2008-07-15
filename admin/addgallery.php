<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
	
	// sometimes a error feedback is better than a white screen
	ini_set('error_reporting', E_ALL ^ E_NOTICE);

	function nggallery_admin_add_gallery()  {

	global $wpdb;
	$ngg_options = get_option('ngg_options');
	
	// same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
	$filepath    = get_option('siteurl') . '/wp-admin/admin.php?page='.$_GET['page'];
	
	// link for the flash file
	$swf_upload_link = NGGALLERY_URLPATH . 'admin/upload.php';
	$swf_upload_link = wp_nonce_url($swf_upload_link, 'ngg_swfupload');
	//flash doesn't seem to like encoded ampersands, so convert them back here
	$swf_upload_link = str_replace('&#038;', '&', $swf_upload_link);

	$defaultpath = $ngg_options['gallerypath'];	

	if ($_POST['addgallery']){
		check_admin_referer('ngg_addgallery');
		$newgallery = attribute_escape($_POST['galleryname']);
		if (!empty($newgallery))
			nggAdmin::create_gallery($newgallery, $defaultpath);
	}
	
	if ($_POST['zipupload']){
		check_admin_referer('ngg_addgallery');
		if ($_FILES['zipfile']['error'] == 0) 
			$messagetext = nggAdmin::import_zipfile($defaultpath);
		else
			nggallery::show_error(__('Upload failed!','nggallery'));
	}
	
	if ($_POST['importfolder']){
		check_admin_referer('ngg_addgallery');
		$galleryfolder = $_POST['galleryfolder'];
		if ((!empty($galleryfolder)) AND ($defaultpath != $galleryfolder))
			$messagetext = nggAdmin::import_gallery($galleryfolder);
	}
	
	if ($_POST['uploadimage']){
		check_admin_referer('ngg_addgallery');
		if ($_FILES['MF__F_0_0']['error'] == 0) {
			$messagetext = nggAdmin::upload_images();
		}
		else
			nggallery::show_error(__('Upload failed!','nggallery'));	
	}
	
	if (isset($_POST['swf_callback'])){
		if ($_POST['galleryselect'] == "0" )
			nggallery::show_error(__('No gallery selected !','nggallery'));
		else {
			// get the path to the gallery
			$galleryID = (int) $_POST['galleryselect'];
			$gallerypath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$galleryID' ");
			$messagetext = nggAdmin::import_gallery($gallerypath);
		}	
	}

	if ( isset($_POST['disable_flash']) ){
		check_admin_referer('ngg_addgallery');
		$ngg_options['swfUpload'] = false;	
		update_option('ngg_options', $ngg_options);
	}

	if ( isset($_POST['enable_flash']) ){
		check_admin_referer('ngg_addgallery');
		$ngg_options['swfUpload'] = true;	
		update_option('ngg_options', $ngg_options);
	}
	
	//get maximum allowable size from php.ini
	//thx to Whoismanu PhotoQ / M.Flury 
		$max_upl_size = strtolower( ini_get( 'upload_max_filesize' ) );
		$max_upl_kbytes = 0;
		if (strpos($max_upl_size, 'k') !== false)
			$max_upl_kbytes = $max_upl_size;
		if (strpos($max_upl_size, 'm') !== false)
			$max_upl_kbytes = $max_upl_size * 1024;
		if (strpos($max_upl_size, 'g') !== false)
			$max_upl_kbytes = $max_upl_size * 1024 * 1024;
			
	// message windows
	if(!empty($messagetext)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$messagetext.'</p></div>'; }
	?>
   
	<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
    <!--[if lte IE 7]>
    <link rel="stylesheet" href="<?php echo NGGALLERY_URLPATH ?>admin/css/jquery.tabs-ie.css" type="text/css" media="projection, screen"/>
    <![endif]-->
	
	<?php if($ngg_options['swfUpload']) { ?>
	<!-- SWFUpload script -->
	<script type="text/javascript">
		var ngg_swf_upload;
			
		window.onload = function () {
			ngg_swf_upload = new SWFUpload({
				// Backend settings
				upload_url : "<?php echo $swf_upload_link; ?>",
				flash_url : "<?php echo NGGALLERY_URLPATH; ?>admin/js/swfupload_f9.swf",
								
				// File Upload Settings
				file_size_limit : "<?php echo $max_upl_kbytes;?> kb", // can use in WP2.5 wp_max_upload_size()
				file_types : "*.jpg;*.gif;*.png",
				file_types_description : "<?php _e('Image Files', 'nggallery') ;?>",
				
				// Queue handler
				file_queued_handler : fileQueued,
				
				// Upload handler
				upload_start_handler : uploadStart,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
				
				post_params : {
					"user_cookie" : "<?php echo $_COOKIE[USER_COOKIE]; ?>",
					"pass_cookie" : "<?php echo $_COOKIE[PASS_COOKIE]; ?>",
					"galleryselect" : "0"
				},
				
				// i18names
				custom_settings : {
					"remove" : "<?php _e('remove', 'nggallery') ;?>",
					"browse" : "<?php _e('Browse...', 'nggallery') ;?>",
					"upload" : "<?php _e('Upload images', 'nggallery') ;?>"
				},

				// Debug settings
				debug: false
				
			});
			
			// on load change the upload to swfupload
			initSWFUpload();
			
		};
	</script>
	
	<div class="wrap" id="progressbar-wrap">
		<div class="progressborder">
			<div class="progressbar" id="progressbar">
				<span>0%</span>
			</div>
		</div>
	</div>
	
	<?php } else { ?>
	<!-- MultiFile script -->
	<script type="text/javascript">	
		jQuery(function(){
			jQuery('#imagefiles').MultiFile({
				STRING: {
			    	remove:'<?php _e('remove', 'nggallery') ;?>'
  				}
		 	});
		});
	</script>
	<?php } ?>
	<!-- jQuery Tabs script -->
	<script type="text/javascript">
		jQuery(function() {
			jQuery('#slider').tabs({ fxFade: true, fxSpeed: 'fast' });	
		});
	</script>
		
	<div id="slider" class="wrap">
	
		<ul id="tabs">
			<li><a href="#addgallery"><?php _e('Add new gallery', 'nggallery') ;?></a></li>
			<?php if ( wpmu_enable_function('wpmuZipUpload') ) { ?>
			<li><a href="#zipupload"><?php _e('Upload a Zip-File', 'nggallery') ;?></a></li>
			<?php } 
			if (!IS_WPMU) {?>
			<li><a href="#importfolder"><?php _e('Import image folder', 'nggallery') ;?></a></li>
			<?php } ?>
			<li><a href="#uploadimage"><?php _e('Upload Images', 'nggallery') ;?></a></li>
		</ul>

		<!-- create gallery -->
		<div id="addgallery">
		<h2><?php _e('Add new gallery', 'nggallery') ;?></h2>
			<form name="addgallery" id="addgallery_form" method="POST" action="<?php echo $filepath; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<fieldset class="options">
				<table class="optiontable"> 
				<tr valign="top"> 
					<th scope="row"><?php _e('New Gallery', 'nggallery') ;?>:</th> 
					<td><input type="text" size="35" name="galleryname" value="" /><br />
					<?php if(!IS_WPMU) { ?>
					<?php _e('Create a new , empty gallery below the folder', 'nggallery') ;?>  <strong><?php echo $defaultpath ?></strong><br />
					<?php } ?>
					<i>( <?php _e('Allowed characters for file and folder names are', 'nggallery') ;?>: a-z, A-Z, 0-9, -, _ )</i></td>
				</tr>
				</table>
				<div class="submit"><input type="submit" name= "addgallery" value="<?php _e('Add gallery', 'nggallery') ;?>"/></div>
			</fieldset>
			</form>
		</div>
		<?php if ( wpmu_enable_function('wpmuZipUpload')) { ?>
		<!-- zip-file operation -->
		<div id="zipupload">
		<h2><?php _e('Upload a Zip-File', 'nggallery') ;?></h2>
			<form name="zipupload" id="zipupload_form" method="POST" enctype="multipart/form-data" action="<?php echo $filepath.'#zipupload'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<fieldset class="options">
				<table class="optiontable"> 
				<tr valign="top"> 
					<th scope="row"><?php _e('Select Zip-File', 'nggallery') ;?>:</th> 
					<td><input type="file" name="zipfile" id="zipfile" size="35" class="uploadform"/><br />
					<?php _e('Upload a zip file with images', 'nggallery') ;?></td> 
				</tr>
				<tr valign="top"> 
					<th scope="row"><?php _e('in to', 'nggallery') ;?></th> 
					<td><select name="zipgalselect">
					<option value="0" ><?php _e('a new gallery', 'nggallery') ?></option>
					<?php
						$gallerylist = $wpdb->get_results("SELECT * FROM $wpdb->nggallery ORDER BY gid ASC");
						if(is_array($gallerylist)) {
							foreach($gallerylist as $gallery) {
								echo '<option value="'.$gallery->name.'" >'.$gallery->name.' | '.$gallery->title.'</option>'."\n";
							}
						}
					?>
					</select>
					<br /><?php echo _e('Note : The upload limit on your server is ','nggallery') . "<strong>" . ini_get('upload_max_filesize') . "Byte</strong>\n"; ?>
					<br /><?php if ( (IS_WPMU) && wpmu_enable_function('wpmuQuotaCheck') ) display_space_usage(); ?></td> 
				</tr> 
				<tr>
					<th><?php _e('Add Metadata :', 'nggallery') ;?></th>
					<td><input type="checkbox" name="addmetadata" value="1" checked="checked" />
					<?php _e('Import EXIF, IPTC or XMP data (if available)', 'nggallery') ;?></td>
				</tr>
				</table>
				<div class="submit"> <input type="submit" name= "zipupload" value="<?php _e('Start upload', 'nggallery') ;?>"/></div>
			</fieldset>
			</form>
		</div>
		<?php }
		if (!IS_WPMU) {?>
		<!-- import folder -->
		<div id="importfolder">
		<h2><?php _e('Import image folder', 'nggallery') ;?></h2>
			<form name="importfolder" id="importfolder_form" method="POST" action="<?php echo $filepath.'#importfolder'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<fieldset class="options">
				<table class="optiontable"> 
				<tr valign="top"> 
					<th scope="row"><?php _e('Import from Server path:', 'nggallery') ;?><br /><code><?php echo WINABSPATH; ?></code></th> 
					<td><br /><input type="text" size="35" name="galleryfolder" value="<?php echo$defaultpath; ?>" /><br />
					<?php _e('Import a folder with images. Please note :', 'nggallery') ;?><br /> 
					<?php _e('For safe-mode = ON you need to add the subfolder thumbs manually', 'nggallery') ;?></td> 
				</tr>
				<tr>
					<th><?php _e('Add Metadata :', 'nggallery') ;?></th>
					<td><input type="checkbox" name="addmetadata" value="1" checked="checked" />
					<?php _e('Import EXIF, IPTC or XMP data (if available)', 'nggallery') ;?></td>
				</tr>
				</table>
				<div class="submit"> <input type="submit" name= "importfolder" value="<?php _e('Import folder', 'nggallery') ;?>"/></div>
			</fieldset>
			</form>
		</div>
		<?php } ?> 
		<!-- upload images -->
		<div id="uploadimage">
		<h2><?php _e('Upload Images', 'nggallery') ;?></h2>
			<form name="uploadimage" id="uploadimage_form" method="POST" enctype="multipart/form-data" action="<?php echo $filepath.'#uploadimage'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<fieldset class="options">
				<table class="optiontable"> 
				<tr valign="top"> 
					<th scope="row"><?php _e('Upload image', 'nggallery') ;?></th>
					<td><input type="file" name="imagefiles" id="imagefiles" size="35" class="imagefiles"/></td>
				</tr> 
				<tr valign="top"> 
					<th scope="row"><?php _e('in to', 'nggallery') ;?></th> 
					<td><select name="galleryselect" id="galleryselect">
					<option value="0" ><?php _e('Choose gallery', 'nggallery') ?></option>
					<?php
						$gallerylist = $wpdb->get_results("SELECT * FROM $wpdb->nggallery ORDER BY gid ASC");
						if(is_array($gallerylist)) {
							foreach($gallerylist as $gallery) {
								echo '<option value="'.$gallery->gid.'" >'.$gallery->name.' | '.$gallery->title.'</option>'."\n";
							}
						}
					?>
					</select>
					<br /><?php echo _e('Note : The upload limit on your server is ','nggallery') . "<strong>" . ini_get('upload_max_filesize') . "Byte</strong>\n"; ?>
					<br /><?php if ((IS_WPMU) && wpmu_enable_function('wpmuQuotaCheck')) display_space_usage(); ?></td> 
				</tr> 
				<tr>
					<th><?php _e('Add Metadata :', 'nggallery') ;?></th>
					<td><input type="checkbox" name="addmetadata" value="1" checked="checked" />
					<?php _e('Import EXIF, IPTC or XMP data (if available)', 'nggallery') ;?></td>
				</tr>
				</table>
				<div class="submit">
					<?php if ($ngg_options['swfUpload']) { ?>
					<input type="submit" name="disable_flash" id="disable_flash" title="<?php _e('The batch upload requires Adobe Flash 9, disable it if you have problems','nggallery') ?>" value="<?php _e('Disable flash upload', 'nggallery') ;?>" />
					<?php } else { ?>
					<input type="submit" name="enable_flash" id="enable_flash" title="<?php _e('Upload multiple files at once by ctrl/shift-selecting in dialog','nggallery') ?>" value="<?php _e('Enable flash based upload', 'nggallery') ;?>" />
					<?php } ?>
					<input type="submit" name="uploadimage" id="uploadimage_btn" value="<?php _e('Upload images', 'nggallery') ;?>" />
				</div>
			</fieldset>
			</form>
		</div>
	</div>
		
	<?php
	}
	
?>