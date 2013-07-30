<?php

/**
 * Registers new AJAX functions for retrieving/updating
 * the contents of CSS stylesheets
 */
class A_Stylesheet_Ajax_Actions extends Mixin
{
	/**
	 * Retrieves the contents of the CSS stylesheet specified
	 */
	function get_stylesheet_contents_action()
	{
		$retval = array();

		if ($this->object->_authorized_for_stylesheet_action()) {

			// Ensure we have a CSS file to open
			$found = FALSE;
			if (($cssfile = $this->object->param('cssfile'))) {
				$alt_filename	= path_join(TEMPLATEPATH, $cssfile);
				$filename		= path_join(
					NGGALLERY_ABSPATH,
					implode(DIRECTORY_SEPARATOR, array('css', $cssfile))
				);
				if (file_exists($alt_filename)) $found = $alt_filename;
				elseif (file_exists($filename)) $found = $filename;
			}

			// Did we find the CSS stylesheet?
			if ($found != FALSE) {
				$retval['contents'] = file_get_contents($found);
				$retval['writable']	= is_writable($found);
			}
			else $retval['error'] = "Could not find CSS stylesheet";

		}
		else {
			$retval['error'] = 'Unauthorized';
		}

		return $retval;

	}


	/**
	 * Determines if the request is authorized
	 * @return boolean
	 */
	function _authorized_for_stylesheet_action()
	{
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$sec_actor = $security->get_current_actor();
		
		return $sec_actor->is_allowed('nextgen_edit_style');
	}
}
