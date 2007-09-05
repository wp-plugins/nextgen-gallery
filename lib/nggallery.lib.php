<?php

/**
 * Image PHP class for the WordPress plugin NextGEN Gallery
 * nggallery.lib.php
 * 
 * @author 		Alex Rabe 
 * @copyright 	Copyright 2007
 * 
 */
	  
class nggImage{
	
	/**** Public variables ****/
	
	var $errmsg			=	"";			// Error message to display, if any
    var $error			=	FALSE; 		// Error state
    var $imagePath		=	"";			// URL Path to the image
    var $thumbPath		=	"";			// URL Path to the thumbnail
    var $thumbPrefix	=	"";			// FolderPrefix to the thumbnail
    var $thumbFolder	=	"";			// Foldername to the thumbnail
    var $href			=	"";			// A href link code
	
	/**** Image Data ****/
    var $galleryid		=	0;			// Gallery ID
    var $imageID		=	0;			// Image ID	
    var $filename		=	"";			// Image filename
    var $description	=	"";			// Image description	
    var $alttext		=	"";			// Image alttext	
    var $exclude		=	"";			// Image exclude
    var $thumbcode		=	"";			// Image effect code

	/**** Gallery Data ****/
    var $name			=	"";			// Gallery name
	var $path			=	"";			// Gallery path	
	var $title			=	"";			// Gallery title
	var $pageid			=	0;			// Gallery page ID
	var $previewpic		=	0;			// Gallery preview pic				
	
 	function nggImage($imageID = '0') {
 		
 		global $wpdb;
 		
 		//initialize variables
        $this->imageID              = $imageID;
 		
 		// get image values
 		$imageData = $wpdb->get_row("SELECT * FROM $wpdb->nggpictures WHERE pid = '$this->imageID' ") or $this->error = true;
		if($this->error == false)
			foreach ($imageData as $key => $value)
				$this->$key = $value ;

		// get gallery values
		$galleryData = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$this->galleryid' ") or $this->error = true;
		if($this->error == false)
			foreach ($galleryData as $key => $value)
				$this->$key = $value ;	
		
		if($this->error == false) {
			// set gallery url
			$this->get_thumbnail_folder($this->path, FALSE);
			$this->imagePath 	= get_option ('siteurl')."/".$this->path."/".$this->filename;
			$this->thumbPath 	= get_option ('siteurl')."/".$this->path.$this->thumbFolder.$this->thumbPrefix.$this->filename;
 		}
 	}
	
	/**********************************************************/
	function get_thumbnail_folder($gallerypath, $include_Abspath = TRUE) {
		//required for myGallery import :-)
		
		if (!$include_Abspath) 
			$gallerypath = WINABSPATH.$gallerypath;
			
		if (is_dir($gallerypath."/thumbs")) {
			$this->thumbFolder 	= "/thumbs/";
			$this->thumbPrefix 	= "thumbs_";
			return TRUE;
		} 
			 
		if (is_dir($gallerypath."/tumbs")) {
			$this->thumbFolder	= "/tumbs/";
			$this->thumbPrefix 	= "tmb_";
			return TRUE;
		}
		
		// create the folder if it not exist
		if (!SAFE_MODE) {
			if (!is_dir($gallerypath."/thumbs")) {
				mkdir($gallerypath."/thumbs");
				$this->thumbFolder	= "/thumbs/";
				$this->thumbPrefix 	= "thumbs_";
				return TRUE;
			}
		}
		
		return FALSE;
		
	}
	
	function get_thumbcode($galleryname) {
		// read the option setting
		$ngg_options = get_option('ngg_options');
		
		// get the effect code
		if ($ngg_options[thumbEffect] != "none") $this->thumbcode = stripslashes($ngg_options[thumbCode]);
		if ($ngg_options[thumbEffect] == "highslide") $this->thumbcode = str_replace("%GALLERY_NAME%", "'".$galleryname."'", $this->thumbcode);
		else $this->thumbcode = str_replace("%GALLERY_NAME%", $galleryname, $this->thumbcode);
		
		return $this->thumbcode;
	}
	
	function get_href_link() {
		// create the a href link from the picture
		$this->href  = "\n".'<a href="'.$this->imagePath.'" title="'.stripslashes($this->description).'" '.$this->get_thumbcode($this->name).'>'."\n\t";
		$this->href .= '<img alt="'.$this->alttext.'" src="'.$this->imagePath.'"/>'."\n".'</a>'."\n";

		return $this->href;
	}

