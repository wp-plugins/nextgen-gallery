<?php

/**
 * NextGEN Gallery Database Class
 * 
 * @author Alex Rabe, Vincent Prat
 * @copyright 2008
 * @since 1.0.0
 */
class nggdb {
	
	/**
	 * PHP4 compatibility layer for calling the PHP5 constructor.
	 * 
	 */
	function nggdb() {
		return $this->__construct();
	}

	/**
	 * Init the Database Abstraction layer for NextGEN Gallery
	 * 
	 */	
	function __construct() {
		global $wpdb;
		
		register_shutdown_function(array(&$this, "__destruct"));
		
	}
	
	/**
	 * PHP5 style destructor and will run when database object is destroyed.
	 *
	 * @return bool Always true
	 */
	function __destruct() {
		return true;
	}	

	/**
	 * Get all the galleries
	 * 
	 * @param string $order_by
	 * @param string $order_dir
	 * @param bool $counter Select true  when you need to count the images
	 * @return array $galleries
	 */
	function find_all_galleries($order_by = 'gid', $order_dir = 'ASC', $counter = false) {		
		global $wpdb;
		
		$order_dir = ( $order_dir == 'DESC') ? 'DESC' : 'ASC';
		$galleries = $wpdb->get_results( "SELECT * FROM $wpdb->nggallery ORDER BY {$order_by} {$order_dir}", OBJECT_K );
		if ( !$galleries )
			return array();
		
		if ( !$counter )
			return $galleries;
		
		// get the galleries information 	
 		foreach ($galleries as $key => $value) {
   			$galleriesID[] = $key;
   			// init the counter values
   			$galleries[$key]->counter = 0;	
		}
		
		// get the counter values 	
		$picturesCounter = $wpdb->get_results('SELECT galleryid, COUNT(*) as counter FROM '.$wpdb->nggpictures.' WHERE galleryid IN (\''.implode('\',\'', $galleriesID).'\') AND exclude != 1 GROUP BY galleryid', OBJECT_K);

		if ( !$picturesCounter )
			return $galleries;
		
		// add the counter to the gallery objekt	
 		foreach ($picturesCounter as $key => $value)
			$galleries[$value->galleryid]->counter = $value->counter;
		
		return $galleries;
	}
	
	/**
	 * Get a gallery given its ID
	 * 
	 * @param int|string $id or $name
	 * @return A nggGallery object (null if not found)
	 */
	function find_gallery( $id ) {		
		global $wpdb;
		
		if( is_numeric($id) )
			$gallery = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->nggallery WHERE gid = %d", $id ) );
		else
			$gallery = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->nggallery WHERE name = %s", $id ) );
		
		// Build the object from the query result
		if ($gallery) 
			return $gallery;
		else 
			return false;
	}
	
	/**
	 * This function return all information about the gallery and the images inside
	 * 
	 * @param int|string $id or $name
	 * @param string $order_by 
	 * @param string $order_dir (ASC |DESC)
	 * @param bool $exclude
	 * @return An array containing the nggImage objects representing the images in the gallery.
	 */
	function get_gallery($id, $order_by = 'sortorder', $order_dir = 'ASC', $exclude = true) {

		global $wpdb;

		// init the gallery as empty array
		$gallery = array();
		
		// Check for the exclude setting
		$exclude_clause = ($exclude) ? ' AND tt.exclude<>1 ' : '';
		
		// Say no to any other value
		$order_dir = ( $order_dir == 'DESC') ? 'DESC' : 'ASC';
		$order_by  = ( empty($order_by) ) ? 'sortorder' : $order_by;
		
		// Query database
		if( is_numeric($id) )
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT tt.*, t.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = %d {$exclude_clause} ORDER BY tt.{$order_by} {$order_dir}", $id ) );
		else
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT tt.*, t.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.name = %s {$exclude_clause} ORDER BY tt.{$order_by} {$order_dir}", $id ) );

		// Build the object
		if ($result) {
				
			// Now added all image data
			foreach ($result as $key => $value)
				$gallery[$key] = new nggImage( $value );
		}
		
		return $gallery;		
	}
	
