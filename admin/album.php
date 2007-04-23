<?php 

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

global $wpdb;

// POST proceed by AJAX jQuery
if ($_POST['ajax']){
	if ($_POST['act_album'] > 0){
		$albumid = $_POST['act_album'];
		if ($_POST['galleryContainer']){ 
			$galleryorder = $_POST['galleryContainer'];
			if(is_array($galleryorder)) {
				$sortorder = array();
				foreach($galleryorder as $gallery) {		
					$gid = substr($gallery, 4); // get id from "gid-x"
					$sortorder[] = $gid;
				}
				$serial_sort = serialize($sortorder); 
				$wpdb->query("UPDATE $wpdb->nggalbum SET sortorder = '$serial_sort' WHERE id = $albumid ");
			}
		} else {
			$wpdb->query("UPDATE $wpdb->nggalbum SET sortorder = '' WHERE id = $albumid ");
		}
		die; // stop ongoing output
	} else die;
}

function nggallery_admin_manage_album()  {
	global $wpdb;
		
	if ($_POST['update']){
		if ($_POST['newalbum']){ 
			$newablum = $_POST['newalbum'];
			$result = $wpdb->query(" INSERT INTO $wpdb->nggalbum (name) VALUES ('$newablum')");
			if ($result) $messagetext = '<font color="green">'.__('Update Successfully','nggallery').'</font>';
		} else $messagetext = '<font color="green">'.__('Update Successfully','nggallery').'</font>';
	}
	
	if ($_POST['delete']){
		$act_album = $_POST['act_album'];
		$result = $wpdb->query("DELETE FROM $wpdb->nggalbum WHERE id = '$act_album' ");
		if ($result) $messagetext = '<font color="green">'.__('Album deleted','nggallery').'</font>';
	}
	
	// message windows
	if(!empty($messagetext)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$messagetext.'</p></div>'; }
		
?>
<script type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/js/interface.js"></script>
<style type="text/css" media="all">@import "<?php echo NGGALLERY_URLPATH ?>css/nggallery.css";</style>
<script type="text/javascript">

$(document).ready(
	function()
	{
		$('div.groupWrapper').Sortable(
			{
				accept: 'groupItem',
				helperclass: 'sort_placeholder',
				opacity: 0.7,
				tolerance: 'intersect'
			}
		),

		$('.textarea1').Autoexpand([230,400]);
	}
);

function serialize(s)
{
	serial = $.SortSerialize(s);
	act_album = document.getElementById("act_album").value;
	mypostvars= "ajax=1&act_album="+act_album+"&"+serial.hash;
//	$.post(document.URL, mypostvars , function(data) { alert("Output: " + data); } );
	$.post(document.URL, mypostvars );


};
</script>

<style type="text/css" media="all">
@import 'http://localhost/saegolf/wp-content/plugins/nggallery/admin/js/portlets.css';
</style>
<div class="wrap">
	<h2><?php _e('Manage Albums', 'nggallery') ?></h2>
	<form id="selectalbum" method="POST" onsubmit="serialize(galleryContainer)">
		<table width="60%" border="0" cellspacing="3" cellpadding="3" >
			<tr>
				<th align="right">Select album</th>  
				<td>
					<select id="act_album" name="act_album" onchange="this.form.submit();">
						<option value="0" ><?php _e('No album selected', 'nggallery') ?></option>
						<?php
							$albumlist = $wpdb->get_results("SELECT * FROM $wpdb->nggalbum ORDER BY id ASC");
							if(is_array($albumlist)) {
								foreach($albumlist as $album) {
									if ($_POST['act_album'] == $album->id) $selected = 'selected="selected" ';
									else $selected = '';
									echo '<option value="'.$album->id.'" '.$selected.'>'.$album->name.'</option>'."\n";
								}
							}
						?>
					</select>
				</td> 
				<th align="right">Add new album</th>
				<td><input id="newalbum" name="newalbum" value="" /></td>
			</tr>
		</table>
		<p class="submit">
		<?php if ($_POST['act_album'] > 0){ ?>
		<input type="submit" name="delete" class="button delete" value="<?php _e('Delete') ?> &raquo;" onclick="javascript:check=confirm('<?php _e('Delete album ?','nggallery'); ?>');if(check==false) return false;"/>
		<?php } ?>
		<input type="submit" name="update" value="<?php _e('Update') ?> &raquo;" /><p>
	</form>	
	<br class="clear"/>
	<div class="container">
		<div id="selectContainer" class="groupWrapper">
		<h3><?php _e('Select Gallery', 'nggallery') ?></h3>
		<?php
		$gallerylist = $wpdb->get_results("SELECT gid FROM $wpdb->nggallery");
		
		if(is_array($gallerylist)) {
			if ( ($_POST['act_album'] == 0) or (!isset($_POST['act_album'])) ) {
				foreach($gallerylist as $gallery) {
					getgallerycontainer($gallery->gid);
				}
			} else {
				$act_album = $_POST['act_album'];
				$sortorder = $wpdb->get_var("SELECT sortorder FROM $wpdb->nggalbum WHERE id = '$act_album'");
				if (!empty($sortorder)) {
					$sort_array = unserialize($sortorder);
					foreach($gallerylist as $gallery) {
						if (!in_array($gallery->gid, $sort_array))
							getgallerycontainer($gallery->gid);
					}
				} else {
					foreach($gallerylist as $gallery) {
						getgallerycontainer($gallery->gid);
					}
				}
			}
		}
		?>
		</div><!-- /#select container -->

		<div id="galleryContainer" class="groupWrapper">
		<?php
			if ($_POST['act_album'] > 0){
				$act_album = $_POST['act_album'];
				$album = $wpdb->get_row("SELECT * FROM $wpdb->nggalbum WHERE id = '$act_album'");
				echo '<h3>'.__('Album Page ID', 'nggallery').' '.$album->id.' : '.$album->name.'</h3>'."\n";
				if (!empty($album->sortorder)) {
					$sort_array = unserialize($album->sortorder);
					if (is_array($sort_array)) {
						foreach($sort_array as $galleryid) {
							getgallerycontainer($galleryid);
						}
					}
				}
			} else echo '<h3>'.__('No Album selected', 'nggallery').'</h3>'."\n";
			?>
		</div><!-- /#gallery container -->

	</div><!-- /#container -->
</div><!-- /#wrap -->

<?php		
}

