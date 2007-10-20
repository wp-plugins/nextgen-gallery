<?php

function searchnggallerytags($content) {

	global $wpdb;
	$ngg_options = get_option('ngg_options');
	
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
	
	$search = "@(?:<p>)*\s*\[tags\s*=\s*(.*?)\s*\]\s*(?:</p>)*@i";

	if	(preg_match_all($search, $content, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				$search = $matches[0][$key];
				$replace= nggShowGalleryTags($v0);
				$content= str_replace ($search, $replace, $content);
			}	
		}
	}// end gallery tags 

	$search = "@(?:<p>)*\s*\[albumtags\s*=\s*(.*?)\s*\]\s*(?:</p>)*@i";

	if	(preg_match_all($search, $content, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				$search = $matches[0][$key];
				$replace= nggShowAlbumTags($v0);
				$content= str_replace ($search, $replace, $content);
			}	
		}
	}// end album tags 
	
	// attach related images based on category or tags
	if ($ngg_options['activateTags']) 
		$content .= nggShowRelatedImages();
	
	return $content;
}// end search content

/**********************************************************/
function nggShowSlideshow($galleryID,$irWidth,$irHeight) {
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');
	$obj = 'so' . $galleryID;
	
	if (empty($irWidth) ) $irWidth = $ngg_options['irWidth'];
	if (empty($irHeight)) $irHeight = $ngg_options['irHeight'];

	$replace  = "\n".'<div class="slideshow" id="ngg_slideshow'.$galleryID.'">';
	$replace .= '<p>The <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> and <a href="http://www.mozilla.com/firefox/">a browser with Javascript support</a> are needed..</p></div>';
    $replace .= "\n\t".'<script type="text/javascript" defer="defer">';
	if ($ngg_options['irXHTMLvalid']) $replace .= "\n\t".'<!--';
	if ($ngg_options['irXHTMLvalid']) $replace .= "\n\t".'//<![CDATA[';
	$replace .= "\n\t\t".'var '. $obj .' = new SWFObject("'.NGGALLERY_URLPATH.'imagerotator.swf", "ngg_slideshow'.$galleryID.'", "'.$irWidth.'", "'.$irHeight.'", "7", "#'.$ngg_options[irBackcolor].'");';
	$replace .= "\n\t\t".$obj.'.addParam("wmode", "opaque");';
	$replace .= "\n\t\t".$obj.'.addVariable("file", "'.NGGALLERY_URLPATH.'nggextractXML.php?gid='.$galleryID.'");';
	if (!$ngg_options['irShuffle']) $replace .= "\n\t\t".$obj.'.addVariable("shuffle", "false");';
	if ($ngg_options['irLinkfromdisplay']) $replace .= "\n\t\t".$obj.'.addVariable("linkfromdisplay", "false");';
	if ($ngg_options['irShownavigation']) $replace .= "\n\t\t".$obj.'.addVariable("shownavigation", "true");';
	if ($ngg_options['irShowicons']) $replace .= "\n\t\t".$obj.'.addVariable("showicons", "true");';
	if ($ngg_options['irKenburns']) $replace .= "\n\t\t".$obj.'.addVariable("kenburns", "true");';
	if ($ngg_options['irWatermark']) $replace .= "\n\t\t".$obj.'.addVariable("logo", "'.$ngg_options['wmPath'].'");';
	if (!empty($ngg_options['irAudio'])) $replace .= "\n\t\t".$obj.'.addVariable("audio", "'.$ngg_options['irAudio'].'");';
	$replace .= "\n\t\t".$obj.'.addVariable("overstretch", "'.$ngg_options['irOverstretch'].'");';
	$replace .= "\n\t\t".$obj.'.addVariable("backcolor", "0x'.$ngg_options['irBackcolor'].'");';
	$replace .= "\n\t\t".$obj.'.addVariable("frontcolor", "0x'.$ngg_options['irFrontcolor'].'");';
	$replace .= "\n\t\t".$obj.'.addVariable("lightcolor", "0x'.$ngg_options['irLightcolor'].'");';
	$replace .= "\n\t\t".$obj.'.addVariable("rotatetime", "'.$ngg_options['irRotatetime'].'");';
	$replace .= "\n\t\t".$obj.'.addVariable("transition", "'.$ngg_options['irTransition'].'");';	
	$replace .= "\n\t\t".$obj.'.addVariable("width", "'.$irWidth.'");';
	$replace .= "\n\t\t".$obj.'.addVariable("height", "'.$irHeight.'");'; 
	$replace .= "\n\t\t".$obj.'.write("ngg_slideshow'.$galleryID.'");';
	if ($ngg_options['irXHTMLvalid']) $replace .= "\n\t".'//]]>';
	if ($ngg_options['irXHTMLvalid']) $replace .= "\n\t".'-->';
	$replace .= "\n\t".'</script>';
		
	return $replace;
}