	/**
	 * This function return all information about the gallery and the images inside
	 * 
	 * @param int|string $id or $name
	 * @param string $orderby 
	 * @param string $order (ASC |DESC)
	 * @param bool $exclude
	 * @return An array containing the nggImage objects representing the images in the gallery.
	 */
	function get_ids_from_gallery($id, $order_by = 'sortorder', $order_dir = 'ASC', $exclude = true) {

		global $wpdb;
		
		// Check for the exclude setting
		$exclude_clause = ($exclude) ? ' AND tt.exclude<>1 ' : '';
		
		// Say no to any other value
		$order_dir = ( $order_dir == 'DESC') ? 'DESC' : 'ASC';		
		$order_by  = ( empty($order_by) ) ? 'sortorder' : $order_by;
				
		// Query database
		if( is_numeric($id) )
			$result = $wpdb->get_col( $wpdb->prepare( "SELECT tt.pid FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = %d $exclude_clause ORDER BY tt.{$order_by} $order_dir", $id ) );
		else
			$result = $wpdb->get_col( $wpdb->prepare( "SELECT tt.pid FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.name = %s $exclude_clause ORDER BY tt.{$order_by} $order_dir", $id ) );

		return $result;		
	}	
	/**
	 * Delete a gallery AND all the pictures associated to this gallery!
	 * 
	 * @gid The gallery ID
	 */
	function delete_gallery($gid) {		
		global $wpdb;
				
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->nggpictures WHERE galleryid = %d", $gid) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->nggallery WHERE gid = %d", $gid) );
		//TODO:Remove all tag relationship
		return true;
	}

	/**
	 * Get an album given its ID
	 * 
	 * @id The album ID or name
	 * @return A nggGallery object (false if not found)
	 */
	function find_album( $id ) {		
		global $wpdb;
		
		// Query database
		if ( is_numeric($id) ) {
			$album = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->nggalbum WHERE id = %d", $id) );
		} elseif ( $id == 'all' || $id == 0) {
			// init the object and fill it
			$album = new stdClass();
			$album->id = 0;
			$album->name = __('Album overview','nggallery');
			$album->sortorder =  serialize( $wpdb->get_col("SELECT gid FROM $wpdb->nggallery") );
		} else {
			$album = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->nggalbum WHERE name = '%s'", $id) );
		}
		
		// Unserialize the galleries inside the album
		if ( $album ) {
			if ( !empty( $album->sortorder ) ) 
				$album->gallery_ids = unserialize( $album->sortorder );
			return $album;
		} 
		
		return false;
	}
	
	/**
	 * Delete an album
	 * 
	 * @id The album ID
	 */
	function delete_album( $id ) {		
		global $wpdb;
				
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->nggalbum WHERE id = %d", $id) );
		return $result;
	}

	/**
	 * Insert an image in the database
	 * 
	 * @return the ID of the inserted image
	 */
	function insert_image($gid, $filename, $alttext, $desc, $exclude) {
		global $wpdb;
		
		$result = $wpdb->query(
			  "INSERT INTO $wpdb->nggpictures (galleryid, filename, description, alttext, exclude) VALUES "
			. "('$gid', '$filename', '$desc', '$alttext', '$exclude');");
		$pid = (int) $wpdb->insert_id;
		
		return $pid;
	}

	/**
	 * nggdb::update_image() - Insert an image in the database
	 * 
	 * @param int $pid   id of the image
	 * @param (optional) string|int $galleryid
	 * @param (optional) string $filename
	 * @param (optional) string $description
	 * @param (optional) string $alttext
	 * @param (optional) int $exclude (0 or 1)
	 * @param (optional) int $sortorder
	 * @return bool result of the ID of the inserted image
	 */
	function update_image($pid, $galleryid = false, $filename = false, $description = false, $alttext = false, $exclude = false, $sortorder = false) {

		global $wpdb;
		
		$sql = array();
		$pid = (int) $pid;
		
		$update = array(
		    'galleryid'   => $galleryid,
		    'filename' 	  => $filename,
		    'description' => $description,
		    'alttext' 	  => $alttext,
		    'exclude' 	  => $exclude,
			'sortorder'   => $sortorder);
		
		// create the sql parameter "name = value"
		foreach ($update as $key => $value)
			if ($value)
				$sql[] = $key . " = '" . $value . "'";
		
		// create the final string
		$sql = implode(', ', $sql);
		
		if ( !empty($sql) && $pid != 0)
			$result = $wpdb->query( "UPDATE $wpdb->nggpictures SET $sql WHERE pid = $pid" );

		return $result;
	}
	
	/**
	 * Get an image given its ID
	 * 
	 * @param int $id The image ID
	 * @return object A nggImage object representing the image (false if not found)
	 */
	function find_image( $id ) {
		global $wpdb;
		
		// Query database
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT tt.*, t.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE tt.pid = %d ", $id ) );
		
		// Build the object from the query result
		if ($result) {
			$image = new nggImage($result);
			return $image;
		} 
		
		return false;
	}
	
	/**
	 * Get images given a list of IDs 
	 * 
	 * @param $pids array of picture_ids
	 * @return An array of nggImage objects representing the images
	 */
	function find_images_in_list( $pids, $exclude = false, $order = 'ASC' ) {
		global $wpdb;
	
		$result = array();
		
		// Check for the exclude setting
		$exclude_clause = ($exclude) ? ' AND t.exclude <> 1 ' : '';

		// Check for the exclude setting
		$order_clause = ($order == 'RAND') ? 'ORDER BY rand() ' : ' ORDER BY t.pid ASC' ;
		
		if ( is_array($pids) ) {
			$id_list = "'" . implode("', '", $pids) . "'";
			
			// Save Query database
			$images = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggpictures AS t INNER JOIN $wpdb->nggallery AS tt ON t.galleryid = tt.gid WHERE t.pid IN ($id_list) $exclude_clause $order_clause", OBJECT_K);
	
			// Build the image objects from the query result
			if ($images) {	
				foreach ($images as $key => $image)
					$result[$key] = new nggImage( $image );
			} 
		}
		return $result;
	}
	
	/**
	* Delete an image entry from the database
	*/
	function delete_image($pid) {
		global $wpdb;
		
		// Delete the image row
		$wpdb->query("DELETE FROM $wpdb->nggpictures WHERE pid = $pid");
		
		// Delete tag references
		wp_delete_object_term_relationships($pid, 'ngg_tag');
	}
	
	/**
	 * Get the last images registered in the database with a maximum number of $limit results
	 * 
	 * @param integer $page
	 * @param integer $limit
	 * @param bool $use_exclude
	 * @return
	 */
	function find_last_images($page = 0, $limit = 30, $exclude = true) {
		global $wpdb;
		
		// Check for the exclude setting
		$exclude_clause = ($exclude) ? ' AND exclude<>1 ' : '';
		
		$offset = (int) $page * $limit;
		
		$result = array();
		$gallery_cache = array();
		
		// Query database
		$images = $wpdb->get_results("SELECT * FROM $wpdb->nggpictures WHERE 1=1 $exclude_clause ORDER BY pid DESC LIMIT $offset, $limit");
		
		// Build the object from the query result
		if ($images) {	
			foreach ($images as $key => $image) {
				
				// cache a gallery , so we didn't need to lookup twice
				if (!array_key_exists($image->galleryid, $gallery_cache))
					$gallery_cache[$image->galleryid] = nggdb::find_gallery($image->galleryid);
				
				// Join gallery information with picture information	
				foreach ($gallery_cache[$image->galleryid] as $index => $value)
					$image->$index = $value;
				
				// Now get the complete image data
				$result[$key] = new nggImage( $image );
			}
		}
		
		return $result;
	}
	
	/**
	 * nggdb::get_random_images() - Get an random image from one ore more gally
	 * 
	 * @param integer $number of images
	 * @param integer $galleryID optional a Gallery
	 * @return A nggImage object representing the image (null if not found)
	 */
	function get_random_images($number = 1, $galleryID = 0) {
		global $wpdb;
		
		$number = (int) $number;
		$galleryID = (int) $galleryID;
		$images = array();
		
		// Query database
		if ($galleryID == 0)
			$result = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE tt.exclude != 1 ORDER by rand() limit $number");
		else
			$result = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = $galleryID AND tt.exclude != 1 ORDER by rand() limit {$number}");
		
		// Return the object from the query result
		if ($result) {
			foreach ($result as $image) {
				$images[] = new nggImage( $image );
			}
			return $images;
		} 
			
		return null;
	}

	/**
	 * Get all the images from a given album
	 * 
	 * @param object|int $album The album object or the id	
	 * @param string $order_by
	 * @param string $order_dir
	 * @param bool $exclude
	 * @return An array containing the nggImage objects representing the images in the album.
	 */
	function find_images_in_album($album, $order_by = 'galleryid', $order_dir = 'ASC', $exclude = true) {		 
		global $wpdb;
		
		if ( !is_object($album) )
			$album = nggdb::find_album( $album );

 		// Get gallery list		 
		$gallery_list = implode(',', $album->gallery_ids);		 
		// Check for the exclude setting
		$exclude_clause = ($exclude) ? ' AND tt.exclude<>1 ' : '';

		// Say no to any other value
		$order_dir = ( $order_dir == 'DESC') ? 'DESC' : 'ASC';		
		$order_by  = ( empty($order_by) ) ? 'galleryid' : $order_by;
		
		$result = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE tt.galleryid IN ($gallery_list) $exclude_clause ORDER BY tt.$order_by $order_dir");		 
		// Return the object from the query result
		if ($result) {
			foreach ($result as $image) {
				$images[] = new nggImage( $image );
			}
			return $images;
		} 

		return null;	 
	}

}
?>