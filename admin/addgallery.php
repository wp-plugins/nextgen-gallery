<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	include_once(NGGALLERY_ABSPATH.'/lib/thumbnail.inc.php');

	function nggallery_admin_add_gallery()  {

	global $wpdb;
	$ngg_options = get_option('ngg_options');

	$defaultpath = $ngg_options['gallerypath'];	
	
	if ($_POST['addgallery']){
		$newgallery = attribute_escape($_POST['galleryname']);
		if (!empty($newgallery))
			$messagetext = ngg_create_gallery($newgallery, $defaultpath);
	}
	
	if ($_POST['zipupload']){
		if ($_FILES['zipfile']['error'] == 0) 
			$messagetext = ngg_import_zipfile($defaultpath);
		else
			$messagetext = '<font color="red">'.__('Upload failed!','nggallery').'</font>';	
	}
	
	if ($_POST['importfolder']){
		$galleryfolder = $_POST['galleryfolder'];
		if ((!empty($galleryfolder)) AND ($defaultpath != $galleryfolder))
			$messagetext = ngg_import_gallery($galleryfolder);
	}
	
	if ($_POST['uploadimage']){
		if ($_FILES['MF__F_0_0']['error'] == 0) {
			$messagetext = ngg_upload_images($defaultpath);
		}
		else
			$messagetext = '<font color="red">'.__('Upload failed!','nggallery').'</font>';	
	}
			
	// message windows
	if(!empty($messagetext)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$messagetext.'</p></div>'; }
	
	?>
	<script type="text/javascript">
		var currentTab = null;
		var inSlide = false;
		jQuery(document).ready(
			function()
			{
				var url, tab = 0, tabIteration = 0;
				
				url = window.location.href.split("#");
				if (url.length == 2 && url[1].indexOf('-slider') > 0) {
					currentTab = jQuery('#' + url[1].substr(0, url[1].length-7));
					if (currentTab.size() == 1) {
						jQuery('#slider div').each(
							function(iteration)
							{
								if(this === currentTab.get(0)) {
									tabIteration = iteration;
								}	
							}
						);
					}
				}
				
				if(!currentTab) {
					currentTab = jQuery('#slider div:first');
				}

				currentTab.SlideToggleUp(500);
				jQuery('#tabs a')
					.eq(tabIteration).addClass('active')
					.end()
					.bind('click', switchTab);
			}
		);

		var switchTab = function()
		{
			// get id from link
			var tabName = this.href.split('#')[1];
			this.blur();
			if (inSlide == false && currentTab.get(0) != jQuery('#' + tabName.substr(0, tabName.length-7)).get(0)) {
				jQuery('#tabs a').removeClass('active');
				jQuery(this).addClass('active');
				inSlide = true;
				currentTab.SlideToggleUp(
					500,
					function()
					{
						currentTab = jQuery('#' + tabName.substr(0, tabName.length-7)).SlideToggleUp(500, function(){inSlide=false;});
					}
				);
			} else {
				return false;
			}
		};
		
		jQuery(function(){
			jQuery('#imagefiles').MultiFile({
				STRING: {
			    	remove:'<?php _e('remove', 'nggallery') ;?>'
  				}
		 	});
		});

	</script>
	<div class="wrap" style="text-align: center">
		<div id="tabs">
			<a href="#addgallery-slider"><?php _e('Add new gallery', 'nggallery') ;?></a> -
			<?php if (!SAFE_MODE) { ?>
			<a href="#zipupload-slider"><?php _e('Upload a Zip-File', 'nggallery') ;?></a> -
			<?php } ?>
			<a href="#importfolder-slider"><?php _e('Import image folder', 'nggallery') ;?></a> -
			<a href="#uploadimage-slider"><?php _e('Upload Images', 'nggallery') ;?></a>
		</div>
	</div>
	
	<div id="slider">
		<!-- create gallery -->
		<div id="addgallery" class="wrap" style="display:none">
		<h2><?php _e('Add new gallery', 'nggallery') ;?></h2>
			<form name="addgallery" id="addgallery" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" accept-charset="utf-8" >
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
		<div id="zipupload" class="wrap" style="display:none">
		<h2><?php _e('Upload a Zip-File', 'nggallery') ;?></h2>
			<form name="zipupload" id="zipupload" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#zipupload-slider'; ?>" accept-charset="utf-8" >
			<fieldset class="options">
				<table class="optiontable"> 
				<tr valign="top"> 
					<th scope="row"><?php _e('Select Zip-File', 'nggallery') ;?>:</th> 
					<td><input type="file" name="zipfile" id="zipfile" size="35" class="uploadform"/><br />
					<?php _e('Upload a zip file with images', 'nggallery') ;?></td> 
				</tr>
				</table>
				<div class="submit"> <input type="submit" name= "zipupload" value="<?php _e('Start upload', 'nggallery') ;?>"/></div>
			</fieldset>
			</form>
		</div>
		<!-- import folder -->
		<div id="importfolder" class="wrap" style="display:none">
		<h2><?php _e('Import image folder', 'nggallery') ;?></h2>
			<form name="importfolder" id="importfolder" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#importfolder-slider'; ?>" accept-charset="utf-8" >
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
		<div id="uploadimage" class="wrap" style="display:none">
		<h2><?php _e('Upload Images', 'nggallery') ;?></h2>
			<form name="uploadimage" id="uploadimage" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#uploadimage-slider'; ?>" accept-charset="utf-8" >
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
	function ngg_create_gallery($galleryname, $defaultpath) {
		// create a new gallery & folder
		global $wpdb;
		
		$myabspath = str_replace("\\","/",ABSPATH);  // required for windows
		
		//cleanup pathname
		$new_pathname = strtolower(preg_replace ("/(\s+)/", '-',$galleryname));
		$new_pathname = preg_replace('|[^a-z0-9-]|i', '', $new_pathname);
		
		if (empty($new_pathname)) return '<font color="red">'.__('No valid gallery name!', 'nggallery'). '</font>';	
	
		if ( substr(decoct(@fileperms($myabspath.$defaultpath)),1) != decoct(NGGFOLDER_PERMISSION) )
			return '<font color="red">'.__('Directory', 'nggallery').' <strong>'.$defaultpath.'</strong> '.__('didn\'t have the permissions ', 'nggallery').decoct(NGGFOLDER_PERMISSION).'!</font>';

		$nggpath = $defaultpath.$new_pathname;

		if (is_dir($myabspath.$nggpath))
			return '<font color="red">'.__('Directory', 'nggallery').' <strong>'.$nggpath.'</strong> '.__('already exists!', 'nggallery').'</font>';	

		// create new directories
		if (!SAFE_MODE) {
			if (!@mkdir ($myabspath.$nggpath,NGGFOLDER_PERMISSION)) return  ('<font color="red">'.__('Unable to create directory ', 'nggallery').$nggpath.'!</font>');
			if (!@chmod ($myabspath.$nggpath,NGGFOLDER_PERMISSION)) return  ('<font color="red">'.__('Unable to set directory permissions ', 'nggallery').$nggpath.'!</font>');
			if (!@mkdir ($myabspath.$nggpath.'/thumbs',NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to create directory ', 'nggallery').$nggpath.'/thumbs !</font>');
			if (!@chmod ($myabspath.$nggpath.'/thumbs',NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to set directory permissions', 'nggallery').$nggpath.'/thumbs !</font>');
		} else {
			$safemode  = '<br /><font color="green">'.__('The server Safe-Mode is on !', 'nggallery');	
			$safemode .= '<br />'.__('Please create directory', 'nggallery').' <strong>'.$nggpath.'</strong> ';	
			$safemode .= __('and the thumbnails directory', 'nggallery').' <strong>'.$nggpath.'/thumbs</strong> '.__('with permission 777 manually !', 'nggallery').'</font>';	
		}
		$result=$wpdb->get_var("SELECT name FROM $wpdb->nggallery WHERE name = '$galleryname' ");
		if ($result) {
			return '<font color="red">'.__('Gallery', 'nggallery').' <strong>'.$newgallery.'</strong> '.__('already exists', 'nggallery').'</font>';			
		} else { 
			$result = $wpdb->query("INSERT INTO $wpdb->nggallery (name, path) VALUES ('$galleryname', '$nggpath') ");
			if ($result) return '<font color="green">'.__('Gallery successfully created!','nggallery').'</font>'.$safemode;
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
		$imageslist = ngg_scandir($gallerypath);
		if (empty($imageslist)) return '<font color="blue">'.__('Directory', 'nggallery').' <strong>'.$gallerypath.'</strong> '.__('contains no pictures', 'nggallery').'!</font>';

		// create thumbnail folder
		$check_thumbnail_folder = ngg_get_thumbnail_folder($gallerypath);
		if (!$check_thumbnail_folder) {
			if (SAFE_MODE) return '<font color="red">'.__('Thumbnail Directory', 'nggallery').' <strong>'.$gallerypath.'/thumbs</strong> '.__('doesn&#96;t exist', 'nggallery').'!<br />'.__('Please create the folder <i>thumbs</i> in your gallery folder.', 'nggallery').'</font>';
	 		else @mkdir ($gallerypath.'/thumbs',NGGFOLDER_PERMISSION) or die  ('<font color="red">'.__('Unable to create directory ', 'nggallery').$gallerypath.'/thumbs !</font>');
		}
		
		// take folder name as gallery name		
		$galleryname = basename($galleryfolder);

		$result = $wpdb->query("INSERT INTO $wpdb->nggallery (name, path) VALUES ('$galleryname', '$galleryfolder') ");
		if (!$result) return '<font color="red">'.__('Database error. Could not add gallery!','nggallery').'</font>';
		$gallery_id = $wpdb->insert_id;  // get index_id
		
		//create thumbnails
		ngg_generatethumbnail($gallerypath,$imageslist);

		// add images to database		
		if (is_array($imageslist)) {
			foreach($imageslist as $picture) {
			$result = $wpdb->query("INSERT INTO $wpdb->nggpictures (galleryid, filename, alttext, exclude) VALUES ('$gallery_id', '$picture', '$picture' , 0) ");
			if ($result) $count_pic++;
			}
		}
		
		return '<font color="green">'.__('Gallery','nggallery').' <strong>'.$galleryname.'</strong> '.__('successfully created!','nggallery').'<br />'.$count_pic.__(' pictures added.','nggallery').'</font>';

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
		
		if(!empty($messagetext)) echo '<div id="message-error" class="error fade"><p><strong>'.__('Some pictures are not writeable :','nggallery').'</strong><br /><ul>'.$messagetext.'</ul></p></div>';
		
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
		
		if(!empty($messagetext)) echo '<div id="message-error" class="error fade"><p><strong>'.__('Some pictures are not writeable :','nggallery').'</strong><br /><ul>'.$messagetext.'</ul></p></div>';
		
		return;
	}

	// **************************************************************
	function ngg_generatethumbnail($gallery_absfolder, $pictures) {
		// ** $gallery_absfolder must contain abspath !!
		
		$ngg_options = get_option('ngg_options');
		
		$prefix = ngg_get_thumbnail_prefix($gallery_absfolder);
		$thumbfolder = ngg_get_thumbnail_folder($gallery_absfolder);
		
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

		if(!empty($errortext)) echo '<div id="message-error" class="error fade"><p><strong>'.__('Follow thumbnails could not created.','nggallery').'</strong><br /><ul>'.$errortext.'</ul></p></div>';		
		if(!empty($messagetext)) echo '<div id="message-error" class="error fade"><p><strong>'.__('Some thumbnails are not writeable :','nggallery').'</strong><br /><ul>'.$messagetext.'</ul></p></div>';

		return;
	}

	// **************************************************************
	function ngg_unzip($dir, $file) {
	// thx to Gregor at http://blog.scoutpress.de/forum/topic/45
		
		require_once(NGGALLERY_ABSPATH.'/lib/pclzip.lib.php');
	
		$archive = new PclZip($file);
		
		//TODO: Check PCLZIP_OPT_REMOVE_ALL_PATH to remove path
		if ($archive->extract(PCLZIP_OPT_PATH, $dir) == 0) {
			die("Error : ".$archive->errorInfo(true));
		}
		
		return;
	}

	// **************************************************************
	function ngg_import_zipfile($defaultpath) {
		
		$temp_zipfile = $_FILES['zipfile']['tmp_name'];
		$filename = $_FILES['zipfile']['name']; 
				
		// check if file is a zip file
		if (!eregi('zip', $_FILES['zipfile']['type'])) {
			@unlink($temp_zipfile); // del temp file
			return '<font color="red">'.__('Uploaded file was no or a faulty zip file ! The server recognize : ','nggallery').$_FILES['zipfile']['type'].'</font>'; 
		}
		
		//cleanup and take the zipfile name as folder name
		$foldername = preg_replace ("/(\s+)/", '-', strtolower(strtok ($filename,'.')));
		$newfolder = WINABSPATH.$defaultpath.$foldername;
		
		if (!is_dir($newfolder)) {
			// create new directories
			if (!@mkdir ($newfolder, NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to create directory ', 'nggallery').$newfolder.'!</font>');
			if (!@chmod ($newfolder, NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to set directory permissions ', 'nggallery').$newfolder.'!</font>');
			if (!@mkdir ($newfolder.'/thumbs', NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to create directory ', 'nggallery').$newfolder.'/thumbs !</font>');
			if (!@chmod ($newfolder.'/thumbs', NGGFOLDER_PERMISSION)) return ('<font color="red">'.__('Unable to set directory permissions ', 'nggallery').$newfolder.'/thumbs !</font>');
		}
		else {
			return '<font color="red">'.__('Directory already exists, please rename zip file', 'nggallery').'!</font>';	
		}
		
		// unzip and del temp file		
		ngg_unzip($newfolder, $temp_zipfile);
		@unlink($temp_zipfile) or die ('<div class="updated"><p><strong>'.__('Unable to unlink zip file ', 'nggallery').$temp_zipfile.'!</strong></p></div>');		
		
		$messagetext = '<font color="green">'.__('Zip-File successfully unpacked','nggallery').'</font><br />';		

		// parse now the folder and add to database
		$messagetext .= ngg_import_gallery($defaultpath.$foldername);

		return $messagetext;
	}

	// **************************************************************
	function ngg_upload_images($defaultpath) {
	// upload of pictures
		
		global $wpdb;
		
		// Images must be an array
		$imageslist = array();
		
		foreach ($_FILES as $key => $value) {

			// look only for uploded files
			if ($_FILES[$key]['error'] == 0) {

				$temp_file = $_FILES[$key]['tmp_name'];
				$filename = $_FILES[$key]['name']; 
			
				$dest_gallery = $_POST['galleryselect'];
				if ($dest_gallery == 0) {
					@unlink($temp_file)  or die  ('<div class="updated"><p><strong>'.__('Unable to unlink file ', 'nggallery').$temp_zipfile.'!</strong></p></div>');		
					return '<font color="red">'.__('No gallery selected !','nggallery').'</font>';	
				}
		
				// get the path to the gallery	
				$gallerypath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$dest_gallery' ");
				if (!$gallerypath){
					@unlink($temp_file)  or die  ('<div class="updated"><p><strong>'.__('Unable to unlink file ', 'nggallery').$temp_zipfile.'!</strong></p></div>');		
					return '<font color="red">'.__('Failure in database, no gallery path set !','nggallery').'</font>';
				} 
				
				$dest_file = WINABSPATH.$gallerypath."/".$filename;
				
				// save temp file to gallery
				if (!@move_uploaded_file($_FILES[$key]['tmp_name'], $dest_file)) return '<font color="red">'.__('Error, the file could not moved to : ','nggallery').$dest_file.'</font>';
				if (!@chmod ($dest_file, NGGFILE_PERMISSION)) return '<font color="red">'.__('Error, the file permissions could not set','nggallery').'</font>';
				
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
	
		return '<font color="green">'.$count_pic.__(' Images successfully added','nggallery').'</font>';

	} // end function

?>