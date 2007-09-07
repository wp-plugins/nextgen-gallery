<?php
/*
Plugin Name: NextGEN Gallery Widget
Description: Adds a sidebar widget support to your NextGEN Gallery
Author: KeViN
Version: 1.12
Author URI: http://www.kev.hu
Plugin URI: http://www.kev.hu

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/ 

function nggGetCSVValues($string,$separator=",") 
{
    $string = str_replace('""', "'", $string);
    // split the string at double quotes "
    $bits = explode('"',$string);
    $elements = array();
    for ( $i=0; $i < count($bits) ; $i++ ) {
        /*
        odd numbered elements would have been
        enclosed by double quotes
        even numbered elements would not have been
        */
        if (($i%2) == 1) {
            /* if the element number is odd add the
            whole string  to the output array */
            $elements[] = $bits[$i];
        } else 
        {
            /* otherwise split the unquoted stuff at commas
            and add the elements to the array */
            $rest = $bits[$i];
            $rest = preg_replace("/^".$separator."/","",$rest);
            $rest = preg_replace("/".$separator."$/","",$rest);
            $elements = array_merge($elements,explode($separator,$rest));
        }
    }
    return $elements;
}

/**********************************************************/
/* Slidehow widget function
/**********************************************************/
function nggSlideshowWidget($galleryID,$irWidth,$irHeight) {
	
	// Check for NextGEN Gallery
	if ( !function_exists('nggShowSlideshow') )
		return;	
	
	global $wpdb;
	$ngg_options = get_option('ngg_options');
	
	if (empty($irWidth) ) $irWidth = $ngg_options[irWidth];
	if (empty($irHeight)) $irHeight = $ngg_options[irHeight];
	
	$replace .= "\n".'<div class="ngg-widget-slideshow" id="ngg_widget_slideshow'.$galleryID.'">';
	$replace .= '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see the slideshow.</div>';
    $replace .= "\n\t".'<script type="text/javascript" defer="defer">';
    $replace .= "\n\t".'<!--';
	$replace .= "\n\t".'//<![CDATA[';
	$replace .= "\n\t\t".'var so = new SWFObject("'.NGGALLERY_URLPATH.'imagerotator.swf", "ngg_slideshow'.$galleryID.'", "'.$irWidth.'", "'.$irHeight.'", "7", "#'.$ngg_options[irBackcolor].'");';
	$replace .= "\n\t\t".'so.addParam("wmode", "opaque");';
	$replace .= "\n\t\t".'so.addVariable("file", "'.NGGALLERY_URLPATH.'nggextractXML.php?gid='.$galleryID.'");';
	if (!$ngg_options[irShuffle]) $replace .= "\n\t\t".'so.addVariable("shuffle", "false");';
//	if ($ngg_options[irLinkfromdisplay]) $replace .= "\n\t\t".'so.addVariable("linkfromdisplay", "false");';
//	if ($ngg_options[irShownavigation]) $replace .= "\n\t\t".'so.addVariable("shownavigation", "true");';
	if ($ngg_options[irShowicons]) $replace .= "\n\t\t".'so.addVariable("showicons", "true");';
	$replace .= "\n\t\t".'so.addVariable("overstretch", "'.$ngg_options[irOverstretch].'");';
	$replace .= "\n\t\t".'so.addVariable("backcolor", "0x'.$ngg_options[irBackcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("frontcolor", "0x'.$ngg_options[irFrontcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("lightcolor", "0x'.$ngg_options[irLightcolor].'");';
	$replace .= "\n\t\t".'so.addVariable("rotatetime", "'.$ngg_options[irRotatetime].'");';
	$replace .= "\n\t\t".'so.addVariable("transition", "'.$ngg_options[irTransition].'");';
	$replace .= "\n\t\t".'so.addVariable("width", "'.$irWidth.'");';
	$replace .= "\n\t\t".'so.addVariable("height", "'.$irHeight.'");'; 
	$replace .= "\n\t\t".'so.write("ngg_widget_slideshow'.$galleryID.'");';
	$replace .= "\n\t".'//]]>';
	$replace .= "\n\t".'-->';
	$replace .= "\n\t".'</script>';
		
	echo $replace;
}


/**********************************************************/
/* Slidehow widget control
/**********************************************************/
function widget_ngg_slideshow() {
 
 	// Check for the required plugin functions. 
	if ( !function_exists('register_sidebar_widget') )
		return;
		
	// Check for NextGEN Gallery
	if ( !function_exists('nggShowSlideshow') )
		return;	
	
	function widget_show_ngg_slideshow($args) {
	 
	    extract($args);
   
    	// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_nggslideshow');

		// These lines generate our output. 
		echo $before_widget . $before_title . $options['title'] . $after_title;
		$url_parts = parse_url(get_bloginfo('home'));
		nggSlideshowWidget($options['galleryid'] , $options['width'] , $options['height']);
		echo $after_widget;
		
	}	

	// Admin section
	function widget_control_ngg_slideshow() {
	 	global $wpdb;
	 	$options = get_option('widget_nggslideshow');
	 	if ( !is_array($options) )
			$options = array('title'=>'Slideshow', 'galleryid'=>'0','height'=>'120','width'=>'160',);
			
		if ( $_POST['ngg-submit'] ) {

			$options['title'] = strip_tags(stripslashes($_POST['ngg-title']));
			$options['galleryid'] = $_POST['ngg-galleryid'];
			$options['height'] = $_POST['ngg-height'];
			$options['width'] = $_POST['ngg-width'];
			update_option('widget_nggslideshow', $options);
		}
		
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$height = $options['height'];
		$width = $options['width'];
		
		// The Box content
		echo '<p style="text-align:right;"><label for="ngg-title">' . __('Title:', 'nggallery') . ' <input style="width: 200px;" id="ngg-title" name="ngg-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="ngg-galleryid">' . __('Select Gallery:', 'nggallery'). ' </label>';
		echo '<select size="1" name="ngg-galleryid" id="ngg-galleryid">';
			echo '<option value="0" ';
			if ($table->gid == $options['galleryid']) echo "selected='selected' ";
			echo '>'.__('All images', 'nggallery').'</option>'."\n\t"; 
			$tables = $wpdb->get_results("SELECT * FROM $wpdb->nggallery ORDER BY 'name' ASC ");
			if($tables) {
				foreach($tables as $table) {
				echo '<option value="'.$table->gid.'" ';
				if ($table->gid == $options['galleryid']) echo "selected='selected' ";
				echo '>'.$table->name.'</option>'."\n\t"; 
				}
			}
		echo '</select></p>';
		echo '<p style="text-align:right;"><label for="ngg-height">' . __('Height:', 'nggallery') . ' <input style="width: 50px;" id="ngg-height" name="ngg-height" type="text" value="'.$height.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="ngg-width">' . __('Width:', 'nggallery') . ' <input style="width: 50px;" id="ngg-width" name="ngg-width" type="text" value="'.$width.'" /></label></p>';
		echo '<input type="hidden" id="ngg-submit" name="ngg-submit" value="1" />';
	 		
	}
	
	register_sidebar_widget(array('NextGEN Slideshow', 'widgets'), 'widget_show_ngg_slideshow');
	register_widget_control(array('NextGEN Slideshow', 'widgets'), 'widget_control_ngg_slideshow', 300, 200);
}

/*******************************************************/
/* DISPLAY FUNCTION TO THE RECENT & RANDOM IMAGES 
/*******************************************************/
function nggDisplayImagesWidget($thumb,$number,$sizeX,$sizeY,$mode,$imgtype) {
	
	// Check for NextGEN Gallery
	if ( !class_exists('nggallery') )
		return;
		
	global $wpdb;
	
	//get ngg options
	$ngg_options = get_option('ngg_options');

	// get the effect code
	$thumbcode = nggallery::get_thumbcode("sidebar_".$imgtype);

	$count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures WHERE exclude != 1 ");
	if ($count < $number) 
		$number = $count;

	if ($imgtype == "random") 
		$imageList = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE exclude != 1 ORDER by rand() limit $number");
	else
		$imageList = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE exclude != 1 ORDER by pid DESC limit 0,$number");

	if (is_array($imageList)){
		foreach($imageList as $image) {

			//TODO:Insert title
			$out = '<a href="'.nggallery::get_image_url($image->pid).'" title="'.stripslashes($image->description).'" '.$thumbcode.'>';
			$addmode = ($mode == 'web20') ? "&amp;mode=web20" : "" ;
			if ( $thumb == "false" )
				$out .= '<img src="'.NGGALLERY_URLPATH.'nggshow.php?pid='.$image->pid.'&amp;width='.$sizeX.'&amp;height='.$sizeY.$addmode.'" title="'.$image->alttext.'" alt="'.$image->alttext.'" />';
			else	
				$out .= '<img src="'.nggallery::get_thumbnail_url($image->pid).'" style="width:'.$sizeX.'px;height:'.$sizeY.'px;" title="'.$image->alttext.'" alt="'.$image->alttext.'" />';			
			
			echo $out . '</a>'."\n";
			
		}
	}
}

/**********************************************************/
/* SIMPLE INSERT TAGS 
/**********************************************************/

function nggDisplayRandomImages($number,$width,$height) {
	echo "\n".'<div class="ngg-widget">'."\n";
	nggDisplayImagesWidget("true",$number,$width,$height,"","random");
	echo '</div>'."\n";
}


function nggDisplayRecentImages($number,$width,$height) {
	echo "\n".'<div class="ngg-widget">'."\n";
	nggDisplayImagesWidget("true",$number,$width,$height,"","recent");
	echo '</div>'."\n";
}


/**********************************************************/
/* Recent widget
/**********************************************************/
function widget_ngg_recentimage() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;
		
	// Check for NextGEN Gallery
	if ( !class_exists('nggallery') )
		return;
	
	function widget_nextgenrecentimage($args) {
		
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_NextGenrecentimage');
		
		$title	= $options['title'];
	    $thumb	= $options['thumb'];
		$number	= $options['number'];
		$sizeX	= $options['sizeX'];
		$sizeY	= $options['sizeY'];
		$mode	= $options['mode'];

		$showinhome		= htmlspecialchars($options['showinhome'], ENT_QUOTES);
		$showcategory	= htmlspecialchars($options['showcategory'], ENT_QUOTES);
		$categorylist	= htmlspecialchars($options['categorylist'], ENT_QUOTES);
		
		$imgtype = "recent";
		
		$show_widget = false;									// checking display status (category or home)
		$categorieslist = nggGetCSVValues($categorylist,','); 	// Make array for checking the categories

		if (($showcategory == "denied")) {						// Denied list -> enable everywhere and make false if found!
			$show_widget = true;
			foreach((get_the_category()) as $cat) 
				{ if ((in_array($cat->cat_ID , $categorieslist)))
					$show_widget = false;
				}
			}

		if (($showcategory == "allow"))						// Allow list -> false is the default -> enable if found
		foreach((get_the_category()) as $cat) 
			{ if ((in_array($cat->cat_ID , $categorieslist)))
				$show_widget = true;
			}
	
		if (($showcategory == "all"))						// All categories -> if it's not the home -> enable
			if ((is_home() != true))
				$show_widget = true;

		if (($showinhome == "yes")) 						// Home page -> If yes -> enable 
			if ((is_home())) 
				$show_widget = true;
		
	
		$url_parts = parse_url(get_bloginfo('home'));

		// Null parameters check
		if ( ($number == '') ) $number = 1;
		if ( ($sizeX == '') ) $sizeX = 190;
		if ( ($sizeY == '') ) $sizeY = 190;

		if ($show_widget) { 
	
			echo $before_widget . $before_title . $title . $after_title;
			echo "\n".'<div class="ngg-widget">'."\n";
			
			nggDisplayImagesWidget($thumb,$number,$sizeX,$sizeY,$mode,$imgtype);
		
			echo '</div>'."\n";
			echo $after_widget;
		}
	}

	/**
	* @desc Output of plugin´s editform in the adminarea
	* @author KeViN
	*/

	function widget_nextgenrecentimage_control($number=1) {

	$options = get_option('widget_NextGenrecentimage');

	if ( !is_array($options) )
			$options = array('title'=>'Recent Images', 'buttontext'=>__('NextGEN Recent Image','nggallery'));
	
		if ( $_POST['nextgen-recentsubmit'] ) {
			// Remember to sanitize and format use input appropriately.
			$options = "";
			$options['title']		= strip_tags(stripslashes($_POST['nextgen-recenttitle']));
			$options['thumb']		= strip_tags(stripslashes($_POST['nextgen-recentthumb']));
			$options['number']		= strip_tags(stripslashes($_POST['nextgen-recentnumber']));
			$options['sizeX']		= strip_tags(stripslashes($_POST['nextgen-recentsizeX']));
			$options['sizeY']		= strip_tags(stripslashes($_POST['nextgen-recentsizeY']));
			
			// [0.80] [new functiions and newvariables] -> Category controll
			$options['showinhome'] 	= strip_tags(stripslashes($_POST['nextgen-recentshowinhome']));
			$options['showcategory']= strip_tags(stripslashes($_POST['nextgen-recentshowcategory']));
			$options['categorylist']= strip_tags(stripslashes($_POST['nextgen-recentcategorylist']));

			// [0.95] [new variable] -> (random / recent)
			$options['imgtype'] 	= strip_tags(stripslashes($_POST['nextgen-recentimgtype']));

			update_option('widget_NextGenrecentimage', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
			$title = htmlspecialchars($options['title'], ENT_QUOTES);
			$thumb = htmlspecialchars($options['thumb'], ENT_QUOTES);
			$number = htmlspecialchars($options['number'], ENT_QUOTES);
			$sizeX = htmlspecialchars($options['sizeX'], ENT_QUOTES);
			$sizeY = htmlspecialchars($options['sizeY'], ENT_QUOTES);
			$mode = htmlspecialchars($options['mode'], ENT_QUOTES);		
			
			//  [0.80] [new functiions and newvariables] -> Category controll
			$showinhome = htmlspecialchars($options['showinhome'], ENT_QUOTES);
			$showcategory = htmlspecialchars($options['showcategory'], ENT_QUOTES);
			$categorylist = htmlspecialchars($options['categorylist'], ENT_QUOTES);

			// [0.95] [new variable] -> (random / recent) 
			$mode = htmlspecialchars($options['imgtype'], ENT_QUOTES);		

		// Here comes the form
	
		echo'<p style="text-align:right;"><label for="nextgen-recenttitle">' . __('Title','nggallery') . ': <input style="width: 150px;" id="nextgen-recenttitle" name="nextgen-recenttitle" type="text" value="'.$title.'" /></label></p>';
		
		echo '<p style="text-align:right;"><label for="nextgen-recentthumb">' . __('Display type','nggallery').':';
		echo ' <select name="nextgen-recentthumb" size="1">';
		echo '   <option id="1" ';if (($thumb == "true")) echo 'selected="selected"'; echo ' value="true">' . __('Thumbnail','nggallery') . '</option>';
		echo '   <option id="2" ';if (($thumb == "false")) echo 'selected="selected"'; echo ' value="false">' . __('Orginal','nggallery') . '</option>';
		echo ' </select></label></p>';
			
		echo '<p style="text-align:right;"><label for="nextgen-recentnumber">' . __('Number of pics','nggallery').':';
		echo ' <select name="nextgen-recentnumber" size="1">';
		echo '   <option id="1" ';if (($number == 1)) echo 'selected="selected"'; echo ' value="1">1</option>';
		echo '   <option id="2" ';if (($number == 2)) echo 'selected="selected"'; echo ' value="2">2</option>';
		echo '   <option id="3" ';if (($number == 3)) echo 'selected="selected"'; echo ' value="3">3</option>';
		echo '   <option id="4" ';if (($number == 4)) echo 'selected="selected"'; echo ' value="4">4</option>';
		echo '   <option id="6" ';if (($number == 6)) echo 'selected="selected"'; echo ' value="6">6</option>';
		echo '   <option id="8" ';if (($number == 8)) echo 'selected="selected"'; echo ' value="8">8</option>';
		echo '   <option id="10" ';if (($number == 10)) echo 'selected="selected"'; echo ' value="10">10</option>';
		echo ' </select></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-recentsizeX">' . __('Width (px)','nggallery') . ': <input style="width: 50px;" id="nextgen-recentsizeX" name="nextgen-recentsizeX" type="text" value="'.$sizeX.'" /></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-recentsizeY">' . __('Height (px)','nggallery') . ': <input style="width: 50px;" id="nextgen-recentsizeY" name="nextgen-recentsizeY" type="text" value="'.$sizeY.'" /></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-recentnumber">' . __('Mode','nggallery').':';
		echo ' <select name="nextgen-recentmode" size="1">';
		echo '   <option id="none" ';if (($mode == "")) echo 'selected="selected"'; echo ' value="">'. __('none','nggallery').'</option>';
		echo '   <option id="web20" ';if (($mode == "web20")) echo 'selected="selected"'; echo ' value="web20">'. __('web2.0','nggallery').'</option>';
		echo ' </select></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-recentshowinhome">' . __('Show in the main page','nggallery').':';
		echo ' <select name="nextgen-recentshowinhome" size="1">';
		echo '   <option id="1" ';if ($showinhome == "yes") echo 'selected="selected"'; echo ' value="yes" >'. __('yes','nggallery').'</option>';
		echo '   <option id="2" ';if ($showinhome == "no") echo 'selected="selected"'; echo ' value="no" >'. __('no','nggallery').'</option>';
		echo ' </select></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-recentshowcategory">' . __('Show in','nggallery').':';
		echo ' <select name="nextgen-recentshowcategory" size="1">';
		echo '   <option id="1" ';if (($showcategory == "all")) echo 'selected="selected"'; echo ' value="all" >'. __('All categories','nggallery').'</option>';
		echo '   <option id="2" ';if (($showcategory == "denied")) echo 'selected="selected"'; echo ' value="denied" >'. __('Only which are not listed','nggallery').'</option>';
		echo '   <option id="3" ';if (($showcategory == "allow")) echo 'selected="selected"'; echo ' value="allow" >'. __('Only which are listed','nggallery').'</option>';
		echo ' </select></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-recentcategorylist">' . __('Categories (id (use , to seperate)','nggallery') . ': <input style="width: 150px;" id="nextgen-recentcategorylist" name="nextgen-recentcategorylist" type="text" value="'.$categorylist.'" /></label></p>';

		echo '<input type="hidden" id="nextgen-recentsubmit" name="nextgen-recentsubmit" value="1" />';
	  }

	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget(array('NextGEN Recent Image', 'widgets'), 'widget_nextgenrecentimage');
    register_widget_control(array('NextGEN Recent Image', 'widgets'), 'widget_nextgenrecentimage_control', 300, 400);
}

/**********************************************************/
/* Random widget
/**********************************************************/
function widget_ngg_randomimage() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;
		
	// Check for NextGEN Gallery
	if ( !class_exists('nggallery') )
		return;
	
	function widget_nextgenimage($args) {
		
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_NextGenimage');
		
		$title	= $options['title'];
	    $thumb	= $options['thumb'];
		$number	= $options['number'];
		$sizeX	= $options['sizeX'];
		$sizeY	= $options['sizeY'];
		$mode	= $options['mode'];

		$showinhome		= htmlspecialchars($options['showinhome'], ENT_QUOTES);
		$showcategory	= htmlspecialchars($options['showcategory'], ENT_QUOTES);
		$categorylist	= htmlspecialchars($options['categorylist'], ENT_QUOTES);
		
		$imgtype = "random";
		
		//origy ngg options
		$ngg_options = get_option('ngg_options');

		// get the effect code
		$thumbcode = nggallery::get_thumbcode("sidebar");
		
		$show_widget = false;								// checking display status (category or home)
		$categorieslist = nggGetCSVValues($categorylist,','); 	// Make array for checking the categories

		if (($showcategory == "denied")) {					// Denied list -> enable everywhere and make false if found!
			$show_widget = true;
			foreach((get_the_category()) as $cat) 
				{ if ((in_array($cat->cat_ID , $categorieslist)))
					$show_widget = false;
				}
			}

		if (($showcategory == "allow"))						// Allow list -> false is the default -> enable if found
		foreach((get_the_category()) as $cat) 
			{ if ((in_array($cat->cat_ID , $categorieslist)))
				$show_widget = true;
			}
	
		if (($showcategory == "all"))						// All categories -> if it's not the home -> enable
			if ((is_home() != true))
				$show_widget = true;

		if (($showinhome == "yes")) 						// Home page -> If yes -> enable 
			if ((is_home())) 
				$show_widget = true;
		
	
		$url_parts = parse_url(get_bloginfo('home'));

		// Null parameters check
		if ( ($number == '') ) $number = 1;
		if ( ($sizeX == '') ) $sizeX = 190;
		if ( ($sizeY == '') ) $sizeY = 190;

		if ($show_widget) { 
	
			echo $before_widget . $before_title . $title . $after_title;
			echo "\n".'<div class="ngg-widget">'."\n";
			
			nggDisplayImagesWidget($thumb,$number,$sizeX,$sizeY,$mode,$imgtype,$thumbcode);
		
			echo '</div>'."\n";
			echo $after_widget;
		}
	}

	/**
	* @desc Output of plugin´s editform in the adminarea
	* @author KeViN
	*/

	function widget_nextgenimage_control($number=1) {

	$options = get_option('widget_NextGenimage');

	if ( !is_array($options) )
			$options = array('title'=>'', 'buttontext'=>__('NextGEN Random Image','nggallery'));
	
		if ( $_POST['nextgen-submit'] ) {
			// Remember to sanitize and format use input appropriately.
			$options = "";
			$options['title']		= strip_tags(stripslashes($_POST['nextgen-title']));
			$options['thumb']		= strip_tags(stripslashes($_POST['nextgen-thumb']));
			$options['number']		= strip_tags(stripslashes($_POST['nextgen-number']));
			$options['sizeX']		= strip_tags(stripslashes($_POST['nextgen-sizeX']));
			$options['sizeY']		= strip_tags(stripslashes($_POST['nextgen-sizeY']));
			$options['mode']		= strip_tags(stripslashes($_POST['nextgen-mode']));
			
			// [0.80] [new functiions and newvariables] -> Category controll
			$options['showinhome'] 	= strip_tags(stripslashes($_POST['nextgen-showinhome']));
			$options['showcategory']= strip_tags(stripslashes($_POST['nextgen-showcategory']));
			$options['categorylist']= strip_tags(stripslashes($_POST['nextgen-categorylist']));

			// [0.95] [new variable] -> (random / recent)
			$options['imgtype'] 	= strip_tags(stripslashes($_POST['nextgen-imgtype']));

			update_option('widget_NextGenimage', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
			$title = htmlspecialchars($options['title'], ENT_QUOTES);
			$thumb = htmlspecialchars($options['thumb'], ENT_QUOTES);
			$number = htmlspecialchars($options['number'], ENT_QUOTES);
			$sizeX = htmlspecialchars($options['sizeX'], ENT_QUOTES);
			$sizeY = htmlspecialchars($options['sizeY'], ENT_QUOTES);
			//$mode = htmlspecialchars($options['mode'], ENT_QUOTES);		
			
			$showinhome = htmlspecialchars($options['showinhome'], ENT_QUOTES);
			$showcategory = htmlspecialchars($options['showcategory'], ENT_QUOTES);
			$categorylist = htmlspecialchars($options['categorylist'], ENT_QUOTES);

			$mode = htmlspecialchars($options['imgtype'], ENT_QUOTES);		

		// Here comes the form
		echo'<p style="text-align:right;"><label for="nextgen-title">' . __('Title','nggallery') . ': <input style="width: 150px;" id="nextgen-title" name="nextgen-title" type="text" value="'.$title.'" /></label></p>';
		
		echo '<p style="text-align:right;"><label for="nextgen-thumb">' . __('Display type','nggallery').':';
		echo ' <select name="nextgen-thumb" size="1">';
		echo '   <option id="1" ';if (($thumb == "true")) echo 'selected="selected"'; echo ' value="true">' . __('Thumbnail','nggallery') . '</option>';
		echo '   <option id="2" ';if (($thumb == "false")) echo 'selected="selected"'; echo ' value="false">' . __('Orginal','nggallery') . '</option>';
		echo ' </select></label></p>';
		
		echo '<p style="text-align:right;"><label for="nextgen-number">' . __('Number of pics','nggallery').':';
		echo ' <select name="nextgen-number" size="1">';
		echo '   <option id="1" ';if (($number == 1)) echo 'selected="selected"'; echo ' value="1">1</option>';
		echo '   <option id="2" ';if (($number == 2)) echo 'selected="selected"'; echo ' value="2">2</option>';
		echo '   <option id="3" ';if (($number == 3)) echo 'selected="selected"'; echo ' value="3">3</option>';
		echo '   <option id="4" ';if (($number == 4)) echo 'selected="selected"'; echo ' value="4">4</option>';
		echo '   <option id="6" ';if (($number == 6)) echo 'selected="selected"'; echo ' value="6">6</option>';
		echo '   <option id="8" ';if (($number == 8)) echo 'selected="selected"'; echo ' value="8">8</option>';
		echo '   <option id="10" ';if (($number == 10)) echo 'selected="selected"'; echo ' value="10">10</option>';
		echo ' </select></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-sizeX">' . __('Width (px)','nggallery') . ': <input style="width: 50px;" id="nextgen-sizeX" name="nextgen-sizeX" type="text" value="'.$sizeX.'" /></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-sizeY">' . __('Height (px)','nggallery') . ': <input style="width: 50px;" id="nextgen-sizeY" name="nextgen-sizeY" type="text" value="'.$sizeY.'" /></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-number">' . __('Mode','nggallery').':';
		echo ' <select name="nextgen-mode" size="1">';
		echo '   <option id="none" ';if (($mode == "")) echo 'selected="selected"'; echo ' value="">'. __('none','nggallery').'</option>';
		echo '   <option id="web20" ';if (($mode == "web20")) echo 'selected="selected"'; echo ' value="web20">'. __('web2.0','nggallery').'</option>';
		echo ' </select></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-showinhome">' . __('Show in the main page','nggallery').':';
		echo ' <select name="nextgen-showinhome" size="1">';
		echo '   <option id="1" ';if ($showinhome == "yes") echo 'selected="selected"'; echo ' value="yes" >'. __('yes','nggallery').'</option>';
		echo '   <option id="2" ';if ($showinhome == "no") echo 'selected="selected"'; echo ' value="no" >'. __('no','nggallery').'</option>';
		echo ' </select></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-showcategory">' . __('Show in','nggallery').':';
		echo ' <select name="nextgen-showcategory" size="1">';
		echo '   <option id="1" ';if (($showcategory == "all")) echo 'selected="selected"'; echo ' value="all" >'. __('All categories','nggallery').'</option>';
		echo '   <option id="2" ';if (($showcategory == "denied")) echo 'selected="selected"'; echo ' value="denied" >'. __('Only which are not listed','nggallery').'</option>';
		echo '   <option id="3" ';if (($showcategory == "allow")) echo 'selected="selected"'; echo ' value="allow" >'. __('Only which are listed','nggallery').'</option>';
		echo ' </select></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-categorylist">' . __('Categories (id (use , to seperate)','nggallery') . ': <input style="width: 150px;" id="nextgen-categorylist" name="nextgen-categorylist" type="text" value="'.$categorylist.'" /></label></p>';

		echo '<input type="hidden" id="nextgen-submit" name="nextgen-submit" value="1" />';
	  }

	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget(array('NextGEN Random Image', 'widgets'), 'widget_nextgenimage');
    register_widget_control(array('NextGEN Random Image', 'widgets'), 'widget_nextgenimage_control', 300, 400);
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_ngg_randomimage');
add_action('widgets_init', 'widget_ngg_slideshow');
add_action('widgets_init', 'widget_ngg_recentimage');
?>