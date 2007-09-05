<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	include_once(NGGALLERY_ABSPATH.'/lib/thumbnail.inc.php');

	//TODO: Check better upload form like http://digitarald.de/project/fancyupload/
	
	function nggallery_admin_add_gallery()  {

	global $wpdb;
	$ngg_options = get_option('ngg_options');

	$defaultpath = $ngg_options['gallerypath'];	
	
	if ($_POST['addgallery']){
		check_admin_referer('ngg_addgallery');
		$newgallery = attribute_escape($_POST['galleryname']);
		if (!empty($newgallery))
			$messagetext = ngg_create_gallery($newgallery, $defaultpath);
	}
	
	if ($_POST['zipupload']){
		check_admin_referer('ngg_addgallery');
		if ($_FILES['zipfile']['error'] == 0) 
			$messagetext = ngg_import_zipfile($defaultpath);
		else
			nggallery::show_error(__('Upload failed!','nggallery'));
	}
	
	if ($_POST['importfolder']){
		check_admin_referer('ngg_addgallery');
		$galleryfolder = $_POST['galleryfolder'];
		if ((!empty($galleryfolder)) AND ($defaultpath != $galleryfolder))
			$messagetext = ngg_import_gallery($galleryfolder);
	}
	
	if ($_POST['uploadimage']){
		check_admin_referer('ngg_addgallery');
		if ($_FILES['MF__F_0_0']['error'] == 0) {
			$messagetext = ngg_upload_images($defaultpath);
		}
		else
			nggallery::show_error(__('Upload failed!','nggallery'));	
	}
			
	// message windows
	if(!empty($messagetext)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$messagetext.'</p></div>'; }

	?>
	
	<link rel="stylesheet" href="<?php echo NGGALLERY_URLPATH ?>admin/js/jquery.tabs.css" type="text/css" media="print, projection, screen"/>
    <!-- Additional IE/Win specific style sheet (Conditional Comments) -->
    <!--[if lte IE 7]>
    <link rel="stylesheet" href="<?php echo NGGALLERY_URLPATH ?>admin/js/jquery.tabs-ie.css" type="text/css" media="projection, screen"/>
    <![endif]-->

	<script type="text/javascript">
		jQuery(function() {
			jQuery('#slider').tabs({ fxFade: true, fxSpeed: 'fast' });	
		});
		
		jQuery(function(){
			jQuery('#imagefiles').MultiFile({
				STRING: {
			    	remove:'<?php _e('remove', 'nggallery') ;?>'
  				}
		 	});
		});

	</script>
	
	<div id="slider" class="wrap">
	
		<ul id="tabs">
			<li><a href="#addgallery"><?php _e('Add new gallery', 'nggallery') ;?></a></li>
			<?php if (!SAFE_MODE) { ?>
			<li><a href="#zipupload"><?php _e('Upload a Zip-File', 'nggallery') ;?></a></li>
			<?php } ?>
			<li><a href="#importfolder"><?php _e('Import image folder', 'nggallery') ;?></a></li>
			<li><a href="#uploadimage"><?php _e('Upload Images', 'nggallery') ;?></a></li>
		</ul>

		<!-- create gallery -->
		<div id="addgallery">
		<h2><?php _e('Add new gallery', 'nggallery') ;?></h2>
			<form name="addgallery" id="addgallery" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<fieldset class="options">
				<table class="optiontable"> 
				<tr valign="top"> 
					<th scope="row"><?php _e('New Gallery', 'nggallery') ;?>:</th> 
					<td><input type="text" size="35" name="galleryname" value="" /><br />
					<?php _e('Create a new , empty gallery below the folder', 'nggallery') ;?>  <strong><?php echo $defaultpath ?></strong><br />
					<i>( <?php _e('Allowed characters for file and folder names are', 'nggallery') ;?>: a-z, A-Z, 0-9, -, _ )</i></td>
				</tr>
				</table>
				<div class="submit"><input type="submit" name= "addgallery" value="<?php _e('Add gallery', 'nggallery') ;?>"/></div>
			</fieldset>
			</form>
		</div>
		<!-- zip-file operation -->
		<div id="zipupload">
		<h2><?php _e('Upload a Zip-File', 'nggallery') ;?></h2>
			<form name="zipupload" id="zipupload" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#zipupload'; ?>" accept-charset="utf-8" >
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
					<br /><?php echo _e('Note : The upload limit on your server is ','nggallery') . "<strong>" . ini_get('upload_max_filesize') . "Byte</strong>\n"; ?></td> 
				</tr> 
				</table>
				<div class="submit"> <input type="submit" name= "zipupload" value="<?php _e('Start upload', 'nggallery') ;?>"/></div>
			</fieldset>
			</form>
		</div>
		<!-- import folder -->
		<div id="importfolder">
		<h2><?php _e('Import image folder', 'nggallery') ;?></h2>
			<form name="importfolder" id="importfolder" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#importfolder'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<fieldset class="options">
				<table class="optiontable"> 
				<tr valign="top"> 
					<th scope="row"><?php _e('Import from Server path:', 'nggallery') ;?><br /><code><?php echo WINABSPATH; ?></code></th> 
					<td><br /><input type="text" size="35" name="galleryfolder" value="<?php echo$defaultpath; ?>" /><br />
					<?php _e('Import a folder with images. Please note :', 'nggallery') ;?><br /> 
					<?php _e('For save_mode = ON you need to add the subfolder thumbs manually', 'nggallery') ;?></td> 
				</tr>
				</table>
				<div class="submit"> <input type="submit" name= "importfolder" value="<?php _e('Import folder', 'nggallery') ;?>"/></div>
			</fieldset>
			</form>
		</div> 
		<!-- upload images -->
		<div id="uploadimage">
		<h2><?php _e('Upload Images', 'nggallery') ;?></h2>
			<form name="uploadimage" id="uploadimage" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#uploadimage'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<fieldset class="options">
				<table class="optiontable"> 
				<tr valign="top"> 
					<th scope="row"><?php _e('Upload image', 'nggallery') ;?></th> 
					<td><input type="file" name="imagefiles" id="imagefiles" size="35" class="imagefiles"/></td> 
				</tr> 
				<tr valign="top"> 
					<th scope="row"><?php _e('in to', 'nggallery') ;?></th> 
					<td><select name="galleryselect">
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
					<br /><?php echo _e('Note : The upload limit on your server is ','nggallery') . "<strong>" . ini_get('upload_max_filesize') . "Byte</strong>\n"; ?></td> 
				</tr> 
				</table>
				<div class="submit"><input type="submit" name= "uploadimage" value="<?php _e('Upload images', 'nggallery') ;?>"/></div>
			</fieldset>
			</form>
		</div>
	</div>
		
	<?php
	}
	
	// **************************************************************
	function ngg_create_gallery($gallerytitle, $defaultpath) {
		// create a new gallery & folder
		global $wpdb;
		
		$myabspath = str_replace("\\","/",ABSPATH);  // required for windows

		//cleanup pathname
		$galleryname = sanitize_title($gallerytitle);
		$nggpath = $defaultpath.$galleryname;
		
		if (empty($galleryname)) return '<font color="red">'.__('No valid gallery name!', 'nggallery'). '</font>';	

		// check for main folder
		if ( !file_exists(($myabspath.$defaultpath)) ) {
			$txt  = __('Directory', 'nggallery').' <strong>'.$defaultpath.'</strong> '.__('didn\'t exist. Please create first the main gallery folder ', 'nggallery').'!<br />';
			$txt .= __('Check this link, if you didn\'t know how to set the permission :', 'nggallery').' <a href="http://codex.wordpress.org/Changing_File_Permissions">http://codex.wordpress.org/Changing_File_Permissions</a> ';
			nggallery::show_error($txt);
			return;
		}

		// check for permission settings
		if ( substr(decoct(@fileperms($myabspath.$defaultpath)),1) != decoct(NGGFOLDER_PERMISSION) ) {
			$txt  = __('Directory', 'nggallery').' <strong>'.$defaultpath.'</strong> '.__('didn\'t have the permissions ', 'nggallery').decoct(NGGFOLDER_PERMISSION).'!<br />';
			$txt .= __('Check this link, if you didn\'t know how to set the permission :', 'nggallery').' <a href="http://codex.wordpress.org/Changing_File_Permissions">http://codex.wordpress.org/Changing_File_Permissions</a> ';
			nggallery::show_error($txt);
			return;
		}
		
		// avoid double creation	
		if (is_dir($myabspath.$nggpath)) {
			nggallery::show_error(__('Directory', 'nggallery').' <strong>'.$nggpath.'</strong> '.__('already exists!', 'nggallery'));
			return; 
		}
		
		// create new directories
		if (!SAFE_MODE) {
			if (!@mkdir ($myabspath.$nggpath,NGGFOLDER_PERMISSION)) return  ('<font color="red">'.__('Unable to create directory ', 'nggallery').$nggpath.'!</font>');
			if (!@chmod ($myabspath.$nggpath,NGGFOLDER_PERMISSION)) return  ('<font color="red">'.__('Unable to set directory permissions ', 'nggallery').$nggpath.'!</font>');
			if (!@mkdir ($myabspath.$nggpath.'/thumbs',NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to create directory ', 'nggallery').$nggpath.'/thumbs !</font>');
			if (!@chmod ($myabspath.$nggpath.'/thumbs',NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to set directory permissions', 'nggallery').$nggpath.'/thumbs !</font>');
		} else {
			$safemode  = '<br /><font color="green">'.__('The server setting Safe-Mode is on !', 'nggallery');	
			$safemode .= '<br />'.__('Please create directory', 'nggallery').' <strong>'.$nggpath.'</strong> ';	
			$safemode .= __('and the thumbnails directory', 'nggallery').' <strong>'.$nggpath.'/thumbs</strong> '.__('with permission 777 manually !', 'nggallery').'</font>';	
		}
		$result=$wpdb->get_var("SELECT name FROM $wpdb->nggallery WHERE name = '$galleryname' ");
		if ($result) {
			nggallery::show_error(__('Gallery', 'nggallery').' <strong>'.$galleryname.'</strong> '.__('already exists', 'nggallery'));
			return; 			
		} else { 
			$result = $wpdb->query("INSERT INTO $wpdb->nggallery (name, path, title) VALUES ('$galleryname', '$nggpath', '$gallerytitle') ");
			if ($result) nggallery::show_message(__('Gallery', 'nggallery').' <strong>'.$wpdb->insert_id." : ".$galleryname.'</strong> '.__('successfully created!','nggallery')."<br />".__('You can show this gallery with the tag','nggallery').'<strong> [gallery='.$wpdb->insert_id.']</strong>'.$safemode); 
			return;
		} 
	}
	
	// **************************************************************
	function ngg_import_gallery($galleryfolder) {
		// ** $galleryfolder contains relative path
		
		//TODO: Check permission of existing thumb folder & images
		
		// import a existing folder
		global $wpdb;
		
		// remove trailing slash at the end, if somebody use it
		if (substr($galleryfolder, -1) == '/') $galleryfolder = substr($galleryfolder, 0, -1);
		$gallerypath = WINABSPATH.$galleryfolder;
		
		if (!is_dir($gallerypath)) return '<font color="red">'.__('Directory', 'nggallery').' <strong>'.$gallerypath.'</strong> '.__('doesn&#96;t exist', 'nggallery').'!</font>';
		
		// read list of images
		$new_imageslist = ngg_scandir($gallerypath);
		if (empty($new_imageslist)) return '<font color="blue">'.__('Directory', 'nggallery').' <strong>'.$gallerypath.'</strong> '.__('contains no pictures', 'nggallery').'!</font>';

		// create thumbnail folder
		$check_thumbnail_folder = nggallery::get_thumbnail_folder($gallerypath);
		if (!$check_thumbnail_folder) {
			if (SAFE_MODE) return '<font color="red">'.__('Thumbnail Directory', 'nggallery').' <strong>'.$gallerypath.'/thumbs</strong> '.__('doesn&#96;t exist', 'nggallery').'!<br />'.__('Please create the folder <i>thumbs</i> in your gallery folder.', 'nggallery').'</font>';
	 		else @mkdir ($gallerypath.'/thumbs',NGGFOLDER_PERMISSION) or die  ('<font color="red">'.__('Unable to create directory ', 'nggallery').$gallerypath.'/thumbs !</font>');
		}
		
		// take folder name as gallery name		
		$galleryname = basename($galleryfolder);
		
		// check for existing gallery
		$gallery_id = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE name = '$galleryname' ");
		
		if (!$gallery_id) {
			$result = $wpdb->query("INSERT INTO $wpdb->nggallery (name, path) VALUES ('$galleryname', '$galleryfolder') ");
			if (!$result) {
				nggallery::show_error(__('Database error. Could not add gallery!','nggallery'));
				return;
			}
			$gallery_id = $wpdb->insert_id;  // get index_id
		}
		
		// Look for existing image list
		$old_imageslist = $wpdb->get_col("SELECT filename FROM $wpdb->nggpictures WHERE galleryid = '$gallery_id' ");
		// if no images are there, create empty array
		if ($old_imageslist == NULL) $old_imageslist = array();
		// check difference
		$new_images = array_diff($new_imageslist, $old_imageslist);
		// now create thumbnails
		ngg_generatethumbnail($gallerypath,$new_images);

		// add images to database		
		$count_pic = 0;
		if (is_array($new_images)) {
			foreach($new_images as $picture) {
			$result = $wpdb->query("INSERT INTO $wpdb->nggpictures (galleryid, filename, alttext, exclude) VALUES ('$gallery_id', '$picture', '$picture' , 0) ");
			if ($result) $count_pic++;
			}
		}
		
		nggallery::show_message(__('Gallery','nggallery').' <strong>'.$galleryname.'</strong> '.__('successfully created!','nggallery').'<br />'.$count_pic.__(' pictures added.','nggallery'));
		return;

	}
	// **************************************************************
	function ngg_scandir($dirname=".") { 
		// thx to php.net :-)
		$ext = array("jpeg", "jpg", "png", "gif"); 
		$files = array(); 
		if($handle = opendir($dirname)) { 
		   while(false !== ($file = readdir($handle))) 
		       for($i=0;$i<sizeof($ext);$i++) 
		           if(stristr($file, ".".$ext[$i])) 
		               $files[] = utf8_encode($file); 
		   closedir($handle); 
		} 
		return($files); 
	} 
	
	// **************************************************************
	function ngg_resizeImages($gallery_absfolder, $pictures) {
		// ** $gallery_absfolder must contain abspath !!
		
		$ngg_options = get_option('ngg_options');
		
		if (is_array($pictures)) {
			foreach($pictures as $picture) {
	
				if (!is_writable($gallery_absfolder."/".$picture)) {
					$messagetext .= $gallery_absfolder."/".$picture."<br />";
					continue;
				}
				
				$thumb = new ngg_Thumbnail($gallery_absfolder."/".$picture, TRUE);
				// echo $thumb->errmsg;	
				// skip if file is not there
				if (!$thumb->error) {
					$thumb->resize($ngg_options[imgWidth],$ngg_options[imgHeight],$ngg_options[imgResampleMode]);
					$thumb->save($gallery_absfolder."/".$picture,$ngg_options[imgQuality]);
				}
				$thumb->destruct();
			}
		}
		
		if(!empty($messagetext)) nggallery::show_error('<strong>'.__('Some pictures are not writeable :','nggallery').'</strong><br /><ul>'.$messagetext.'</ul>');
		return;
	}
	
	// **************************************************************
	function ngg_generateWatermark($gallery_absfolder, $pictures) {
		// ** $gallery_absfolder must contain abspath !!
		
		$ngg_options = get_option('ngg_options');
		
		if (is_array($pictures)) {
			foreach($pictures as $picture) {
	
			if (!is_writable($gallery_absfolder."/".$picture)) {
				$messagetext .= $gallery_absfolder."/".$picture."<br />";
				continue;
			}
			
			$thumb = new ngg_Thumbnail($gallery_absfolder."/".$picture, TRUE);
			// echo $thumb->errmsg;	
			// skip if file is not there
			if (!$thumb->error) {
				if ($ngg_options[wmType] == 'image') {
					$thumb->watermarkImgPath = $ngg_options[wmPath];
					$thumb->watermarkImage($ngg_options[wmPos], $ngg_options[wmXpos], $ngg_options[wmYpos]); 
				}
				if ($ngg_options[wmType] == 'text') {
					$thumb->watermarkText = $ngg_options[wmText];
					$thumb->watermarkCreateText($ngg_options[wmColor], $ngg_options[wmFont], $ngg_options[wmSize], $ngg_options[wmOpaque]);
					$thumb->watermarkImage($ngg_options[wmPos], $ngg_options[wmXpos], $ngg_options[wmYpos]);  
				}
				$thumb->save($gallery_absfolder."/".$picture,$ngg_options[imgQuality]);
			}
			$thumb->destruct();
			}
		}
		
		if(!empty($messagetext)) nggallery::show_error('<strong>'.__('Some pictures are not writeable :','nggallery').'</strong><br /><ul>'.$messagetext.'</ul>');
		return;
	}

	// **************************************************************
	function ngg_generatethumbnail($gallery_absfolder, $pictures) {
		// ** $gallery_absfolder must contain abspath !!
		
		$ngg_options = get_option('ngg_options');
		
		$prefix = nggallery::get_thumbnail_prefix($gallery_absfolder);
		$thumbfolder = nggallery::get_thumbnail_folder($gallery_absfolder);
		
		if (is_array($pictures)) {
			foreach($pictures as $picture) {
				
				// check for existing thumbnail
				if (file_exists($gallery_absfolder.$thumbfolder.$prefix.$picture)) {
					if (!is_writable($gallery_absfolder.$thumbfolder.$prefix.$picture)) {
						$messagetext .= $gallery_absfolder."/".$picture."<br />";
						continue;
					}
				}
	
				$thumb = new ngg_Thumbnail($gallery_absfolder."/".utf8_decode($picture), TRUE);

				// skip if file is not there
				if (!$thumb->error) {
					if ($ngg_options[thumbcrop]) {
						
						// THX to Kees de Bruin, better thumbnails if portrait format
						$width = $ngg_options[thumbwidth];
						$height = $ngg_options[thumbheight];
						$curwidth = $thumb->currentDimensions['width'];
						$curheight = $thumb->currentDimensions['height'];
						if ($curwidth > $curheight) {
							$aspect = (100 * $curwidth) / $curheight;
						} else {
							$aspect = (100 * $curheight) / $curwidth;
						}
						$width = intval(($width * $aspect) / 100);
						$height = intval(($height * $aspect) / 100);
						$thumb->resize($width,$height,$ngg_options[thumbResampleMode]);
						$thumb->cropFromCenter($width,$ngg_options[thumbResampleMode]);
					} 
					elseif ($ngg_options[thumbfix])  {
						// check for portrait format
						if ($thumb->currentDimensions['height'] > $thumb->currentDimensions['width']) {
							$thumb->resize($ngg_options[thumbwidth], 0,$ngg_options[thumbResampleMode]);
							// get optimal y startpos
							$ypos = ($thumb->currentDimensions['height'] - $ngg_options[thumbheight]) / 2;
							$thumb->crop(0, $ypos, $ngg_options[thumbwidth],$ngg_options[thumbheight],$ngg_options[thumbResampleMode]);	
						} else {
							$thumb->resize(0,$ngg_options[thumbheight],$ngg_options[thumbResampleMode]);	
							// get optimal x startpos
							$xpos = ($thumb->currentDimensions['width'] - $ngg_options[thumbwidth]) / 2;
							$thumb->crop($xpos, 0, $ngg_options[thumbwidth],$ngg_options[thumbheight],$ngg_options[thumbResampleMode]);	
						}
					} else {
						$thumb->resize($ngg_options[thumbwidth],$ngg_options[thumbheight],$ngg_options[thumbResampleMode]);	
					}
					$thumb->save($gallery_absfolder.$thumbfolder.$prefix.$picture,$ngg_options[thumbquality]);
					// didn't work under safe mode, but I want to set it if possible
					@chmod ($gallery_absfolder.$thumbfolder.$prefix.$picture, NGGFILE_PERMISSION); 
				} else {
					$errortext .= $picture." <strong>(Error : ".$thumb->errmsg .")</strong><br />";
				}
				$thumb->destruct();
			}
		}

		if(!empty($errortext)) nggallery::show_error('<strong>'.__('Follow thumbnails could not created.','nggallery').'</strong><br /><ul>'.$errortext.'</ul>');		
		if(!empty($messagetext)) nggallery::show_error('<strong>'.__('Some thumbnails are not writeable :','nggallery').'</strong><br /><ul>'.$messagetext.'</ul>');

		return;
	}

	// **************************************************************
	function ngg_unzip($dir, $file) {
	// thx to Gregor at http://blog.scoutpress.de/forum/topic/45
		
		require_once(NGGALLERY_ABSPATH.'/lib/pclzip.lib.php');
		
		$archive = new PclZip($file);
	
		// extract all files in one folder
		if ($archive->extract(PCLZIP_OPT_PATH, $dir, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_CB_PRE_EXTRACT, 'ngg_getonlyimages') == 0) {
			die("Error : ".$archive->errorInfo(true));
		}
		
		return;
	}
 
	// **************************************************************
	function ngg_getonlyimages($p_event, &$p_header)	{
		$info = pathinfo($p_header['filename']);
		// check for extension
		$ext = array("jpeg", "jpg", "png", "gif"); 
		if (in_array( strtolower($info['extension']), $ext)) {
			// For MAC skip the ".image" files
			if ($info['basename']{0} ==  "." ) 
				return 0;
			else 
				return 1;
		}
		// ----- all other files are skipped
		else {
		  return 0;
		}
	}

	// **************************************************************
	function ngg_import_zipfile($defaultpath) {
		
		$temp_zipfile = $_FILES['zipfile']['tmp_name'];
		$filename = $_FILES['zipfile']['name']; 
					
		// check if file is a zip file
		if (!eregi('zip', $_FILES['zipfile']['type']))
			// on whatever reason MAC shows "application/download"
			if (!eregi('download', $_FILES['zipfile']['type'])) {
				@unlink($temp_zipfile); // del temp file
				nggallery::show_error(__('Uploaded file was no or a faulty zip file ! The server recognize : ','nggallery').$_FILES['zipfile']['type']);
				return; 
			}
			
		// get foldername if selected
		$foldername = $_POST['zipgalselect'];
		if ($foldername == "0") {	
			//cleanup and take the zipfile name as folder name
			$foldername = sanitize_title(strtok ($filename,'.'));
			//$foldername = preg_replace ("/(\s+)/", '-', strtolower(strtok ($filename,'.')));					
		}

		// set complete folder path		
		$newfolder = WINABSPATH.$defaultpath.$foldername;
	
		if (!is_dir($newfolder)) {
			// create new directories
			if (!@mkdir ($newfolder, NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to create directory ', 'nggallery').$newfolder.'!</font>');
			if (!@chmod ($newfolder, NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to set directory permissions ', 'nggallery').$newfolder.'!</font>');
			if (!@mkdir ($newfolder.'/thumbs', NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to create directory ', 'nggallery').$newfolder.'/thumbs !</font>');
			if (!@chmod ($newfolder.'/thumbs', NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to set directory permissions ', 'nggallery').$newfolder.'/thumbs !</font>');
		} 
		
		// unzip and del temp file		
		ngg_unzip($newfolder, $temp_zipfile);
		@unlink($temp_zipfile) or die ('<div class="updated"><p><strong>'.__('Unable to unlink zip file ', 'nggallery').$temp_zipfile.'!</strong></p></div>');		
		
		$messagetext = __('Zip-File successfully unpacked','nggallery').'<br />';		
		
		// parse now the folder and add to database
		$messagetext .= ngg_import_gallery($defaultpath.$foldername);

		nggallery::show_message($messagetext);
		return;
	}

	// **************************************************************
	function ngg_upload_images($defaultpath) {
	// upload of pictures
		
		global $wpdb;
		
		// Images must be an array
		$imageslist = array();
		$i = 1;
		
		foreach ($_FILES as $key => $value) {
			
			// look only for uploded files
			if ($_FILES[$key]['error'] == 0) {
				$temp_file = $_FILES[$key]['tmp_name'];
				$filepart = pathinfo ( strtolower($_FILES[$key]['name']) );
				// required until PHP 5.2.0
				$filepart['filename'] = substr($filepart["basename"],0 ,strlen($filepart["basename"]) - (strlen($filepart["extension"]) + 1) );
				$filename = sanitize_title($filepart['filename']).".".$filepart['extension'];
				// check if this filename already exist
				if (in_array($filename,$imageslist))
					$filename = sanitize_title($filepart['filename']) . "_" . $i++ . "." .$filepart['extension'];
					
				$dest_gallery = $_POST['galleryselect'];
				if ($dest_gallery == 0) {
					@unlink($temp_file)  or die  ('<div class="updated"><p><strong>'.__('Unable to unlink file ', 'nggallery').$temp_zipfile.'!</strong></p></div>');		
					nggallery::show_error(__('No gallery selected !','nggallery'));
					return;	
				}
		
				// get the path to the gallery	
				$gallerypath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$dest_gallery' ");
				if (!$gallerypath){
					@unlink($temp_file)  or die  ('<div class="updated"><p><strong>'.__('Unable to unlink file ', 'nggallery').$temp_zipfile.'!</strong></p></div>');		
					nggallery::show_error(__('Failure in database, no gallery path set !','nggallery'));
					return;
				} 

				// check for allowed extension
				$ext = array("jpeg", "jpg", "png", "gif"); 
				if (!in_array($filepart['extension'],$ext)){ 
					nggallery::show_error('<strong>'.$_FILES[$key]['name'].' </strong>'.__('is no valid image file!','nggallery'));
					continue;
				}
				
				$dest_file = WINABSPATH.$gallerypath."/".$filename;
				
				// save temp file to gallery
				if (!@move_uploaded_file($_FILES[$key]['tmp_name'], $dest_file)){
					nggallery::show_error(__('Error, the file could not moved to : ','nggallery').$dest_file);
					continue;
				} 
				if (!@chmod ($dest_file, NGGFILE_PERMISSION)) {
					nggallery::show_error(__('Error, the file permissions could not set','nggallery'));
					continue;
				}
				
				// add to imagelist
				$imageslist[] = $filename;

			}
		}
			
		//create thumbnails
		ngg_generatethumbnail(WINABSPATH.$gallerypath,$imageslist);
		
		// add images to database		
		$count_pic = 0;
		if (is_array($imageslist)) {
			foreach($imageslist as $picture) {
			$result = $wpdb->query("INSERT INTO $wpdb->nggpictures (galleryid, filename, alttext, exclude) VALUES ('$dest_gallery', '$picture', '$picture', 0) ");
			if ($result) $count_pic++;
			}
		}
		
		nggallery::show_message($count_pic.__(' Image(s) successfully added','nggallery'));
		return;

	} // end function

?>