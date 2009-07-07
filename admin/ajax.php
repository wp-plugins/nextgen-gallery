<?php

/**
 * @author Alex Rabe
 * @copyright 2008
 */

add_action('wp_ajax_ngg_ajax_operation', 'ngg_ajax_operation' );

function ngg_ajax_operation() {
		
		global $wpdb;

		// if nonce is not correct it returns -1
		check_ajax_referer( "ngg-ajax" );
		
		// check for correct capability
		if ( !is_user_logged_in() )
			die('-1');
		
		// check for correct NextGEN capability. TODO : Can we check here already for "can_manage_this_gallery($gallery->author)" ?
		if ( !current_user_can('NextGEN Upload images') && !current_user_can('NextGEN Manage gallery') ) 
			die('-1');	
		
		// include the ngg function
		include_once (dirname (__FILE__). '/functions.php');

		// Get the image id
		if ( isset($_POST['image'])) {
			$id = (int) $_POST['image'];
			// let's get the image data
			$picture = nggdb::find_image($id);
			// what do you want to do ?		
			switch ( $_POST['operation'] ) {
				case 'create_thumbnail' :
					$result = nggAdmin::create_thumbnail($picture);
				break;
				case 'resize_image' :
					$result = nggAdmin::resize_image($picture);
				break;
				case 'set_watermark' :
					$result = nggAdmin::set_watermark($picture);
				break;
				default :
					die('-1');	
				break;		
			}
			// A success should return a '1'
			die ($result);
		}
		
		// The script should never stop here
		die('0');
}

add_action('wp_ajax_createNewThumb', 'createNewThumb');
	
	function createNewThumb() {
		
		global $wpdb;
		
		// check for correct capability
		if ( !is_user_logged_in() )
			die('-1');
			
		// check for correct NextGEN capability
		if ( !current_user_can('NextGEN Manage gallery') ) 
			die('-1');	

		require_once( dirname( dirname(__FILE__) ) . '/ngg-config.php');
		include_once( nggGallery::graphic_library() );
		
		$ngg_options=get_option('ngg_options');
		
		$id 	 = (int) $_POST['id'];
		$picture = nggdb::find_image($id);

		$x = round( $_POST['x'] * $_POST['rr'], 0);
		$y = round( $_POST['y'] * $_POST['rr'], 0);
		$w = round( $_POST['w'] * $_POST['rr'], 0);
		$h = round( $_POST['h'] * $_POST['rr'], 0);
		
		$thumb = new ngg_Thumbnail($picture->imagePath, TRUE);
		
		$thumb->crop($x, $y, $w, $h);
		
		$thumb_filename = $picture->thumbPath;

		if ($ngg_options['thumbfix'])  {
			if ($thumb->currentDimensions['height'] > $thumb->currentDimensions['width']) {
				$thumb->resize($ngg_options['thumbwidth'], 0);
			} else {
				$thumb->resize(0,$ngg_options['thumbheight']);	
			}
		} else {
			$thumb->resize($ngg_options['thumbwidth'],$ngg_options['thumbheight'],$ngg_options['thumbResampleMode']);	
		}
		
		if ( $thumb->save($thumb_filename,100)) {
			echo "OK";
		} else {
			header('HTTP/1.1 500 Internal Server Error');			
			echo "KO";
		}
		
		exit();
		
	}
	
?>