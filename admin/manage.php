<?php  

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

function nggallery_admin_manage_gallery() {
	global $wpdb;

	// GET variables
	$act_gid = trim($_GET['gid']);
	$act_pid = trim($_GET['pid']);	
	$mode = trim($_GET['mode']);

	// get the options
	$ngg_options=get_option('ngg_options');	

	if ($mode == 'delete') {
	// Delete a gallery
	
		// get the path to the gallery
		$gallerypath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$act_gid' ");
		if ($gallerypath){
			$thumb_folder = ngg_get_thumbnail_folder($gallerypath, FALSE);
			$thumb_prefix = ngg_get_thumbnail_prefix($gallerypath, FALSE);
	
			// delete pictures
			$imagelist = $wpdb->get_col("SELECT filename FROM $wpdb->nggpictures WHERE galleryid = '$act_gid' ");
			if ($ngg_options[deleteImg]) {
				if (is_array($imagelist)) {
					foreach ($imagelist as $filename) {
						unlink(WINABSPATH.$gallerypath.'/'.$thumb_folder.'/'.$thumb_prefix.$filename);
						unlink(WINABSPATH.$gallerypath.'/'.$filename);
					}
				}
				// delete folder
					@rmdir(WINABSPATH.$gallerypath.'/'.$thumb_folder);
					@rmdir(WINABSPATH.$gallerypath);
			}
		}

		$delete_pic = $wpdb->query("DELETE FROM $wpdb->nggpictures WHERE galleryid = $act_gid");
		$delete_galllery = $wpdb->query("DELETE FROM $wpdb->nggallery WHERE gid = $act_gid");
		
		if($delete_galllery)
			$messagetext = '<font color="green">'.__('Gallery','nggallery').' \''.$act_gid.'\' '.__('deleted successfully','nggallery').'</font>';
	 	$mode = 'main'; // show mainpage
	}

	if ($mode == 'delpic') {
	// Delete a picture
		$gallerypath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$act_gid' ");
		if ($gallerypath){
			$thumb_folder = ngg_get_thumbnail_folder($gallerypath, FALSE);
			$thumb_prefix = ngg_get_thumbnail_prefix($gallerypath, FALSE);
			$filename = $wpdb->get_var("SELECT filename FROM $wpdb->nggpictures WHERE pid = '$act_pid' ");
			if ($ngg_options[deleteImg]) {
				unlink(WINABSPATH.$gallerypath.'/'.$thumb_folder.'/'.$thumb_prefix.$filename);
				unlink(WINABSPATH.$gallerypath.'/'.$filename);
			}
		}		
		$delete_pic = $wpdb->query("DELETE FROM $wpdb->nggpictures WHERE pid = $act_pid");

		if($delete_pic)
			$messagetext = '<font color="green">'.__('Picture','nggallery').' \''.$act_pid.'\' '.__('deleted successfully','nggallery').'</font>';
	 	$mode = 'edit'; // show pictures
	}
	
	if (isset($_POST['bulkaction']))  {
		// do bulk update
		
		$gallerypath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$act_gid' ");
		$imageslist = array();
		
		if ( is_array($_POST['doaction']) ) {
			foreach ( $_POST['doaction'] as $imageID ) {
				$imageslist[] = $wpdb->get_var("SELECT filename FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
			}
		}
		
		switch ($_POST['bulkaction']) {
			case 0;
			// No action
				break;
			case 1:
			// Set watermark
				ngg_generateWatermark(WINABSPATH.$gallerypath,$imageslist);
				$messagetext = '<font color="green">'.__('Watermark successfully added','nggallery').'</font>';	
				break;
			case 2:
			// Create new thumbnails
				ngg_generatethumbnail(WINABSPATH.$gallerypath,$imageslist);
				$messagetext = '<font color="green">'.__('Thumbnails successfully created. Please refresh your browser cache.','nggallery').'</font>';	
				break;
			case 3:
			// Resample images
				ngg_resizeImages(WINABSPATH.$gallerypath,$imageslist);
				$messagetext = '<font color="green">'.__('Images successfully resized','nggallery').'</font>';	
				break;
			case 4:
			// Delete images
				if ( is_array($_POST['doaction']) ) {
				if ($gallerypath){
					$thumb_folder = ngg_get_thumbnail_folder($gallerypath, FALSE);
					$thumb_prefix = ngg_get_thumbnail_prefix($gallerypath, FALSE);
					foreach ( $_POST['doaction'] as $imageID ) {
						$filename = $wpdb->get_var("SELECT filename FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
						if ($ngg_options[deleteImg]) {
							unlink(WINABSPATH.$gallerypath.'/'.$thumb_folder.'/'.$thumb_prefix.$filename);
							unlink(WINABSPATH.$gallerypath.'/'.$filename);	
						} 
						$delete_pic = $wpdb->query("DELETE FROM $wpdb->nggpictures WHERE pid = $imageID");
					}
				}		
				if($delete_pic)
					$messagetext = '<font color="green">'.__('Pictures deleted successfully ','nggallery').'</font>';
				}
				break;
		}
	}

	if ($_POST['updatepictures'])  {
	// Update pictures	
		
		$gallery_title=$_POST[title];
		$gallery_path=$_POST[path];
		$gallery_desc=$_POST[gallerydesc];
		$gallery_pageid=$_POST[pageid];
		$gallery_preview=$_POST[previewpic];
		
		$result = $wpdb->query("UPDATE $wpdb->nggallery SET title= '$gallery_title', path= '$gallery_path', description = '$gallery_desc', pageid = '$gallery_pageid', previewpic = '$gallery_preview' WHERE gid = '$act_gid'");
		$result = ngg_update_pictures($_POST[description], $_POST[alttext], $_POST[exclude], $act_gid );

		$messagetext = '<font color="green">'.__('Update successfully','nggallery').'</font>';
	}

	if ($_POST['scanfolder'])  {
	// Rescan folder
		$gallerypath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$act_gid' ");
		$old_imageslist = $wpdb->get_col("SELECT filename FROM $wpdb->nggpictures WHERE galleryid = '$act_gid' ");
		// if no images are there, create empty array
		if ($old_imageslist == NULL) $old_imageslist = array();
		// read list of images in folder
		$new_imageslist = ngg_scandir(WINABSPATH.$gallerypath);
		// check difference
		$imageslist = array_diff($new_imageslist, $old_imageslist);
		//create thumbnails
		ngg_generatethumbnail(WINABSPATH.$gallerypath,$imageslist);
		// add images to database
		$count_pic = 0;		
		if (is_array($imageslist)) {
			foreach($imageslist as $picture) {
				$result = $wpdb->query("INSERT INTO $wpdb->nggpictures (galleryid, filename, alttext) VALUES ('$act_gid', '$picture', '$picture') ");
				if ($result) $count_pic++;
			}
			$messagetext = '<font color="green">'.$count_pic.__(' picture(s) successfully added','nggallery').'</font>';
		}
	}

	// message windows
	if(!empty($messagetext)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$messagetext.'</p></div>'; }

	if (($mode == '') or ($mode == "main"))
		nggallery_manage_gallery_main();
	
	if ($mode == 'edit')
		nggallery_pciturelist();
	
}//nggallery_admin_manage_gallery

function nggallery_manage_gallery_main() {
// *** show main gallery list

	global $wpdb;
	
	?>
	<script type="text/javascript"> var tb_pathToImage = '<?php echo NGGALLERY_URLPATH ?>thickbox/loadingAnimationv3.gif';</script>
	<div class="wrap">
		<h2><?php _e('Gallery Overview', 'nggallery') ?></h2>
		<table id="the-list-x" width="100%" cellspacing="3" cellpadding="3" >
			<thead>
			<tr>
				<th scope="col" ><?php _e('ID') ?></th>
				<th scope="col" ><?php _e('Gallery name', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Title', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Description', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Page ID', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Quantity', 'nggallery') ?></th>
				<th scope="col" colspan="2"><?php _e('Action'); ?></th>
			</tr>
			</thead>
			<tbody>
<?php			
$gallerylist = $wpdb->get_results("SELECT * FROM $wpdb->nggallery ORDER BY gid ASC");
if($gallerylist) {
	foreach($gallerylist as $gallery) {
		$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
		$gid = $gallery->gid;
		$counter = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures WHERE galleryid = '$gid'");
		?>
		<tr id="gallery-<?php echo $gid ?>" <?php echo $class; ?> style="text-align:center">
			<th scope="row" style="text-align: center"><?php echo $gid; ?></th>
			<td><?php echo $gallery->name; ?></td>
			<td><?php echo $gallery->title; ?></td>
			<td><?php echo $gallery->description; ?></td>
			<td><?php echo $gallery->pageid; ?></td>
			<td><?php echo $counter; ?></td>
			<td><a href="admin.php?page=nggallery-manage-gallery&amp;mode=edit&amp;gid=<?php echo $gid; ?>" class='edit'> <?php _e('Edit') ?></a></td>
			<td><a href="admin.php?page=nggallery-manage-gallery&amp;mode=delete&amp;gid=<?php echo $gid; ?>" class="delete" onclick="javascript:check=confirm( '<?php _e("Delete this file ?",'nggallery')?>');if(check==false) return false;"><?php _e('Delete') ?></a></td>
		</tr>
		<?php
	}
} else {
	echo '<tr><td colspan="7" align="center"><strong>'.__('No entries found','nggallery').'</strong></td></tr>';
}
?>			
			</tbody>
		</table>
	</div>
<?php
} //nggallery_manage_gallery_main

function nggallery_pciturelist() {
// *** show picture list
	global $wpdb;
	
	// GET variables
	$act_gid = trim($_GET['gid']);
	
	// get the options
	$ngg_options=get_option('ngg_options');	
	
	// get gallery values
	$act_gallery = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$act_gid' ");

	// set gallery url
	$act_gallery_url 	= get_option ('siteurl')."/".$act_gallery->path."/";
	$act_thumbnail_url 	= get_option ('siteurl')."/".$act_gallery->path.ngg_get_thumbnail_folder($act_gallery->path, FALSE);
	$act_thumb_prefix   = ngg_get_thumbnail_prefix($act_gallery->path, FALSE);

?>

<script type="text/javascript"> 
jQuery(document).ready(	
	function()	{ 
		jQuery('.textarea1').Autoexpand([230,400]); 
/*		jQuery("input:checkbox").click( 
			function() {
				jQuery(this).parents('tr').Highlight(500, '#ff0', function(){jQuery(this).parents('tr').css('backgroundColor', 'transparent');});	
//				jQuery(this).parents('tr').('#td.img').fadeOut(2000);
			}
		); 
*/
	}); 
</script>
<script type="text/javascript"> var tb_pathToImage = '<?php echo NGGALLERY_URLPATH ?>thickbox/loadingAnimationv3.gif';</script>
<style type="text/css" media="all">@import "<?php echo NGGALLERY_URLPATH ?>thickbox/thickbox.css";</style>
<style type="text/css" media="all">
	/** required for view button **/
	a.thickbox:hover {
			
		background:#CCCCCC none repeat scroll 0% 50%;
		color:#003366;
		
		}
		
	a.thickbox, a.thickbox:hover {
		border-bottom:medium none;
		display:block;
		padding:5px 0pt;
		text-align:center;
		}
</style>
<script type="text/javascript">
<!--
function checkAll(form)
{
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]") {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
			}
		}
	}
}

