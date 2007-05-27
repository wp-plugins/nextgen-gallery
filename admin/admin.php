<?php
// ************************************
// ** Admin Section for NextGEN Gallery
// ** for V 0.50
// ** by Alex Rabe
// ************************************

// add to header in admin area
add_action('admin_head', 'ngg_nocache');
function ngg_nocache() {
	echo "\n".'<meta name="NextGEN" content="'.NGGVERSION.'" />';
	echo "\n".'<meta http-equiv="pragma" content="no-cache" />'."\n";
}

// load script files
add_action('init', 'ngg_add_admin_js',1);
function ngg_add_admin_js() {
	if ($wp_version < "2.2") {
	    wp_register_script('jquery', NGGALLERY_URLPATH .'admin/js/jquery.js', FALSE, '1.1.2');
	} 
	switch ($_GET['page']) {
		case "nggallery-manage-gallery" :
			wp_enqueue_script('interface', NGGALLERY_URLPATH .'admin/js/interface.js', array('jquery'), '1.2');
			wp_enqueue_script('thickbox', NGGALLERY_URLPATH .'thickbox/thickbox-pack.js', array('jquery'), '3.0.1');
		break;
		case "nggallery-manage-album" :
		case "nggallery-options" :
			wp_enqueue_script('interface', NGGALLERY_URLPATH .'admin/js/interface.js', array('jquery'), '1.2');
		break;		
		case "nggallery-add-gallery" :
			wp_enqueue_script('interface', NGGALLERY_URLPATH .'admin/js/interface.js', array('jquery'), '1.2');
			wp_enqueue_script('mutlifile', NGGALLERY_URLPATH .'admin/js/jquery.MultiFile.js', array('jquery'), '1.1.1');
		break;
	}
}
	
// add to menu
add_action('admin_menu', 'add_nextgen_gallery_menu');

  function add_nextgen_gallery_menu()
  {
    add_menu_page(__('Gallery', 'nggallery'), __('Gallery', 'nggallery'), 'edit_others_posts', NGGFOLDER, 'nggallery_admin_overview');
    add_submenu_page( NGGFOLDER , __('Add Gallery', 'nggallery'), __('Add Gallery', 'nggallery'), 'edit_others_posts', 'nggallery-add-gallery', 'nggallery_admin_add_gallery');
    add_submenu_page( NGGFOLDER , __('Manage Gallery', 'nggallery'), __('Manage Gallery', 'nggallery'), 'edit_others_posts', 'nggallery-manage-gallery', 'nggallery_admin_manage_gallery');
    add_submenu_page( NGGFOLDER , __('Album', 'nggallery'), __('Album', 'nggallery'), 'edit_others_posts', 'nggallery-manage-album', 'nggallery_admin_manage_album');
    add_submenu_page( NGGFOLDER , __('Options', 'nggallery'), __('Options', 'nggallery'), 'manage_options', 'nggallery-options', 'nggallery_admin_options');
    add_submenu_page( NGGFOLDER , __('Style', 'nggallery'), __('Style', 'nggallery'), 'manage_options', 'nggallery-style', 'nggallery_admin_style');
    add_submenu_page( NGGFOLDER , __('Setup Gallery', 'nggallery'), __('Setup', 'nggallery'), 'manage_options', 'nggallery-setup', 'nggallery_admin_setup');
    add_submenu_page( NGGFOLDER , __('About this Gallery', 'nggallery'), __('About', 'nggallery'), 'edit_others_posts', 'nggallery-about', 'nggallery_admin_about');
  }
  //TODO: Check Rights and Role
  
  /************************************************************************/
  
  include 'overview.php'; 	// nggallery_admin_overview
  include 'addgallery.php';	// nggallery_admin_add_gallery
  include 'manage.php';		// nggallery_admin_manage_gallery
  include 'album.php';		// nggallery_admin_manage_album
  include 'settings.php';	// nggallery_admin_options
  include 'style.php';		// nggallery_admin_style
  include 'setup.php';		// nggallery_admin_setup
  include 'about.php';		// nggallery_admin_about
  
  /**************************************************************************/

?>