/**********************************************************/
function nggShowGallery($galleryID) {
	
	global $wpdb;
	
	$ngg_options = get_option('ngg_options');
	
	// use the jQuery Plugin if activated
	if (($ngg_options['thumbEffect'] == "thickbox") && ($ngg_options['galUsejQuery'])) {
		$gallerycontent .= nggShowJSGallery($galleryID);
		return $gallerycontent;
	}

	// set $_GET if slideshow first
	if ( !isset( $_GET['show'] ) AND ($ngg_options['galShowOrder'] == 'slide')) {
		$_GET['page'] = get_the_ID();
		$_GET['show'] = slide;
	}

	// go on only on this page
	if ( $_GET['page'] == get_the_ID() ) { 
			
		// 1st look for ImageBrowser link
		if (isset( $_GET['pid']))  {
			$gallerycontent = nggShowImageBrowser($galleryID);
			return $gallerycontent;
		}
		
		// 2nd look for slideshow
		if ( isset( $_GET['show'] ) AND ($_GET['show'] == slide) ) {
			$args['page'] = get_the_ID();
			$args['show'] = "gallery";
			$gallerycontent  = '<div class="ngg-galleryoverview">';
			$gallerycontent .= '<a class="slideshowlink" href="' . htmlspecialchars(add_query_arg( $args) ) . '">'.$ngg_options['galTextGallery'].'</a>';
			$gallerycontent .= nggShowSlideshow($galleryID,$ngg_options['irWidth'],$ngg_options['irHeight']);
			$gallerycontent .= '</div>'."\n";
			$gallerycontent .= '<div class="ngg-clear"></div>'."\n";
			return $gallerycontent;
		}
	}
	
	//Set sort order value, if not used (upgrade issue)
	$ngg_options['galSort'] = ($ngg_options['galSort']) ? $ngg_options['galSort'] : "pid";
	$ngg_options['galSortDir'] = ($ngg_options['galSortDir'] == "DESC") ? "DESC" : "ASC";

	// get all picture with this galleryid
	$picturelist = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = '$galleryID' AND tt.exclude != 1 ORDER BY tt.$ngg_options[galSort] $ngg_options[galSortDir] ");
	if (is_array($picturelist)) { 
		$gallerycontent = nggCreateGallery($picturelist,$galleryID);
	}
	
	return $gallerycontent;
}