	function get_href_thumb_link() {
		// create the a href link with the thumbanil
		$this->href  = "\n".'<a href="'.$this->imagePath.'" title="'.stripslashes($this->description).'" '.$this->get_thumbcode($this->name).'>'."\n\t";
		$this->href .= '<img alt="'.$this->alttext.'" src="'.$this->thumbPath.'"/>'."\n".'</a>'."\n";

		return $this->href;
	}
}

/**
 * Main PHP class for the WordPress plugin NextGEN Gallery
 * nggallery.lib.php
 * 
 * @author 		Alex Rabe 
 * @copyright 	Copyright 2007
 * 
 */

class nggallery {
	
	/**********************************************************/
	// remove page break
	/**********************************************************/
	function ngg_nl2br($string) {
		
		$string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
		
		return $string;

	}
	
	/**********************************************************/
	// Show a error messages
	/**********************************************************/
	function show_error($message) {
		echo '<div class="fade error" id="message"><p>'.$message.'</p></div>'."\n";
	}
	
	/**********************************************************/
	// Show a system messages
	/**********************************************************/
	function show_message($message)
	{
		echo '<div class="fade updated" id="message"><p>'.$message.'</p></div>'."\n";
	}

	/**********************************************************/
	// remove some umlauts
	/**********************************************************/
	function remove_umlauts($filename) {
	
		$cleanname = str_replace(
		array('ä',   'ö',   'ü',   'Ä',   'Ö',   'Ü',   'ß',   ' '), 
		array('%E4', '%F6', '%FC', '%C4', '%D6', '%DC', '%DF', '%20'),
		utf8_decode($filename)
		);
		
		return $cleanname;
	}
	
