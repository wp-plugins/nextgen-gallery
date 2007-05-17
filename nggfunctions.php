<?php

function searchnggallerytags($content) {

	global $wpdb;
		
	$search = "/\[singlepic=(\d+)(|,\d+|,)(|,\d+|,)(|,watermark|,web20|,)(|,right|,left|,)\]/i";
	
	if	(preg_match_all($search, $content, $matches)) {
		
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				// check for correct id
				$result = $wpdb->get_var("SELECT filename FROM $wpdb->nggpictures WHERE pid = '$v0' ");
				if($result){
					$search = $matches[0][$key];
					$replace= nggSinglePicture($v0,$matches[2][$key],$matches[3][$key],$matches[4][$key],$matches[5][$key]);
					$content= str_replace ($search, $replace, $content);
				}
			}	
		}
	}// end singelpic

	$search = "/\[album\s*=\s*(\w+|^\+)(|,extend|,compact)\]/i";
	
	if	(preg_match_all($search, $content, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				// check for album id
				$albumID = $wpdb->get_var("SELECT id FROM $wpdb->nggalbum WHERE id = '$v0' ");
				if(!$albumID) $albumID = $wpdb->get_var("SELECT id FROM $wpdb->nggalbum WHERE name = '$v0' ");
				if($albumID) {
					$search = $matches[0][$key];
					$replace= nggShowAlbum($albumID,$matches[2][$key]);
					$content= str_replace ($search, $replace, $content);
				}
			}	
		}
	}// end album

	$search = "/\[gallery\s*=\s*(\w+|^\+)\]/i";
	
	if	(preg_match_all($search, $content, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				// check for gallery id
				$galleryID = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE gid = '$v0' ");
				if(!$galleryID) $galleryID = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE name = '$v0' ");
				if($galleryID) {
					$search = $matches[0][$key];
					$replace= nggShowGallery($galleryID);
					$content= str_replace ($search, $replace, $content);
				}
			}	
		}
	}// end gallery
	
	$search = "/\[slideshow\s*=\s*(\w+|^\+)(|,(\d+)|,)(|,(\d+))\]/i";

	if	(preg_match_all($search, $content, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				// check for gallery id
				$galleryID = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE gid = '$v0' ");
				if(!$galleryID) $galleryID = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE name = '$v0' ");
				if($galleryID) {
					$search = $matches[0][$key];
					// get the size if they are set
			 		$ssWidth  =  $matches[3][$key]; 
					$ssHeight =  $matches[5][$key];
					$replace= nggShowSlideshow($galleryID,$irWidth,$irHeight);
					$content= str_replace ($search, $replace, $content);
				}
			}	
		}
	}// end slideshow

	return $content;
}// end search content