/**********************************************************/
function nggCreateGallery($picturelist,$galleryID = false) {
	/** 
	* @array  	$picturelist
	* @int		$galleryID
    **/
    
    if (!is_array($picturelist))
		$picturelist = array($picturelist);
	
	// Get option
	$ngg_options = get_option('ngg_options');
	
	$maxElement = $ngg_options['galImages'];
	$thumbwidth = $ngg_options['thumbwidth'];
	$thumbheight = $ngg_options['thumbheight'];
	
	// set thumb size 
	$thumbsize = "";
	if ($ngg_options['thumbfix'])  $thumbsize = 'style="width:'.$thumbwidth.'px; height:'.$thumbheight.'px;"';
	if ($ngg_options['thumbcrop']) $thumbsize = 'style="width:'.$thumbwidth.'px; height:'.$thumbwidth.'px;"';
	
	// get the effect code
	if ($galleryID)
		$thumbcode = ($ngg_options['galImgBrowser']) ? "" : nggallery::get_thumbcode($picturelist[0]->name);
	else
		$thumbcode = ($ngg_options['galImgBrowser']) ? "" : nggallery::get_thumbcode(get_the_title());
	
 	// check for page navigation
 	if ($maxElement > 0) {
	 	if ( $_GET['page'] == get_the_ID() ) {
			if ( isset( $_GET['nggpage'] ) )	
				$page = (int) $_GET['nggpage'];
			else
				 $page = 1;
		}
		else $page = 1;
		 
	 	$start = $offset = ( $page - 1 ) * $maxElement;
	 	
	 	$total = count($picturelist);
	 	
		// remove the element if we didn't start at the beginning
		if ($start > 0 ) array_splice($picturelist, 0, $start);
		// return the list of images we need
		array_splice($picturelist, $maxElement);
	
		$navigation = nggallery::create_navigation($page, $total, $maxElement);
	} 	
	
	if (is_array($picturelist)) {
	$out  = '<div class="ngg-galleryoverview">';
	
	// show slideshow link
	if ($galleryID)
		if (($ngg_options['galShowSlide']) AND (NGGALLERY_IREXIST)) {
			$args['page'] = get_the_ID();
			$args['show'] = "slide";
			$out .= '<a class="slideshowlink" href="' . htmlspecialchars(add_query_arg( $args )) . '">'.$ngg_options[galTextSlide].'</a>';
		}
	
	// a description below the picture, require fixed width
	if (!$ngg_options['galShowDesc'])
		$ngg_options['galShowDesc'] = "none";
	$setwidth = ($ngg_options['galShowDesc'] != "none") ? 'style="width:'.($thumbwidth + 15).'px;"' : '';
	
	foreach ($picturelist as $picture) {
		// set image url
		$folder_url 	= get_option ('siteurl')."/".$picture->path."/";
		$thumbnailURL 	= get_option ('siteurl')."/".$picture->path.nggallery::get_thumbnail_folder($picture->path, FALSE);
		$thumb_prefix   = nggallery::get_thumbnail_prefix($picture->path, FALSE);
		//clean filename
		$picturefile =  nggallery::remove_umlauts($picture->filename);
		// choose link between imagebrowser or effect

		$link =($ngg_options['galImgBrowser']) ? htmlspecialchars(add_query_arg(array('page'=>get_the_ID(),'pid'=>$picture->pid))) : $folder_url.$picturefile;
		// create output
		$out .= '<div class="ngg-gallery-thumbnail-box">'."\n\t";
		$out .= '<div class="ngg-gallery-thumbnail" '.$setwidth.' >'."\n\t";
		$out .= '<a href="'.$link.'" title="'.stripslashes($picture->description).'" '.$thumbcode.' >';
		$out .= '<img title="'.stripslashes($picture->alttext).'" alt="'.stripslashes($picture->alttext).'" src="'.$thumbnailURL.$thumb_prefix.$picture->filename.'" '.$thumbsize.' />';
		$out .= '</a>'."\n";
		if ($ngg_options['galShowDesc'] == "alttext")
			$out .= '<span>'.stripslashes($picture->alttext).'</span>'."\n";
		if ($ngg_options['galShowDesc'] == "desc")
			$out .= '<span>'.stripslashes($picture->description).'</span>'."\n";
		$out .= '</div>'."\n".'</div>'."\n";
		}
	$out .= '</div>'."\n";
 	$out .= ($maxElement > 0) ? $navigation : '<div class="ngg-clear"></div>'."\n";
	}		
	return $out;
}