function getNumChecked(form)
{
	var num = 0;
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]")
				if(form.elements[i].checked == true)
					num++;
		}
	}
	return num;
}
//-->
</script>
<div class="wrap">
<h2><?php _e('Gallery', 'nggallery') ?> : <?php echo $act_gallery->name; ?></h2>

<form id="updategallery" method="POST">

<fieldset class="options">
	<table width="100%" border="0" cellspacing="3" cellpadding="3" >
		<tr>
			<th align="left"><?php _e('Title') ?>:</th>
			<th align="left"><input type="text" size="50" name="title" value="<?php echo $act_gallery->title; ?>"  /></th>
			<th align="right"><?php _e('Page Link to', 'nggallery') ?>:</th>
			<th align="left">
			<select name="pageid" style="width:95%">
				<option value="0" ><?php _e('Not linked', 'nggallery') ?></option>
			<?php
				$pageids = get_all_page_ids();
				foreach($pageids as $pageid) {
					$post= get_post($pageid); 				
					if ($pageid == $act_gallery->pageid) $selected = 'selected="selected" ';
					else $selected = '';
					echo '<option value="'.$pageid.'" '.$selected.'>'.$post->post_title.'</option>'."\n";
				}
			?>
			</select>
			</th>
		</tr>
		<tr>
			<th align="left"><?php _e('Description') ?>:</th> 
			<th align="left"><textarea name="gallerydesc" cols="30" rows="3" style="width: 95%"  ><?php echo $act_gallery->description; ?></textarea></th>
			<th align="right"><?php _e('Preview image', 'nggallery') ?>:</th>
			<th align="left">
				<select name="previewpic" >
					<option value="0" ><?php _e('No Picture', 'nggallery') ?></option>
					<?php
						$picturelist = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE galleryid = '$act_gid' ORDER BY '$ngg_options[galSort]' ASC");
						if(is_array($picturelist)) {
							foreach($picturelist as $picture) {
								if ($picture->pid == $act_gallery->previewpic) $selected = 'selected="selected" ';
								else $selected = '';
								echo '<option value="'.$picture->pid.'" '.$selected.'>'.$picture->filename.'</option>'."\n";
							}
						}
					?>
				</select>
			</th>
		</tr>
		<tr>
			<th align="left"><?php _e('Path', 'nggallery') ?>:</th> 
			<th align="left"><input type="text" size="50" name="path" value="<?php echo $act_gallery->path; ?>"  /></th>
			<th></th>
			<th></th>
		</tr>

	</table>