/**********************************************************/
function nggShowSlideshow($galleryID,$irWidth,$irHeight) {
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');
	
	if (empty($irWidth) ) $irWidth = $ngg_options[irWidth];
	if (empty($irHeight)) $irHeight = $ngg_options[irHeight];

	$replace .= "\n".'<div class="slideshow" id="ngg_slideshow'.$galleryID.'">';
	$replace .= '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see the slideshow.</p>';
    $replace .= "\n\t".'<script type="text/javascript">';
//  $replace .= "\n\t".'<!--';
	$replace .= "\n\t".'//<![CDATA[';
	$replace .= "\n\t\t".'var so = new SWFObject("'.NGGALLERY_URLPATH.'imagerotator.swf", "ngg_slideshow'.$galleryID.'", "'.$irWidth.'", "'.$irHeight.'", "7", "#'.$ngg_options[irBackcolor].'");';
	$replace .= "\n\t\t".'so.addParam("wmode", "opaque");';
	$replace .= "\n\t\t".'so.addVariable("file", "'.NGGALLERY_URLPATH.'nggextractXML.php?gid='.$galleryID.'");';
	if (!$ngg_options[irShuffle]) $replace .= "\n\t\t".'so.addVariable("shuffle", "false");';
	if ($ngg_options[irLinkfromdisplay]) $replace .= "\n\t\t".'so.addVariable("linkfromdisplay", "false");';
	if ($ngg_options[irShownavigation]) $replace .= "\n\t\t".'so.addVariable("shownavigation", "true");';
	if ($ngg_options[irShowicons]) $replace .= "\n\t\t".'so.addVariable("showicons", "true");';
	$replace .= "\n\t\t".'so.addVariable("overstretch", "'.$ngg_options[irOverstretch].'");';
	$replace .= "\n\t\t".'so.addVariable("backcolor", "0x'.$ngg_options[irBackcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("frontcolor", "0x'.$ngg_options[irFrontcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("lightcolor", "0x'.$ngg_options[irLightcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("rotatetime", "'.$ngg_options[irRotatetime].'");';
	$replace .= "\n\t\t".'so.addVariable("transition", "'.$ngg_options[irTransition].'");';
	$replace .= "\n\t\t".'so.addVariable("width", "'.$irWidth.'");';
	$replace .= "\n\t\t".'so.addVariable("height", "'.$irHeight.'");'; 
	$replace .= "\n\t\t".'so.write("ngg_slideshow'.$galleryID.'");';
	$replace .= "\n\t".'//]]>';
//	$replace .= "\n\t".'-->';
	$replace .= "\n\t".'</script>';
	$replace .= '</div>'."\n";
		
	return $replace;
}

/**********************************************************/
function nggShowGallery($galleryID) {
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');

	// Get option
	$maxElement = $ngg_options[galImages];
	$thumbwidth = $ngg_options[thumbwidth];
	$thumbheight = $ngg_options[thumbheight];
	
	// set thumb size 
	$thumbsize = "";
	if ($ngg_options[thumbfix])  $thumbsize = 'style="width:'.$thumbwidth.'px; height:'.$thumbheight.'px;"';
	if ($ngg_options[thumbcrop]) $thumbsize = 'style="width:'.$thumbwidth.'px; height:'.$thumbwidth.'px;"';
	
	// get gallery values
	$act_gallery = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$galleryID' ");

	// get the effect code
	if ($ngg_options[thumbEffect] != "none") $thumbcode = stripslashes($ngg_options[thumbCode]);
	if ($ngg_options[thumbEffect] == "highslide") $thumbcode = str_replace("%GALLERY_NAME%", "'".$act_gallery->name."'", $thumbcode);
	else $thumbcode = str_replace("%GALLERY_NAME%", $act_gallery->name, $thumbcode);

	// set gallery url
	$folder_url 	= get_option ('siteurl')."/".$act_gallery->path."/";
	$thumbnailURL 	= get_option ('siteurl')."/".$act_gallery->path.ngg_get_thumbnail_folder($act_gallery->path, FALSE);
	$thumb_prefix   = ngg_get_thumbnail_prefix($act_gallery->path, FALSE);

	// slideshow first
	if ( !isset( $_GET['show'] ) AND ($ngg_options[galShowOrder] == 'slide')) $_GET['show'] = slide;
	// show a slide show
	if ( isset( $_GET['show'] ) AND ($_GET['show'] == slide) ) {
		$getvalue['show'] = "gallery";
		$gallerycontent  = '<div class="ngg-galleryoverview">';
		$gallerycontent .= '<a class="slideshowlink" href="' . add_query_arg($getvalue) . '">'.$ngg_options[galTextGallery].'</a>';
		$gallerycontent .= nggShowSlideshow($galleryID,$ngg_options[irWidth],$ngg_options[irHeight]);
		$gallerycontent .= '</div>'."\n";
		$gallerycontent .= '<div style="clear:both;"></div>'."\n";
		return $gallerycontent;
	}

 	// check for page navigation
 	if ($maxElement > 0) {	
		if ( isset( $_GET['nggpage'] ) )	$page = (int) $_GET['nggpage'];
		else $page = 1; 
	 	$start = $offset = ( $page - 1 ) * $maxElement;
	
		$picturelist = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ORDER BY $ngg_options[galSort] ASC LIMIT $start, $maxElement ");
		$total = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ");	
	} else {
		$picturelist = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ORDER BY $ngg_options[galSort] ASC ");	
	}
	
	if (is_array($picturelist)) {
		
		// create navigation	
		if ( $total > $maxElement ) {
			$total_pages = ceil( $total / $maxElement );
			$r = '';
			if ( 1 < $page ) {
				$args['nggpage'] = ( 1 == $page - 1 ) ? FALSE : $page - 1;
				$r .=  '<a class="prev" href="'. add_query_arg( $args ) . '">&#9668;</a>';
			}
			if ( ( $total_pages = ceil( $total / $maxElement ) ) > 1 ) {
				for ( $page_num = 1; $page_num <= $total_pages; $page_num++ ) {
					if ( $page == $page_num ) {
						$r .=  '<span>' . $page_num . '</span>';
					} else {
						$p = false;
						if ( $page_num < 3 || ( $page_num >= $page - 3 && $page_num <= $page + 3 ) || $page_num > $total_pages - 3 ) {
							$args['nggpage'] = ( 1 == $page_num ) ? FALSE : $page_num;
							$r .= '<a class="page-numbers" href="' . add_query_arg($args) . '">' . ( $page_num ) . '</a>';
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
				$r .=  '<a class="next" href="' . add_query_arg($args) . '">&#9658;</a>';
			}
			
			$navigation = "<div class='ngg-navigation'>$r</div>";
		}

	$gallerycontent  = '<div class="ngg-galleryoverview">';
	if (($ngg_options[galShowSlide]) AND (NGGALLERY_IREXIST)) {
		$getvalue['show'] = "slide";
		$gallerycontent .= '<a class="slideshowlink" href="' . add_query_arg($getvalue) . '">'.$ngg_options[galTextSlide].'</a>';
	}
	
	foreach ($picturelist as $picture) {
		$picturefile = str_replace(
			array('ä',   'ö',   'ü',   'Ä',   'Ö',   'Ü',   'ß',   ' '), 
			array('%E4', '%F6', '%FC', '%C4', '%D6', '%DC', '%DF', '%20'),
			utf8_decode($picture->filename)
		);
		$gallerycontent .= '<div class="ngg-gallery-thumbnail-box">'."\n\t";
		$gallerycontent .= '<div class="ngg-gallery-thumbnail">'."\n\t";
		$gallerycontent .= '<a href="'.$folder_url.$picturefile.'" title="'.$picture->alttext.'" '.$thumbcode.' >';
		$gallerycontent .= '<img title="'.$picture->alttext.'" alt="'.$picture->alttext.'" src="'.$thumbnailURL.$thumb_prefix.$picture->filename.'" '.$thumbsize.' />';
		$gallerycontent .= '</a>'."\n".'</div>'."\n".'</div>'."\n";
		}
	$gallerycontent .= '</div>'."\n";
	$gallerycontent .= '<div style="clear:both;"></div>'."\n";
	$gallerycontent .= $navigation;	
	}
		
	return $gallerycontent;
}

/**********************************************************/
function nggShowAlbum($albumID,$mode = "extend") {
	
	global $wpdb;

	$mode = ltrim($mode,',');
	$sortorder = $wpdb->get_var("SELECT sortorder FROM $wpdb->nggalbum WHERE id = '$albumID' ");
	if (!empty($sortorder)) {
		$gallery_array = unserialize($sortorder);
//		$gallery_array = $wpdb->get_col("SELECT gid FROM $wpdb->nggallery ");
	} 

	$albumcontent = '<div class="ngg-albumoverview">';
	if (is_array($gallery_array)) {
	foreach ($gallery_array as $galleryID) {
		$albumcontent .= nggCreateGalleryDiv($galleryID,$mode);	
		}
	}
	$albumcontent .= '</div>'."\n";
	$albumcontent .= '<div style="clear:both;"></div>'."\n";
	
	return $albumcontent;
}

/**********************************************************/
function nggCreateGalleryDiv($galleryID,$mode = "extend") {
	// create a gallery overview div
	
	global $wpdb;
	
	$gallerycontent = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$galleryID' ");
	
	if ($gallerycontent) {
 		if ($mode == "compact") {
			if ($gallerycontent->previewpic != 0)
				$insertpic = '<img class="Thumb" width="91" height="68" alt="'.$gallerycontent->title.'" src="'.ngg_get_thumbnail_url($gallerycontent->previewpic).'"/>';
			else 
				$insertpic = __('Watch gallery', 'nggallery');
 			$counter = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures WHERE galleryid = '$galleryID'");
 			$galleryoutput = '	
				<div class="ngg-album-compact">
					<div class="ngg-album-compactbox">
						<div class="ngg-album-link">
							<a class="Link" href="'.get_permalink($gallerycontent->pageid).'">'.$insertpic.'</a>
						</div>
					</div>
					<h4><a class="ngg-album-desc" title="'.$gallerycontent->title.'" href="'.get_permalink($gallerycontent->pageid).'">'.$gallerycontent->title.'</a></h4>
					<p><b>'.$counter.'</b> '.__('Photos', 'nggallery').'</p></div>';
		} else {
			// mode extend
			if ($gallerycontent->previewpic != 0)
				$insertpic = '<img src="'.ngg_get_thumbnail_url($gallerycontent->previewpic).'" alt="'.$gallerycontent->title.'" title="'.$gallerycontent->title.'"/>';
			else 
				$insertpic = __('Watch gallery', 'nggallery');
			$galleryoutput = '
			<div class="ngg-album">
				<div class="ngg-albumtitle"><a href="'.get_permalink($gallerycontent->pageid).'">'.$gallerycontent->title.'</a></div>
				<div class="ngg-albumcontent">
					<div class="ngg-thumbnail"><a href="'.get_permalink($gallerycontent->pageid).'">'.$insertpic.'</a></div>
					<div class="ngg-description"><p>'.$gallerycontent->description.'</p></div>'."\n".'</div>'."\n".'</div>';

		}
	}
	
	return $galleryoutput;
}

/**********************************************************/
function nggSinglePicture($imageID,$width=250,$height=250,$mode="",$float="") {
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');

	// remove the comma
	$float = ltrim($float,',');
	$mode = ltrim($mode,',');
	$width = ltrim($width,',');
	$height = ltrim($height,',');

	// get picturedata
	$picture = $wpdb->get_row("SELECT * FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
	
	// add fullsize picture as link
	if ($ngg_options[imgSinglePicLink]) {	
		// get gallery values
		$act_gallery = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$picture->galleryid' ");
		
		// set gallery url
		$folder_url = get_option ('siteurl')."/".$act_gallery->path."/";
		
	    // get the effect code
		if ($ngg_options[thumbEffect] != "none") $thumbcode = stripslashes($ngg_options[thumbCode]);
		if ($ngg_options[thumbEffect] == "highslide") $thumbcode = str_replace("%GALLERY_NAME%", "'".$act_gallery->name."'", $thumbcode);
		else $thumbcode = str_replace("%GALLERY_NAME%", $act_gallery->name, $thumbcode);
		
		$link  = '<a href="'.$folder_url.$picture->filename.'" title="'.$picture->alttext.'" '.$thumbcode.' >';
	}

	// add float to img
	if (!empty($float)) {
		switch ($float) {
		
		case 'left': $float=' style="float:left;" ';
		break;
		
		case 'right': $float=' style="float:right;" ';
		break;
		
		default: $float='';
		break;
		}
	}

	$content = $link . '<img class="ngg-singlepic" src="'.NGGALLERY_URLPATH.'nggshow.php?pid='.$imageID.'&amp;width='.$width.'&amp;height='.$height.'&amp;mode='.$mode.'" alt="'.$picture->alttext.'" title="'.$picture->alttext.'"'.$float.' />';

	if ($ngg_options[imgSinglePicLink]) $content .= '</a>';
	
	return $content;
}

/**********************************************************/
// Global function 
/**********************************************************/
function ngg_get_thumbnail_url($imageID){
	// get the complete url to the thumbnail
	global $wpdb;
	
	// get gallery values
	$galleryID = $wpdb->get_var("SELECT galleryid FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
	$fileName = $wpdb->get_var("SELECT filename FROM $wpdb->nggpictures WHERE pid = '$imageID' ");
	$picturepath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$galleryID' ");

	// set gallery url
	$folder_url 	= get_option ('siteurl')."/".$picturepath.ngg_get_thumbnail_folder($picturepath, FALSE);
	$thumb_prefix   = ngg_get_thumbnail_prefix($picturepath, FALSE);
	$thumbnailURL	= $folder_url.$thumb_prefix.$fileName;
	
	return $thumbnailURL;
}

/**********************************************************/
function ngg_get_image_url($imageID){
	// get the complete url to the image
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
function ngg_get_thumbnail_folder($gallerypath, $include_Abspath = TRUE) {
	//required for myGallery import :-)
	
	if (!$include_Abspath) $gallerypath = WINABSPATH.$gallerypath;
	if (is_dir($gallerypath."/thumbs")) return "/thumbs/";
	if (is_dir($gallerypath."/tumbs")) return "/tumbs/";
	if (!is_dir($gallerypath."/thumbs")) {
		mkdir($gallerypath."/thumbs");
		return "/thumbs/";
	}
	return FALSE;
	
}

/**********************************************************/
function ngg_get_thumbnail_prefix($gallerypath, $include_Abspath = TRUE) {
	//required for myGallery import :-)

	if (!$include_Abspath) $gallerypath = WINABSPATH.$gallerypath;
	if (is_dir($gallerypath."/thumbs")) return "thumbs_";
	if (is_dir($gallerypath."/tumbs")) return "tmb_";

	return FALSE;
	
}

?>