function getgallerycontainer($galleryid = 0) {
	global $wpdb;
	
	$gallery = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$galleryid'");

	if ($gallery) {

		// set image url
		$act_thumbnail_url 	= get_settings ('siteurl')."/".$gallery->path.ngg_get_thumbnail_folder($gallery->path, FALSE);
		$act_thumb_prefix   = ngg_get_thumbnail_prefix($gallery->path, FALSE);
		
		$post= get_post($gallery->pageid); 	
		$pagename = $post->post_title;	
		$filename = $wpdb->get_var("SELECT filename FROM $wpdb->nggpictures WHERE pid = '$gallery->previewpic'");
		if ($filename) $img = '<img src="'.$act_thumbnail_url.$act_thumb_prefix.$filename.'" />';
		else $img = '';
		echo ' 
		<div id="gid-'.$gallery->gid.'" class="groupItem">
			<div class="innerhandle">
				<div class="itemContent">
				<div class="inlinepicture">'.$img.'</div>
					<p><strong>'.__('ID', 'nggallery').' : </strong>'.$gallery->gid.'</p>
					<p><strong>'.__('Name', 'nggallery').' : </strong>'.$gallery->name.'</p>
					<p><strong>'.__('Title', 'nggallery').' : </strong>'.$gallery->title.'</p>
					<p><strong>'.__('Page', 'nggallery').' : </strong>'.$pagename.'</p>
				</div>
			</div>
		</div>
		'; 
	}
}
?>