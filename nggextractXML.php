<?php

/*
+----------------------------------------------------------------+
+	imageRotartor-XML V1.00
+	by Alex Rabe
+   	required for NextGEN Gallery
+----------------------------------------------------------------+
*/

// get the gallery id
$galleryID = $_GET['gid'];

$wpconfig = realpath("../../../wp-config.php");
if (!file_exists($wpconfig)) die; // stop when wp-config is not there

require_once($wpconfig);

function get_out_now() { exit; }
add_action('shutdown', 'get_out_now', -1);

global $wpdb;
$ngg_options = get_option('ngg_options');

// get gallery values
$act_gallery = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$galleryID' ");
$thepictures = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude = 0 ORDER BY '$ngg_options[galSort]' ASC");

// set gallery url
$folder_url 	= get_option ('siteurl')."/".$act_gallery->path."/";

// Create XML output
header("content-type:text/xml;charset=utf-8");

echo "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
echo "	<title>".$act_gallery->name."</title>\n";
echo "	<trackList>\n";

if (is_array ($thepictures)){
	foreach ($thepictures as $picture) {
		echo "		<track>\n";
		if (!empty($picture->description))	
		echo "			<title>".$picture->description."</title>\n";
		else if (!empty($picture->alttext))	
		echo "			<title>".$picture->alttext."</title>\n";
		else 
		echo "			<title>".$picture->filename."</title>\n";
		echo "			<location>".$folder_url.$picture->filename."</location>\n";
		echo "		</track>\n";
	}
}
 
echo "	</trackList>\n";
echo "</playlist>\n";

?>