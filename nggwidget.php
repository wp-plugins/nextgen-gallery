<?php
/*
Plugin Name: NextGEN Gallery Widget
Description: Adds a sidebar widget to see random images in your NextGEN Gallery!
Author: KeViN
Version: 0.91b
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

function getCSVValues($string,$separator=",") 
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

function widget_nextgenimage_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;
	
	// This is the function that outputs our little Google search form.
	function widget_nextgenimage($args) {
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_NextGenimage');
		$title = $options['title'];
		$number = $options['number'];
		$sizeX = $options['sizeX'];
		$sizeY = $options['sizeY'];
		$mode = $options['mode'];
		// $border = $options['border'];
		// $bordercolor = $options['bordercolor'];
		// $margin = $options['margin'];

		$showinhome = htmlspecialchars($options['showinhome'], ENT_QUOTES);
		$showcategory = htmlspecialchars($options['showcategory'], ENT_QUOTES);
		$categorylist = htmlspecialchars($options['categorylist'], ENT_QUOTES);
		
		//origy ngg options
		$ngg_options = get_option('ngg_options');

		// get the effect code
		if ($ngg_options[thumbEffect] != "none") $thumbcode = stripslashes($ngg_options[thumbCode]);
		if ($ngg_options[thumbEffect] == "highslide") $thumbcode = str_replace("%GALLERY_NAME%", "'sidebar'", $thumbcode);
		else $thumbcode = str_replace("%GALLERY_NAME%", "sidebar", $thumbcode);
	
		// checking display status (category or home)
		$show_widget = false;
	
		// Make array for checking the categories
		$categorieslist = getCSVValues($categorylist,',');

		// Denied list -> enable everywhere and make false if found!
		if (($showcategory == "denied")) {
			$show_widget = true;
			foreach((get_the_category()) as $cat) 
				{ if ((in_array($cat->cat_ID , $categorieslist)))
					$show_widget = false;
				}
			}

		// Allow list -> false is the default -> enable if found
		if (($showcategory == "allow"))
		foreach((get_the_category()) as $cat) 
			{ if ((in_array($cat->cat_ID , $categorieslist)))
				$show_widget = true;
			}

		// All categories -> if it's not the home -> enable
		if (($showcategory == "all"))
			if ((is_home() != true))
				$show_widget = true;
			
			
		// Home page -> If yes -> enable 
		if (($showinhome == "yes")) 
			if ((is_home())) 
				$show_widget = true;
		
	// here comes the display (v.08)
	
		// These lines generate our output. Widgets can be very complex
		// but as you can see here, they can also be very, very simple.
		$url_parts = parse_url(get_bloginfo('home'));

		// Null parameters check
		if ( ($number == '') ) $number = 2;
		if ( ($sizeX == '') ) $sizeX = 100;
		if ( ($sizeY == '') ) $sizeY = 75;
		// if ( ($border == '') ) $border = 0;
		// if ( ($bordercolor == '') ) $bordercolor = "#dadada";
		// if ( ($margin == '') ) $margin = 5;

		if ($show_widget) { 
	
			echo $before_widget . $before_title . $title . $after_title;
			echo "\n".'<div class="ngg-widget"><ul class="ngg-randomlist">'."\n";
			
			for ($i=1; $i<=$number; $i++) {
	
				global $wpdb;
		
				// Get a random image from the database
				$imageID = $wpdb->get_var("SELECT pid FROM $wpdb->nggpictures ORDER by rand() limit 1");
		
				//if web20
				if ( ($mode == 'web20') ) {
					echo '<li><a href="'.ngg_get_image_url($imageID).'" title="" '.$thumbcode.'>';
					echo '<img src="'.NGGALLERY_URLPATH.'nggshow.php?pid='.$imageID.'&amp;width='.$sizeX.'&amp;height='.$sizeY.'&amp;mode=web20" />';
					echo '</a></li>'."\n";
				}
				else { 
					echo '<li><a href="'.ngg_get_image_url($imageID).'" title="" '.$thumbcode.'>';
					echo '<img src="'.ngg_get_thumbnail_url($imageID).'" style="width:'.$sizeX.'px;height:'.$sizeY.'px;" />';
					echo '</a></li>'."\n";
				}
			
				//echo '</div>'."\n";
			}
	
			echo '</ul></div>'."\n";
//			echo '<div style="clear:both;"></div>'."\n";
			echo $after_widget;
		}
	}

	/**
	* @desc Output of plugin´s editform in te adminarea
	* @author KeViN
	*/

	function widget_nextgenimage_control($number=1) {

	$options = get_option('widget_NextGenimage');

	if ( !is_array($options) )
			$options = array('title'=>'', 'buttontext'=>__('NextGEN Random Image','nggallery'));
	
		if ( $_POST['nextgen-submit'] ) {
			// Remember to sanitize and format use input appropriately.
			$options = "";
			$options['title'] = strip_tags(stripslashes($_POST['nextgen-title']));
			$options['number'] = strip_tags(stripslashes($_POST['nextgen-number']));
			$options['sizeX'] = /* 90; */ 		strip_tags(stripslashes($_POST['nextgen-sizeX']));
			$options['sizeY'] = /* 90; */ 		strip_tags(stripslashes($_POST['nextgen-sizeY']));
			$options['mode'] =  /* "web20" ; */ strip_tags(stripslashes($_POST['nextgen-mode']));
			// $options['border'] = strip_tags(stripslashes($_POST['nextgen-border']));
			// $options['bordercolor'] = strip_tags(stripslashes($_POST['nextgen-bordercolor']));
			// $options['margin'] = strip_tags(stripslashes($_POST['nextgen-margin']));
			
			// Category controll (v0.8b)
			$options['showinhome'] = strip_tags(stripslashes($_POST['nextgen-showinhome']));
			$options['showcategory'] = strip_tags(stripslashes($_POST['nextgen-showcategory']));
			$options['categorylist'] = strip_tags(stripslashes($_POST['nextgen-categorylist']));
			update_option('widget_NextGenimage', $options);
		}


		// Be sure you format your options to be valid HTML attributes.
			$title = htmlspecialchars($options['title'], ENT_QUOTES);
			$number = htmlspecialchars($options['number'], ENT_QUOTES);
			$sizeX = htmlspecialchars($options['sizeX'], ENT_QUOTES);
			$sizeY = htmlspecialchars($options['sizeY'], ENT_QUOTES);
			$mode = htmlspecialchars($options['mode'], ENT_QUOTES);		
			// $border = htmlspecialchars($options['border'], ENT_QUOTES);		
			// $bordercolor = htmlspecialchars($options['bordercolor'], ENT_QUOTES);		
			// $margin = htmlspecialchars($options['margin'], ENT_QUOTES);		
	
			$showinhome = htmlspecialchars($options['showinhome'], ENT_QUOTES);
			$showcategory = htmlspecialchars($options['showcategory'], ENT_QUOTES);
			$categorylist = htmlspecialchars($options['categorylist'], ENT_QUOTES);
		// Here comes the form
	
		echo'<p style="text-align:right;"><label for="nextgen-title">' . __('Title','nggallery') . ': <input style="width: 150px;" id="nextgen-title" name="nextgen-title" type="text" value="'.$title.'" /></label></p>';
		
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

		echo '<p style="text-align:right;"><label for="nextgen-sizeX">' . __('Width (px)','nggallery') . ': <input style="width: 150px;" id="nextgen-sizeX" name="nextgen-sizeX" type="text" value="'.$sizeX.'" /></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-sizeY">' . __('Height (px)','nggallery') . ': <input style="width: 150px;" id="nextgen-sizeY" name="nextgen-sizeY" type="text" value="'.$sizeY.'" /></label></p>';

		echo '<p style="text-align:right;"><label for="nextgen-number">' . __('Mode','nggallery').':';
		echo ' <select name="nextgen-mode" size="1">';
		echo '   <option id="none" ';if (($mode == "")) echo 'selected="selected"'; echo ' value="">'. __('none','nggallery').'</option>';
		echo '   <option id="web20" ';if (($mode == "web20")) echo 'selected="selected"'; echo ' value="web20">'. __('web2.0','nggallery').'</option>';
		echo ' </select></label></p>';

		// moved to CSS file
		// echo '<p style="text-align:right;"><label for="nextgen-border">' . __('Border (px):') . ' <input style="width: 150px;" id="nextgen-border" name="nextgen-border" type="text" value="'.$border.'" /></label></p>';
		// echo '<p style="text-align:right;"><label for="nextgen-bordercolor">' . __('Border color:') . ' <input style="width: 150px;" id="nextgen-bordercolor" name="nextgen-bordercolor" type="text" value="'.$bordercolor.'" /></label></p>';
		// echo '<p style="text-align:right;"><label for="nextgen-margin">' . __('Margin (px):') . ' <input style="width: 150px;" id="nextgen-margin" name="nextgen-margin" type="text" value="'.$margin.'" /></label></p>';

		// v0.8 - category control 
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
add_action('widgets_init', 'widget_nextgenimage_init');

?>