<p class="submit">
	<input type="submit" name="scanfolder" value="<?php _e("Scan Folder for new images",'nggallery')?> " />
	<input type="submit" name="updatepictures" value="<?php _e("Save Changes",'nggallery')?> &raquo;" />
<p>
</fieldset>
<fieldset class="options">
<p><select id="bulkaction"  name="bulkaction" >
	<option value="0" ><?php _e("No action",'nggallery')?></option>
	<option value="1" ><?php _e("Set watermark",'nggallery')?></option>
	<option value="2" ><?php _e("Create new thumbnails",'nggallery')?></option>
	<option value="3" ><?php _e("Resize images",'nggallery')?></option>
	<option value="4" ><?php _e("Delete images",'nggallery')?></option>
</select>
<input  type="submit" name="doaction" value="<?php _e("OK",'nggallery')?>" onclick="var numchecked = getNumChecked(document.getElementById('updategallery')); if(numchecked < 1) { alert('<?php echo js_escape(__("No images selected",'nggallery')); ?>'); return false } return confirm('<?php echo sprintf(js_escape(__("You are about to start the bulk edit for %s images \n \n 'Cancel' to stop, 'OK' to proceed.",'nggallery')), "' + numchecked + '") ; ?>')" />
</p>
<table id="listimages" width="100%" cellspacing="2" cellpadding="5" class="widefat" >
	<thead>
	<tr>
		<th scope="col" style="text-align: center"><input name="checkall" type="checkbox" onclick="checkAll(document.getElementById('updategallery'));" /></th>
		<th scope="col" style="text-align: center"><?php _e('ID') ?></th>
		<th scope="col" style="text-align: center"><?php _e('File name', 'nggallery') ?></th>
		<th scope="col" style="text-align: center"><?php _e('Thumbnail', 'nggallery') ?></th>
		<th scope="col" style="text-align: center"><?php _e('Description', 'nggallery') ?></th>
		<th scope="col" style="text-align: center"><?php _e('Alt &amp; Title Text', 'nggallery') ?></th>
		<th scope="col" style="text-align: center"><?php _e('exclude', 'nggallery') ?></th>
		<th scope="col" colspan="2" style="text-align: center"><?php _e('Action') ?></th>
	</tr>
	</thead>
	<tbody>
