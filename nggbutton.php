<?php

$wpconfig = realpath("../../../wp-config.php");

if (!file_exists($wpconfig))  {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}// stop when wp-config is not there

require_once($wpconfig);
require_once(ABSPATH.'/wp-admin/admin.php');

global $wpdb;
$ngg_options = get_option('ngg_options');

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$lang_insert_link_title}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>js/tinymce.js"></script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('gallerytag').focus();" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="NextGEN" action="#">
	<div class="tabs">
		<ul>
			<li id="gallery_tab" class="current"><span><a href="javascript:mcTabs.displayTab('gallery_tab','gallery_panel');" onmousedown="return false;"><?php _e("Gallery", 'nggallery'); ?></a></span></li>
			<li id="album_tab"><span><a href="javascript:mcTabs.displayTab('album_tab','album_panel');" onmousedown="return false;"><?php _e("Album", 'nggallery'); ?></a></span></li>
			<li id="singlepic_tab"><span><a href="javascript:mcTabs.displayTab('singlepic_tab','singlepic_panel');" onmousedown="return false;"><?php _e("Picture", 'nggallery'); ?></a></span></li>
		</ul>
	</div>
	
	<div class="panel_wrapper">
		<!-- gallery panel -->
		<div id="gallery_panel" class="panel current">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="target"><?php _e("Select gallery", 'nggallery'); ?></label></td>
            <td><select id="gallerytag" name="gallerytag" style="width: 200px">
                <option value="_self">gallery</option>
                <option value="_blank">text 1</option>
            </select></td>
          </tr>
          <tr>
            <td nowrap="nowrap"><label for="linktitle"><?php _e("TEXT ABC", 'nggallery'); ?></label></td>
            <td><input id="linktitle" name="linktitle" type="text" value="" style="width: 200px" /></td>
          </tr>
          <tr id="styleSelectRow">
            <td><label for="styleSelect"><?php _e("TEXT ABC", 'nggallery'); ?></label></td>
            <td>
			 <select id="styleSelect" name="styleSelect">
                <option value="" selected="selected"><?php __("TEXT ABC", 'nggallery'); ?></option>
             </select></td>
          </tr>
        </table>
		</div>
		<!-- gallery panel -->
		
		<!-- album panel -->
		<div id="album_panel" class="panel">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="target2"><?php _e("Select album", 'nggallery'); ?></label></td>
            <td><select id="target2" name="target2" style="width: 200px">
                <option value="zweirt">album</option>
            </select></td>
          </tr>
        </table>
		</div>
		<!-- album panel -->
		
		<!-- single pic panel -->
		<div id="singlepic_panel" class="panel">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="target3"><?php _e("Select picture", 'nggallery'); ?></label></td>
            <td><select id="target3" name="target3" style="width: 200px">
                <option value="start">singlepic</option>
            </select></td>
          </tr>
        </table>
		</div>
		<!-- single pic panel -->
	</div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'nggallery'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'nggallery'); ?>" onclick="insertNGGLink();" />
		</div>
	</div>
</form>
</body>
</html>