	/**********************************************************/
	// get the thumbnail url to the image
	//TODO:Combine in one class
	/**********************************************************/
	function get_thumbnail_url($imageID){
		// get the complete url to the thumbnail
		global $wpdb;
		
		// get gallery values
		$galleryID = $wpdb->get_var("SELECT galleryid FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
		$fileName = $wpdb->get_var("SELECT filename FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
		$picturepath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$galleryID' ");
	
		// set gallery url
		$folder_url 	= get_option ('siteurl')."/".$picturepath.nggallery::get_thumbnail_folder($picturepath, FALSE);
		$thumb_prefix   = nggallery::get_thumbnail_prefix($picturepath, FALSE);
		$thumbnailURL	= $folder_url.$thumb_prefix.$fileName;
		
		return $thumbnailURL;
	}
	
	/**********************************************************/
	// get the complete url to the image
	/**********************************************************/
	function get_image_url($imageID){
		
		global $wpdb;
		
		// get gallery values
		$galleryID = $wpdb->get_var("SELECT galleryid FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
		$fileName = $wpdb->get_var("SELECT filename FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
		$picturepath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$galleryID' ");
	
		// set gallery url
		$imageURL 	= get_option ('siteurl')."/".$picturepath."/".$fileName;
		
		return $imageURL;	
	}

	/**********************************************************/
	// get the thumbnail folder
	/**********************************************************/
	function get_thumbnail_folder($gallerypath, $include_Abspath = TRUE) {
		//required for myGallery import :-)
		
		if (!$include_Abspath) $gallerypath = WINABSPATH.$gallerypath;
		if (is_dir($gallerypath."/thumbs")) return "/thumbs/";
		if (is_dir($gallerypath."/tumbs")) return "/tumbs/";
		if (!SAFE_MODE) {
			if (!is_dir($gallerypath."/thumbs")) {
				mkdir($gallerypath."/thumbs");
				return "/thumbs/";
			}
		}
		return FALSE;
		
	}
	
	/**********************************************************/
	// get the thumbnail prefix
	/**********************************************************/
	function get_thumbnail_prefix($gallerypath, $include_Abspath = TRUE) {
		//required for myGallery import :-)
	
		if (!$include_Abspath) $gallerypath = WINABSPATH.$gallerypath;
		if (is_dir($gallerypath."/thumbs")) return "thumbs_";
		if (is_dir($gallerypath."/tumbs")) return "tmb_";
	
		return FALSE;
		
	}

	/**********************************************************/
	// get the effect code
	/**********************************************************/
	function get_thumbcode($groupname) {

		$ngg_options = get_option('ngg_options');
		
		// get the effect code
		if ($ngg_options['thumbEffect'] != "none") $thumbcode = stripslashes($ngg_options['thumbCode']);
		if ($ngg_options['thumbEffect'] == "highslide") $thumbcode = str_replace("%GALLERY_NAME%", "'".$groupname."'", $thumbcode);
		else $thumbcode = str_replace("%GALLERY_NAME%", $groupname, $thumbcode);
	
		return $thumbcode;
		
	}
	
	/**********************************************************/
	// create the complete navigation
	/**********************************************************/
	function create_navigation($page, $totalElement, $maxElement = 0) {
	 	$navigation = "";
	 	
		 	if ($maxElement > 0) {
			$total = $totalElement;
			$args['page'] = get_the_ID();
					
			// create navigation	
			if ( $total > $maxElement ) {
				$total_pages = ceil( $total / $maxElement );
				$r = '';
				if ( 1 < $page ) {
					$args['nggpage'] = ( 1 == $page - 1 ) ? FALSE : $page - 1;
					$r .=  '<a class="prev" href="'. htmlspecialchars( add_query_arg( $args ) ) . '">&#9668;</a>';
				}
				if ( ( $total_pages = ceil( $total / $maxElement ) ) > 1 ) {
					for ( $page_num = 1; $page_num <= $total_pages; $page_num++ ) {
						if ( $page == $page_num ) {
							$r .=  '<span>' . $page_num . '</span>';
						} else {
							$p = false;
							if ( $page_num < 3 || ( $page_num >= $page - 3 && $page_num <= $page + 3 ) || $page_num > $total_pages - 3 ) {
								$args['nggpage'] = ( 1 == $page_num ) ? FALSE : $page_num;
								$r .= '<a class="page-numbers" href="' . htmlspecialchars( add_query_arg( $args ) ) . '">' . ( $page_num ) . '</a>';
								$in = true;
							} elseif ( $in == true ) {
								$r .= '<span>...</span>';
								$in = false;
							}
						}
					}
				}
				if ( ( $page ) * $maxElement < $total || -1 == $total ) {
					$args['nggpage'] = $page + 1;
					$r .=  '<a class="next" href="' . htmlspecialchars( add_query_arg( $args ) ) . '">&#9658;</a>';
				}
				
				$navigation = "<div class='ngg-navigation'>$r</div>";
			} else {
				$navigation = "<div class='ngg-clear'></div>"."\n";
			}
		}
		
		return $navigation;
	}
	
}

/**
 * Tag PHP class for the WordPress plugin NextGEN Gallery
 * nggallery.lib.php
 * 
 * @author 		Alex Rabe 
 * @copyright 	Copyright 2007
 * 
 */
 
class ngg_Tags {
	
	var $sluglist = array ();
	var $img_slugs = array ();
	var $img_tags = array ();
	var $taglist = "";
	
	function ngg_Tags() {
		return $this->__construct();
	}

	function __construct() {
		// First get all slugs in a array
		$this->get_sluglist();
	}
	
	function __destruct() {
		// Clean varlist
		unset ($this->sluglist, $this->img_slugs, $this->img_tags, $this->taglist );
	}

	function get_sluglist() {
		// read the slugs and cache the array
		global $wpdb;
		
		$slugarray = $wpdb->get_results("SELECT id, slug FROM $wpdb->nggtags");
		if (is_array($slugarray)){
			foreach($slugarray as $element)
				$this->sluglist[$element->id] = $element->slug;
		}
		
		return $this->sluglist;
	}
	
	function get_tags_from_image($id) {
		// read the tags and slugs
		global $wpdb;
		
		$this->taglist = "";
		$this->img_slugs = $this->img_tags = array();
	
		$tagarray = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggpic2tags AS t INNER JOIN $wpdb->nggtags AS tt ON t.tagid = tt.id WHERE t.picid = '$id' ORDER BY tt.slug ASC ");
	
		if (is_array($tagarray)){
			foreach($tagarray as $element) {
				$this->img_slugs[$element->id] = $element->slug;
				$this->img_tags[$element->id] = $element->name;
			}
			$this->taglist = implode(", ", $this->img_tags);
		}
		
		return $this->taglist;
	}
	
	function add_tag($tag) {
		// add a tag if not exist and return the id
		global $wpdb;

		$tagid = false;
		
		$tag = trim($tag);
		$slug = sanitize_title($tag);

		// look for tag in the cached list and get id
		$tagid = array_search($slug, $this->sluglist);
		
		// if tag is not found add to database
		if (!$tagid) {
			if (!empty ($tag)) {
				$wpdb->query("INSERT INTO $wpdb->nggtags (name, slug) VALUES ('$tag', '$slug')");
				$tagid = (int) $wpdb->insert_id;
				// Update also sluglist
				if ($tagid)	$this->sluglist[$tagid] = $slug;
			}
		}
		
		return $tagid;
	}
	
	function add_relationship($pic_id = 0, $tag_id = 0) {
		// add the relation between image and tag
		global $wpdb;

		if (($pic_id != 0) && ($tag_id != 0)){
			// checkfor duplicate first
			$exist = $wpdb->get_var("SELECT picid FROM $wpdb->nggpic2tags WHERE picid = '$pic_id' AND tagid = '$tag_id' ");
			if (!$exist)
				$wpdb->query("INSERT INTO $wpdb->nggpic2tags (picid, tagid) VALUES ('$pic_id', '$tag_id')");
		}
	}

	function remove_relationship($pic_id = 0, $slugarray, $cached = false) {
		// remove the relation between image and tag
		global $wpdb;

		if (!is_array($slugarray))
			$slugarray = array($slugarray);
		
		// get all tags if we didnt chaed them already
		if (!$cached)
			$this->get_tags_from_image($pic_id);
		
		$delete_ids = array();
			
		foreach ($slugarray as $slug) {
			// look for tag in the cached list and get ids
			// require frst get_tags_from_image()
			$tagid = array_search($slug, $this->sluglist);
			if ($tagid)
				$delete_ids[] = $tagid;
		}

		$delete_list = "'" . implode("', '", $delete_ids) . "'";
		$wpdb->query("DELETE FROM $wpdb->nggpic2tags WHERE picid = '$pic_id' AND tagid IN ($delete_list)");

	}

	function remove_unused_tags() {
		// remove tags which are not longer used
		global $wpdb;
		
		// get all used tags
		$tagarray = $wpdb->get_results("SELECT tt.* FROM $wpdb->nggpic2tags AS t INNER JOIN $wpdb->nggtags AS tt ON t.tagid = tt.id ");
		if (is_array($tagarray)){
			// remove used items from sluglist
			foreach($tagarray as $element)
				unset ($this->sluglist[$element->id]);
			// remove now all unused tags	
			$delete_ids = array();
			foreach($this->sluglist as $key=>$value)
				$delete_ids[] = $key;
			$delete_list = "'" . implode("', '", $delete_ids) . "'";
			$wpdb->query("DELETE FROM $wpdb->nggtags WHERE id IN ($delete_list)");		
		}
	}
	
	function get_images($taglist) {
		// return the images based on the tag
		global $wpdb;
		
		// extract it into a array
		$taglist = explode(",", $taglist);
		
		if (!is_array($taglist))
			$taglist = array($taglist);
	
		$taglist = array_map('trim', $taglist);
		$new_slugarray = array_map('sanitize_title', $taglist);
	
		$sluglist   = "'" . implode("', '", $new_slugarray) . "'";
			
		$picarray = array();
		
		// first get all picture with this tag
		$picids = $wpdb->get_col("SELECT t.picid FROM $wpdb->nggpic2tags AS t INNER JOIN $wpdb->nggtags AS tt ON t.tagid = tt.id WHERE tt.slug IN ($sluglist) ORDER BY t.picid ASC ");
		if (is_array($picids)){
			// now get all pictures
			$piclist = "'" . implode("', '", $picids) . "'";
			$picarray = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggpictures AS t INNER JOIN $wpdb->nggallery AS tt ON t.galleryid = tt.gid WHERE t.pid IN ($piclist) ORDER BY t.pid ASC ");
		}
		
		return $picarray;
	}
	
	function get_album_images($taglist) {
		// return one images based on the tag
		// required for a tag based album overview
		global $wpdb;
		
		// extract it into a array
		$taglist = explode(",", $taglist);
		
		if (!is_array($taglist))
			$taglist = array($taglist);
	
		$taglist = array_map('trim', $taglist);
		$new_slugarray = array_map('sanitize_title', $taglist);
		
		$picarray = array();
		
		foreach($new_slugarray as $slug) {
			// get random picture of tag
			$picture = $wpdb->get_row("SELECT t.picid, t.tagid, tt.name, tt.slug FROM $wpdb->nggpic2tags AS t INNER JOIN $wpdb->nggtags AS tt ON t.tagid = tt.id WHERE tt.slug = '$slug' ORDER BY rand() limit 1 ");	
			if ($picture) {
				$picdata = $wpdb->get_row("SELECT t.*, tt.* FROM $wpdb->nggpictures AS t INNER JOIN $wpdb->nggallery AS tt ON t.galleryid = tt.gid WHERE t.pid = $picture->picid");		
				$picarray[] = array_merge((array)$picdata, (array)$picture);
			}
		}

		return $picarray;

	}

}
?>