/**********************************************************/
function nggShowJSGallery($galleryID) {
	// create a gallery with a jQuery plugin
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');

	// Get option
	$maxElement = $ngg_options['galImages'];

	// get gallery values
	$act_gallery = $wpdb->get_row("SELECT * FROM $wpdb->nggallery WHERE gid = '$galleryID' ");

	// set gallery url
	$folder_url 	= get_option ('siteurl')."/".$act_gallery->path."/";
	$thumb_folder   = str_replace('/','',nggallery::get_thumbnail_folder($act_gallery->path, FALSE));

	$picturelist = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ORDER BY $ngg_options[galSort] $ngg_options[galSortDir] ");	
	
	if (is_array($picturelist)) {
		
		// create array	
		$i = 0;
		
		$gallerycontent  = '<script type="text/javascript">'."\n";
		$gallerycontent .= 'var nggal'. $galleryID .'=new Array()'."\n";
		foreach ($picturelist as $picture) {
			$picturefile =  nggallery::remove_umlauts($picture->filename);
			$gallerycontent .= 'nggal'. $galleryID .'['.$i++.']=["'.$picture->filename.'", "'.stripslashes($picture->alttext).'", "'.strip_tags(nggallery::ngg_nl2br($picture->description)).'"]'."\n";	
		}
		$gallerycontent .=	'jQuery(document).ready(function() {'."\n";
		$gallerycontent .=  '  jQuery("#nggal'. $galleryID .'").nggallery({'."\n";
		$gallerycontent .=	'		imgarray    : nggal'. $galleryID . ','."\n";
		$gallerycontent .=	'		name        : "'. $act_gallery->name . '",'."\n";
		$gallerycontent .=	'		galleryurl  : "'. $folder_url  . '",'."\n";
		$gallerycontent .=	'		thumbfolder : "'. $thumb_folder  . '",'."\n";
		if ($ngg_options['thumbEffect'] == "thickbox")
			$gallerycontent .=	'		thickbox    : true,'."\n";	
		$gallerycontent .=	'		maxelement  : '. $maxElement ."\n";
		$gallerycontent .=	'	});'."\n";
		$gallerycontent .=	'});'."\n";
		
		$gallerycontent .= '</script>'."\n";
		$gallerycontent .= '	<div id="nggal'. $galleryID .'">'."\n";
		$gallerycontent .= '	<!-- The content will be dynamically loaded in here -->'."\n";
		$gallerycontent .= '</div>'."\n";
		$gallerycontent .= '<div class="ngg-clear"></div>'."\n";
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

		$galleryID = (int) attribute_escape($_GET['gallery']);
		$albumcontent = nggShowGallery($galleryID);
		return $albumcontent;
	} 

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
	$albumcontent .= '<div class="ngg-clear"></div>'."\n";
	
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
		$link = htmlspecialchars(add_query_arg($gallerylink));
	} else {
		$link = get_permalink($gallerycontent->pageid);
	}
	
	if ($gallerycontent) {
		$counter = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1");
 		if ($mode == "compact") {
			if ($gallerycontent->previewpic != 0)
				$insertpic = '<img class="Thumb" width="91" height="68" alt="'.$gallerycontent->title.'" src="'.nggallery::get_thumbnail_url($gallerycontent->previewpic).'"/>';
			else 
				$insertpic = __('Watch gallery', 'nggallery');
 			$galleryoutput = '	
				<div class="ngg-album-compact">
					<div class="ngg-album-compactbox">
						<div class="ngg-album-link">
							<a class="Link" href="'.$link.'">'.$insertpic.'</a>
						</div>
					</div>
					<h4><a class="ngg-album-desc" title="'.$gallerycontent->title.'" href="'.$link.'">'.$gallerycontent->title.'</a></h4>
					<p><strong>'.$counter.'</strong> '.__('Photos', 'nggallery').'</p></div>';
		} else {
			// mode extend
			if ($gallerycontent->previewpic != 0)
				$insertpic = '<img src="'.nggallery::get_thumbnail_url($gallerycontent->previewpic).'" alt="'.$gallerycontent->title.'" title="'.$gallerycontent->title.'"/>';
			else 
				$insertpic = __('Watch gallery', 'nggallery');
			$galleryoutput = '
			<div class="ngg-album">
				<div class="ngg-albumtitle"><a href="'.$link.'">'.$gallerycontent->title.'</a></div>
				<div class="ngg-albumcontent">
					<div class="ngg-thumbnail"><a href="'.$link.'">'.$insertpic.'</a></div>
					<div class="ngg-description"><p>'.html_entity_decode($gallerycontent->galdesc).'</p><p><strong>'.$counter.'</strong> '.__('Photos', 'nggallery').'</p></div>'."\n".'</div>'."\n".'</div>';

		}
	}
	
	return $galleryoutput;
}

/**********************************************************/
function nggShowImageBrowser($galleryID) {
	/** 
	* show the ImageBrowser
	* @galleryID	int / gallery id
	*/
	
	global $wpdb;
	
	// get options
	$ngg_options = get_option('ngg_options');
	
	// get the pictures
	$picturelist = $wpdb->get_col("SELECT pid FROM $wpdb->nggpictures WHERE galleryid = '$galleryID' AND exclude != 1 ORDER BY $ngg_options[galSort] $ngg_options[galSortDir]");	
	if (is_array($picturelist)) { 
		$output = nggCreateImageBrowser($picturelist);
	}
	
	return $output;
	
}

