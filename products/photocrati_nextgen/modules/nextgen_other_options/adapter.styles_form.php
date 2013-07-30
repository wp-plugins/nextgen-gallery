<?php

class A_Styles_Form extends Mixin
{
	function get_model()
	{
		return C_Settings_Model::get_instance();
	}

	function get_title()
	{
		return 'Styles';
	}

    function get_stylesheet_directory()
    {
        return NGGALLERY_ABSPATH . "css";
    }

	function get_cssfiles()
	{
		/** THIS FUNCTION WAS TAKEN FROM NGGLEGACY **/
		$cssfiles = array ();

		// Files in nggallery/css directory
		$plugin_root = $this->object->get_stylesheet_directory();

		$plugins_dir = @ dir($plugin_root);
		if ($plugins_dir) {
			while (($file = $plugins_dir->read()) !== false) {
				if (preg_match('|^\.+$|', $file))
					continue;
				if (is_dir($plugin_root.'/'.$file)) {
					$plugins_subdir = @ dir($plugin_root.'/'.$file);
					if ($plugins_subdir) {
						while (($subfile = $plugins_subdir->read()) !== false) {
							if (preg_match('|^\.+$|', $subfile))
								continue;
							if (preg_match('|\.css$|', $subfile))
								$plugin_files[] = "$file/$subfile";
						}
					}
				} else {
					if (preg_match('|\.css$|', $file))
						$plugin_files[] = $file;
				}
			}
		}

		if ( !$plugins_dir || !$plugin_files )
			return $cssfiles;

		foreach ( $plugin_files as $plugin_file ) {
			if ( !is_readable("$plugin_root/$plugin_file"))
				continue;

			$plugin_data = $this->object->_get_cssfiles_data("$plugin_root/$plugin_file");

			if ( empty ($plugin_data['Name']) )
				continue;

			$cssfiles[plugin_basename($plugin_file)] = $plugin_data;
		}

		uasort($cssfiles, create_function('$a, $b', 'return strnatcasecmp($a["Name"], $b["Name"]);'));

		return $cssfiles;
	}

	/**
	 * Parses the CSS header
	 * @param string $plugin_file
	 * @return array
	 */
	function _get_cssfiles_data($plugin_file)
	{
		$plugin_data = implode('', file($plugin_file));
		preg_match("|CSS Name:(.*)|i", $plugin_data, $plugin_name);
		preg_match("|Description:(.*)|i", $plugin_data, $description);
		preg_match("|Author:(.*)|i", $plugin_data, $author_name);
		if (preg_match("|Version:(.*)|i", $plugin_data, $version))
			$version = trim($version[1]);
		else
			$version = '';

		$description = wptexturize(trim($description[1]));

		$name = trim($plugin_name[1]);
		$author = trim($author_name[1]);

		return array ('Name' => $name, 'Description' => $description, 'Author' => $author, 'Version' => $version );
	}

	function render()
	{
		return $this->object->render_partial('photocrati-nextgen_other_options#styling_tab', array(
            'stylesheet_directory'      =>  $this->object->get_stylesheet_directory(),
			'select_stylesheet_label'	=>	'What stylesheet would you like to use?',
			'stylesheets'				=>	$this->object->get_cssfiles(),
			'activated_stylesheet'		=>	$this->object->get_model()->CSSfile,
			'hidden_label'				=>	_('(Show Customization Options)'),
			'active_label'				=>	_('(Hide Customization Options)'),
			'cssfile_contents_label'	=>	_('File Content:'),
			'writable_label'			=>	_('Changes you make to the contents will be saved'),
			'readonly_label'			=>	_('You could edit this file if it were writable')
		), TRUE);
	}

	function save_action()
	{
		// Ensure that we have
		if (($settings = $this->object->param('style_settings'))) {
			$this->object->get_model()->set($settings)->save();

			// Are we to modify the CSS file?
			if (($contents = $this->object->param('cssfile_contents'))) {

				// Find filename
				$css_file		= $settings['CSSfile'];
				$filename		= path_join(TEMPLATEPATH, $css_file);
				$alt_filename	= path_join(
					NGGALLERY_ABSPATH,
					implode(DIRECTORY_SEPARATOR, array('css', $css_file))
				);
				$found = FALSE;
				if (file_exists($filename)) {
					if (is_writable($filename)) $found = $filename;
				}
				elseif (file_exists($alt_filename)) {
					if (is_writable($alt_filename)) $found = $alt_filename;
				}

				// Write file contents
				if ($found)
				{
					$fp = fopen($found, 'w');
					fwrite($fp, $contents);
					fclose($fp);
				}
			}
		}
	}
}