<?php

if($picturelist) {
	foreach($picturelist as $picture) {
		//TODO: Ajax delete version , looks better
		//TODO: Use effect for inactive pic : style="filter:alpha(opacity=30); -moz-opacity:0.3"

		$pid = $picture->pid;
		$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';								
		if ($picture->exclude) {
			$exclude='checked="checked"';
		} else {
			$exclude='';
		}
		
		?>
		<tr id="picture-<?php echo $pid ?>" <?php echo $class ?> style="text-align:center">
			<td><input name="doaction[]" type="checkbox" value="<?php echo $pid ?>" /></td>
			<th scope="row" style="text-align: center"><?php echo $pid ?></th>
			<td><?php echo $picture->filename ?></td>
			<td><img class="thumb" src="<?php echo $act_thumbnail_url.$act_thumb_prefix.$picture->filename ?>" /></td>
			<td><textarea name="description[<?php echo $pid ?>]" class="textarea1" cols="30" rows="3" ><?php echo stripslashes($picture->description) ?></textarea></td>
			<td><input name="alttext[<?php echo $pid ?>]" type="text" size="20"   value="<?php echo $picture->alttext ?>" /></td>
			<td><input name="exclude[<?php echo $pid ?>]" type="checkbox" value="1" <?php echo $exclude ?> /></td>
			<td><a href="<?php echo $act_gallery_url.$picture->filename ?>" class="thickbox" title="<?php echo $picture->alttext ?>" ><?php _e('View') ?></a></td>
			<td><a href="admin.php?page=nggallery-manage-gallery&amp;mode=delpic&amp;gid=<?php echo $act_gid ?>&amp;pid=<?php echo $pid ?>" class="delete" onclick="javascript:check=confirm( '<?php _e("Delete this file ?",'nggallery')?>');if(check==false) return false;" ><?php _e('Delete') ?></a></td>
		</tr>
		<?php
	}
} else {
	echo '<tr><td colspan="8" align="center"><strong>'.__('No entries found','nggallery').'</strong></td></tr>';
}
?>
	
		</tbody>
	</table>
	</fieldset>
	<p class="submit"><input type="submit" name="updatepictures" value="<?php _e("Save Changes",'nggallery')?> &raquo;" /><p>
	</form>	
	<br class="clear"/>
	</div><!-- /#wrap -->
	<?php
			
} //nggallery_pciturelist

