<?php

function searchnggallerytags($content) {

	global $wpdb;
		
	$search = "@\[singlepic=(\d+)(|,\d+|,)(|,\d+|,)(|,watermark|,web20|,)(|,right|,left|,)\]@i";
	
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

	$search = "@(?:<p>)*\s*\[album\s*=\s*(\w+|^\+)(|,extend|,compact)\]\s*(?:</p>)*@i";
	
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

	$search = "@(?:<p>)*\s*\[gallery\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
	
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

	$search = "@(?:<p>)*\s*\[imagebrowser\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
	
	if	(preg_match_all($search, $content, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				// check for gallery id
				$galleryID = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE gid = '$v0' ");
				if(!$galleryID) $galleryID = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE name = '$v0' ");
				if($galleryID) {
					$search = $matches[0][$key];
					$replace= nggShowImageBrowser($galleryID);
					$content= str_replace ($search, $replace, $content);
				}
			}	
		}
	}// end gallery
	
	$search = "@(?:<p>)*\s*\[slideshow\s*=\s*(\w+|^\+)(|,(\d+)|,)(|,(\d+))\]\s*(?:</p>)*@i";

	if	(preg_match_all($search, $content, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				// check for gallery id
				$galleryID = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE gid = '$v0' ");
				if(!$galleryID) $galleryID = $wpdb->get_var("SELECT gid FROM $wpdb->nggallery WHERE name = '$v0' ");
				if($galleryID) {
					$search = $matches[0][$key];
					// get the size if they are set
			 		$irWidth  =  $matches[3][$key]; 
					$irHeight =  $matches[5][$key];
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

	$replace  = "\n".'<div class="slideshow" id="ngg_slideshow'.$galleryID.'">';
	$replace .= '<p>The <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> and <a href="http://www.mozilla.com/firefox/">a browser with Javascript support</a> are needed..</p></div>';
    $replace .= "\n\t".'<script type="text/javascript">';
	if ($ngg_options[irXHTMLvalid]) $replace .= "\n\t".'<!--';
	if ($ngg_options[irXHTMLvalid]) $replace .= "\n\t".'//<![CDATA[';
	$replace .= "\n\t\t".'var so = new SWFObject("'.NGGALLERY_URLPATH.'imagerotator.swf", "ngg_slideshow'.$galleryID.'", "'.$irWidth.'", "'.$irHeight.'", "7", "#'.$ngg_options[irBackcolor].'");';
	$replace .= "\n\t\t".'so.addParam("wmode", "opaque");';
	$replace .= "\n\t\t".'so.addVariable("file", "'.NGGALLERY_URLPATH.'nggextractXML.php?gid='.$galleryID.'");';
	if (!$ngg_options[irShuffle]) $replace .= "\n\t\t".'so.addVariable("shuffle", "false");';
	if ($ngg_options[irLinkfromdisplay]) $replace .= "\n\t\t".'so.addVariable("linkfromdisplay", "false");';
	if ($ngg_options[irShownavigation]) $replace .= "\n\t\t".'so.addVariable("shownavigation", "true");';
	if ($ngg_options[irShowicons]) $replace .= "\n\t\t".'so.addVariable("showicons", "true");';
	if ($ngg_options[irKenburns]) $replace .= "\n\t\t".'so.addVariable("kenburns", "true");';
	if ($ngg_options[irWatermark]) $replace .= "\n\t\t".'so.addVariable("logo", "'.$ngg_options[wmPath].'");';
	if (!empty($ngg_options[irAudio])) $replace .= "\n\t\t".'so.addVariable("audio", "'.$ngg_options[irAudio].'");';
	$replace .= "\n\t\t".'so.addVariable("overstretch", "'.$ngg_options[irOverstretch].'");';
	$replace .= "\n\t\t".'so.addVariable("backcolor", "0x'.$ngg_options[irBackcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("frontcolor", "0x'.$ngg_options[irFrontcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("lightcolor", "0x'.$ngg_options[irLightcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("rotatetime", "'.$ngg_options[irRotatetime].'");';
	$replace .= "\n\t\t".'so.addVariable("transition", "'.$ngg_options[irTransition].'");';	
	$replace .= "\n\t\t".'so.addVariable("width", "'.$irWidth.'");';
	$replace .= "\n\t\t".'so.addVariable("height", "'.$irHeight.'");'; 
	$replace .= "\n\t\t".'so.write("ngg_slideshow'.$galleryID.'");';
	if ($ngg_options[irXHTMLvalid]) $replace .= "\n\t".'//]]>';
	if ($ngg_options[irXHTMLvalid]) $replace .= "\n\t".'-->';
	$replace .= "\n\t".'</script>';
		
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

	// use the jQuery Plugin if activated
	if (($ngg_options[thumbEffect] == "thickbox") && ($ngg_options[galUsejQuery])) {
		$gallerycontent .= nggShowJSGallery($galleryID);
		return $gallerycontent;
	}
	
 	// check for page navigation
 	if ($maxElement > 0) {	
		if ( isset( $_GET['nggpage'] ) )	$page = (int) $_GET['nggpage'];
		else $page = 1; 
	 	$start = $offset = ( $page - 1 ) * $maxElement;
	
		$picturelist = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ORDER BY $ngg_options[galSort] $ngg_options[galSortDir] LIMIT $start, $maxElement ");
		$total = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ");	
	} else {
		$picturelist = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ORDER BY $ngg_options[galSort] $ngg_options[galSortDir] ");	
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
		$picturefile =  nggallery::remove_umlauts($picture->filename);
		$gallerycontent .= '<div class="ngg-gallery-thumbnail-box">'."\n\t";
		$gallerycontent .= '<div class="ngg-gallery-thumbnail">'."\n\t";
		$gallerycontent .= '<a href="'.$folder_url.$picturefile.'" title="'.$picture->description.'" '.$thumbcode.' >';
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
function nggShowJSGallery($galleryID) {
	// create a gallery with a jQuery plugin
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');

	// Get option
	$maxElement = $ngg_options[galImages];

	// get gallery values
	$act_gallery = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$galleryID' ");

	// set gallery url
	$folder_url 	= get_option ('siteurl')."/".$act_gallery->path."/";
	$thumb_folder   = str_replace('/','',ngg_get_thumbnail_folder($act_gallery->path, FALSE));

	$picturelist = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ORDER BY $ngg_options[galSort] $ngg_options[galSortDir] ");	
	
	if (is_array($picturelist)) {
		
		// create array	
		$i = 0;
		
		$gallerycontent  = '<script type="text/javascript">'."\n";
		$gallerycontent .= 'var nggal'. $galleryID .'=new Array()'."\n";
		foreach ($picturelist as $picture) {
			$picturefile =  nggallery::remove_umlauts($picture->filename);
			$gallerycontent .= 'nggal'. $galleryID .'['.$i++.']=["'.$picture->filename.'", "'.$picture->alttext.'", "'.strip_tags(nggallery::ngg_nl2br($picture->description)).'"]'."\n";	
		}
		$gallerycontent .=	'jQuery(document).ready(function() {'."\n";
		$gallerycontent .=  '  jQuery("#nggal'. $galleryID .'").nggallery({'."\n";
		$gallerycontent .=	'		imgarray    : nggal'. $galleryID . ','."\n";
		$gallerycontent .=	'		name        : "'. $act_gallery->name . '",'."\n";
		$gallerycontent .=	'		galleryurl  : "'. $folder_url  . '",'."\n";
		$gallerycontent .=	'		thumbfolder : "'. $thumb_folder  . '",'."\n";
		if ($ngg_options[thumbEffect] == "thickbox")
			$gallerycontent .=	'		thickbox    : true,'."\n";	
		$gallerycontent .=	'		maxelement  : '. $maxElement ."\n";
		$gallerycontent .=	'	});'."\n";
		$gallerycontent .=	'});'."\n";
		
		$gallerycontent .= '</script>'."\n";
		$gallerycontent .= '	<div id="nggal'. $galleryID .'">'."\n";
		$gallerycontent .= '	<!-- The content will be dynamically loaded in here -->'."\n";
		$gallerycontent .= '</div>'."\n";
		$gallerycontent .= '<div style="clear:both;"></div>'."\n";
	}
		
	return $gallerycontent;	
}
/**********************************************************/
function nggShowAlbum($albumID,$mode = "extend") {
	
	global $wpdb;
	
	$albumcontent = "";

	// look for gallery variable 
	if (isset( $_GET['gallery']))  {
		
		if ($albumID != $_GET['album']) return $albumcontent;

		$galleryID = attribute_escape($_GET['gallery']);
		$albumcontent = nggShowGallery($galleryID);

	} else {

		$mode = ltrim($mode,',');
		$sortorder = $wpdb->get_var("SELECT sortorder FROM $wpdb->nggalbum WHERE id = '$albumID' ");
		if (!empty($sortorder)) {
			$gallery_array = unserialize($sortorder);
		} 
	
		$albumcontent = '<div class="ngg-albumoverview">';
		if (is_array($gallery_array)) {
		foreach ($gallery_array as $galleryID) {
			$albumcontent .= nggCreateAlbum($galleryID,$mode,$albumID);	
			}
		}
		$albumcontent .= '</div>'."\n";
		$albumcontent .= '<div style="clear:both;"></div>'."\n";
	
	}
	
	return $albumcontent;
}

/**********************************************************/
function nggCreateAlbum($galleryID,$mode = "extend",$albumID = 0) {
	// create a gallery overview div
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');
	
	$gallerycontent = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$galleryID' ");

	// choose between variable and page link
	if ($ngg_options[galNoPages]) {
		$gallerylink['album'] = $albumID; 
		$gallerylink['gallery'] = $galleryID;
		$link = add_query_arg($gallerylink);
	} else {
		$link = get_permalink($gallerycontent->pageid);
	}
	
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
							<a class="Link" href="'.$link.'">'.$insertpic.'</a>
						</div>
					</div>
					<h4><a class="ngg-album-desc" title="'.$gallerycontent->title.'" href="'.$link.'">'.$gallerycontent->title.'</a></h4>
					<p><b>'.$counter.'</b> '.__('Photos', 'nggallery').'</p></div>';
		} else {
			// mode extend
			if ($gallerycontent->previewpic != 0)
				$insertpic = '<img src="'.ngg_get_thumbnail_url($gallerycontent->previewpic).'" alt="'.$gallerycontent->title.'" title="'.$gallerycontent->title.'"/>';
			else 
				$insertpic = __('Watch gallery', 'nggallery');
			$galleryoutput = '
			<div class="ngg-album">
				<div class="ngg-albumtitle"><a href="'.$link.'">'.$gallerycontent->title.'</a></div>
				<div class="ngg-albumcontent">
					<div class="ngg-thumbnail"><a href="'.$link.'">'.$insertpic.'</a></div>
					<div class="ngg-description"><p>'.html_entity_decode($gallerycontent->description).'</p></div>'."\n".'</div>'."\n".'</div>';

		}
	}
	
	return $galleryoutput;
}

/**********************************************************/
function nggShowImageBrowser($galleryID) {
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');
	
	// get gallery values
	$act_gallery = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$galleryID' ");

	// get the effect code
	if ($ngg_options[thumbEffect] != "none") $thumbcode = stripslashes($ngg_options[thumbCode]);
	if ($ngg_options[thumbEffect] == "highslide") $thumbcode = str_replace("%GALLERY_NAME%", "'".$act_gallery->name."'", $thumbcode);
	else $thumbcode = str_replace("%GALLERY_NAME%", $act_gallery->name, $thumbcode);

	// set gallery url
	$folder_url 	= get_option ('siteurl')."/".$act_gallery->path."/";
	
	$picarray = $wpdb->get_col("SELECT pid FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ORDER BY $ngg_options[galSort] $ngg_options[galSortDir]");	
	$total = count($picarray);

	// look for gallery variable 
	if ( isset( $_GET['pid'] )) {
		$act_pid = attribute_escape($_GET['pid']);
	} else {
		reset($picarray);
		$act_pid = current($picarray);
	}
	
	// get ids for back/next
	$key = array_search($act_pid,$picarray);
	if (!$key) {
		$act_pid = reset($picarray);
		$key = key($picarray);
	}
	$back_pid = ( $key >=1 ) ? $picarray[$key-1] : end($picarray) ;
	$next_pid = ( $key < ($total-1) ) ? $picarray[$key+1] : reset($picarray) ;

	$picture = $wpdb->get_row("SELECT * FROM $wpdb->nggpictures WHERE pid = '$act_pid'");	

	if ($picture) {
		$headline = ($ngg_options[ImgBrHead]) ? '<h3>'.$picture->alttext.'</h3>' : '' ;
		$desc = ($ngg_options[ImgBrDesc]) ? '<div class="ngg-imagebrowser-desc"><p>'.html_entity_decode($picture->description).'</p></div>' : '' ;
		$galleryoutput = '
		<div class="ngg-imagebrowser" >
			'.$headline.'
			<div class="pic">
				<a href="'.$folder_url.$picture->filename.'" title="'.$picture->description.'" '.$thumbcode.'>
					<img alt="'.$picture->alttext.'" src="'.$folder_url.$picture->filename.'"/>
				</a>
			</div>
			<div class="ngg-imagebrowser-nav">';
		if 	($back_pid) {
			$backlink['pid'] = $back_pid;
			$galleryoutput .='<div class="back"><a href="'.add_query_arg($backlink).'">'.$ngg_options[ImgBrTextBack].'</a></div>';
		}
		if 	($next_pid) {
			$nextlink['pid'] = $next_pid;
			$galleryoutput .='<div class="next"><a href="'.add_query_arg($nextlink).'">'.$ngg_options[ImgBrTextNext].'</a></div>';
		}
		$galleryoutput .='
				<div class="counter">'.__('Picture', 'nggallery').' '.($key+1).' '.__('from', 'nggallery').' '.$total.'</div>
				'.$desc.'
			</div>	
		</div>';
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
		
		$link  = '<a href="'.$folder_url.$picture->filename.'" title="'.$picture->description.'" '.$thumbcode.' >';
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
	if (!SAFE_MODE) {
		if (!is_dir($gallerypath."/thumbs")) {
			mkdir($gallerypath."/thumbs");
			return "/thumbs/";
		}
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

// NextGEN gallery class : 
// just started to do it better ;-)

class nggallery {
	
	/**********************************************************/
	function ngg_nl2br($string) {
		// remove page break
		$string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
		
		return $string;

	}
	
	function show_error($message) {
		echo '<div class="fade error" id="message"><p>'.$message.'</p></div>'."\n";
	}
	
	function show_message($message)
	{
		echo '<div class="fade updated" id="message"><p>'.$message.'</p></div>'."\n";
	}
	
	function remove_umlauts($filename) {
	
		$cleanname = str_replace(
		array('ä',   'ö',   'ü',   'Ä',   'Ö',   'Ü',   'ß',   ' '), 
		array('%E4', '%F6', '%FC', '%C4', '%D6', '%DC', '%DF', '%20'),
		utf8_decode($filename)
		);
		
		return $cleanname;
	}
	
}
?>