/**********************************************************/
function nggCreateImageBrowser($picarray) {
	/** 
	* @array  	$picarray with pid
    **/

    if (!is_array($picarray))
		$picarray = array($picarray);

	$total = count($picarray);

	// look for gallery variable 
	if ( isset( $_GET['pid'] )) {
		$act_pid = (int) attribute_escape($_GET['pid']);
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
	$back_pid = ( $key >= 1 ) ? $picarray[$key-1] : end($picarray) ;
	$next_pid = ( $key < ($total-1) ) ? $picarray[$key+1] : reset($picarray) ;
	
	// get the picture data
	$picture = new nggImage($act_pid);
	
	if ($picture) {
		$galleryoutput = '
		<div class="ngg-imagebrowser" >
			<h3>'.stripslashes($picture->alttext).'</h3>
			<div class="pic">'.$picture->get_href_link().'</div>
			<div class="ngg-imagebrowser-nav">';
		if 	($back_pid) {
			$backlink['pid'] = $back_pid;
			$galleryoutput .='<div class="back"><a href="'.htmlspecialchars(add_query_arg($backlink)).'">'.'&#9668; '.__('Back', 'nggallery').'</a></div>';
		}
		if 	($next_pid) {
			$nextlink['pid'] = $next_pid;
			$galleryoutput .='<div class="next"><a href="'.htmlspecialchars(add_query_arg($nextlink)).'">'.__('Next', 'nggallery').' &#9658;'.'</a></div>';
		}
		$galleryoutput .='
				<div class="counter">'.__('Picture', 'nggallery').' '.($key+1).' '.__('from', 'nggallery').' '.$total.'</div>
				<div class="ngg-imagebrowser-desc"><p>'.html_entity_decode($picture->description).'</p></div>
			</div>	
		</div>';
	}
	return $galleryoutput;
	
}

/**********************************************************/
function nggSinglePicture($imageID,$width=250,$height=250,$mode="",$float="") {
	/** 
	* create a gallery based on the tags
	* @imageID		db-ID of the image
	* @width 		width of the image
	* @height 		height of the image
	* @mode 		none, watermark, web20
	* @float 		none, left, right
	*/
	global $wpdb;
	
	// remove the comma
	$float = ltrim($float,',');
	$mode = ltrim($mode,',');
	$width = ltrim($width,',');
	$height = ltrim($height,',');

	// get picturedata
	$picture = new nggImage($imageID);
	
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

	// add fullsize picture as link
	$content  = '<a href="'.$picture->imagePath.'" title="'.stripslashes($picture->description).'" '.$picture->get_thumbcode("singlepic".$imageID).' >';
	$content .= '<img class="ngg-singlepic" src="'.NGGALLERY_URLPATH.'nggshow.php?pid='.$imageID.'&amp;width='.$width.'&amp;height='.$height.'&amp;mode='.$mode.'" alt="'.stripslashes($picture->alttext).'" title="'.stripslashes($picture->alttext).'"'.$float.' />';
	$content .= '</a>';
	
	return $content;
}

/**********************************************************/
function nggShowGalleryTags($taglist) {
	/** 
	* create a gallery based on the tags
	* @taglist		list of tags as csv
	*/
	
	global $wpdb;
	
	// get now the related images
	$picturelist = ngg_Tags::get_images($taglist);
	
	// look for ImageBrowser 
	if ( $_GET['page'] == get_the_ID() )  
		if (isset( $_GET['pid']))  {
			foreach ($picturelist as $picture)
				$picarray[] = $picture->pid;
			$content = nggCreateImageBrowser($picarray);
			return $content;
		}

	// go on if not empty
	if (empty($picturelist))
		return;
	
	// show gallery
	if (is_array($picturelist)) { 
		$content = nggCreateGallery($picturelist,false);
	}
	
	return $content;
}

/**********************************************************/
function nggShowRelatedGallery($taglist, $maxImages = 0) {
	/** 
	* create a gallery based on the tags
	* @taglist		list of tags as csv
	* @maxImages	limit the number of images to show
	*/
	
	global $wpdb;
	
	// get now the related images
	$picturelist = ngg_Tags::get_images($taglist);
	
	// go on if not empty
	if (empty($picturelist))
		return;
		
	// get the options
	$ngg_options = get_option('ngg_options');

	// get the effect code
	$thumbcode = nggallery::get_thumbcode("Related images for ".get_the_title());

	// cut the list to maxImages
	if ($maxImages > 0 ) array_splice($picturelist, $maxImages);
	
 	// *** build the gallery output
	$content   = '<div class="ngg-related-gallery">';
	
	foreach ($picturelist as $picture) {
		// set gallery url
		$folder_url 	= get_option ('siteurl')."/".$picture->path."/";
		$thumbnailURL 	= get_option ('siteurl')."/".$picture->path.nggallery::get_thumbnail_folder($picture->path, FALSE);
		$thumb_prefix   = nggallery::get_thumbnail_prefix($picture->path, FALSE);

		$picturefile =  nggallery::remove_umlauts($picture->filename);
		$content .= '<a href="'.$folder_url.$picturefile.'" title="'.stripslashes($picture->description).'" '.$thumbcode.' >';
		$content .= '<img title="'.stripslashes($picture->alttext).'" alt="'.stripslashes($picture->alttext).'" src="'.$thumbnailURL.$thumb_prefix.$picture->filename.'" '.$thumbsize.' />';
		$content .= '</a>'."\n";
	}

	$content .= '</div>'."\n";

	return $content;
}

/**********************************************************/
function nggShowAlbumTags($taglist) {
	/** 
	* create a gallery based on the tags
	* @taglist		list of tags as csv
	*/
	
	global $wpdb;
	
	// look for gallerytag variable 
	if ( $_GET['page'] == get_the_ID() )  {
		if (isset( $_GET['gallerytag']))  {
	
			$galleryTag = attribute_escape($_GET['gallerytag']);
			$tagname  = $wpdb->get_var("SELECT name FROM $wpdb->nggtags WHERE slug = '$galleryTag' ");		
			$content  = '<div id="albumnav"><span><a href="'.get_permalink().'" title="'.__('Overview', 'nggallery').'">'.__('Overview', 'nggallery').'</a> | '.$tagname.'</span></div>';
			$content .=  nggShowGalleryTags($galleryTag);
			return $content;
	
		} 
	}
	
	// get now the related images
	$picturelist = ngg_Tags::get_album_images($taglist);

	// go on if not empty
	if (empty($picturelist))
		return;

	$content = '<div class="ngg-albumoverview">';
	foreach ($picturelist as $picture) {
		$args['page'] = get_the_ID();
		$args['gallerytag'] = $picture["slug"];
		$link = htmlspecialchars( add_query_arg($args) );
		
		$insertpic = '<img class="Thumb" width="91" height="68" alt="'.$picture["name"].'" src="'.nggallery::get_thumbnail_url($picture["pid"]).'"/>';
		$tagid = $picture['tagid'];
		$counter  = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpic2tags WHERE tagid = '$tagid' ");
		$content .= '	
			<div class="ngg-album-compact">
				<div class="ngg-album-compactbox">
					<div class="ngg-album-link">
						<a class="Link" href="'.$link.'">'.$insertpic.'</a>
					</div>
				</div>
				<h4><a class="ngg-album-desc" title="'.$picture["name"].'" href="'.$link.'">'.$picture["name"].'</a></h4>
				<p><strong>'.$counter.'</strong> '.__('Photos', 'nggallery').'</p></div>';
	}
	$content .= '</div>'."\n";
	$content .= '<div class="ngg-clear"></div>'."\n";

	return $content;
}

/**********************************************************/
function nggShowRelatedImages($type = '', $maxImages = 0) {
	// return related images based on category or tags
		
		if ($type == '') {
			$ngg_options = get_option('ngg_options');
			$type = $ngg_options['appendType'];
			$maxImages = $ngg_options['maxImages'];
		}
	
		$sluglist = array();
		switch ($type) {
			
		case "tags":
			if (function_exists('get_the_tags')) { 
				$taglist = get_the_tags();
				
				if (is_array($taglist)) 
				foreach ($taglist as $tag)
					$sluglist[] = $tag->slug;
			}
			break;
		case "category":
			$catlist = get_the_category();
			
			if (is_array($catlist)) 
			foreach ($catlist as $cat)
				$sluglist[] = $cat->category_nicename;
		}
		
		$sluglist = implode(",", $sluglist);
		$content = nggShowRelatedGallery($sluglist, $maxImages);
		
		return $content;
}

/**********************************************************/
function the_related_images($type = 'tags', $maxNumbers = 7) {
	// function for theme authors
	echo nggShowRelatedImages($type, $maxNumbers);
}

?>