/**************************************************************************/
function ngg_update_pictures( $nggdescription, $nggalttext, $nggexclude, $nggalleryid ) {
// update all pictures
	
	global $wpdb;
	
	if (is_array($nggdescription)) {
		foreach($nggdescription as $key=>$value) {
			$result=$wpdb->query( "UPDATE $wpdb->nggpictures SET description = '$value' WHERE pid = $key");
			if($result) $update_ok = $result;
		}
	}
	if (is_array($nggalttext)){
		foreach($nggalttext as $key=>$value) {
			$result=$wpdb->query( "UPDATE $wpdb->nggpictures SET alttext = '$value' WHERE pid = $key");
			if($result) $update_ok = $result;
		}
	}
	
	$nggpictures = $wpdb->get_results("SELECT pid FROM $wpdb->nggpictures WHERE galleryid = '$nggalleryid'");

	if (is_array($nggpictures)){
		foreach($nggpictures as $picture){
			if (is_array($nggexclude)){
				if (array_key_exists($picture->pid, $nggexclude)) {
					$result=$wpdb->query("UPDATE $wpdb->nggpictures SET exclude = 1 WHERE pid = '$picture->pid'");
					if($result) $update_ok = $result;
				} 
				else {
					$result=$wpdb->query("UPDATE $wpdb->nggpictures SET exclude = 0 WHERE pid = '$picture->pid'");
					if($result) $update_ok = $result;
				}
			} else {
				$result=$wpdb->query("UPDATE $wpdb->nggpictures SET exclude = 0 WHERE pid = '$picture->pid'");
				if($result) $update_ok = $result;
			}   
		}
	}
	
	return $update_ok;
}

?>
