<?php
class C_Resource_Manager_Controller extends C_MVC_Controller
{
    var $_fs            = NULL;
    var $_document_root = NULL;
    var $_resource_map  = NULL;

    /**
     * Gets an instance of the controller
     * @var array
     * @return C_Resource_Manager_Controller
     */
    static $_instances = array();
    static function get_instance($context)
    {
        if (!isset(self::$_instances[$context])) {
            $klass = get_class();
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }


    /**
     * Defines the object
     * @param bool $context
     */
    function define($context=FALSE)
    {
        parent::define($context);
        $this->implement('I_Resource_Manager');
    }

    /**
     * Gets the FS utility
     * @return C_Component|null
     */
    function _get_fs_utility()
    {
        if (is_null($this->_fs))
            $this->_fs = $this->get_registry()->get_utility('I_Fs');

        return $this->_fs;
    }

    /**
     * Gets a cached map association between handles and urls
     * @param $resource_type
     * @return mixed|void
     */
    function _get_resource_map($resource_type)
    {
        if (is_null($this->_resource_map))
            $this->_resource_map = get_option('ngg_'.$resource_type.'_map');

        return $this->_resource_map;
    }

    /**
     * Gets the url associated with a script/stylesheet handle
     * @param $handle
     * @param $resource_type: 'scripts' or 'styles'
     * @return string|FALSE
     */
    function _get_handle_url($handle, $resource_type)
    {
        global $wp_scripts, $wp_styles;
        $register = $resource_type == 'scripts' ? $wp_scripts : $wp_styles;
        $retval = FALSE;

        // First, look for the handle in the list of
        // registered scripts
        if (isset($register->registered[$handle])) {
            $retval = $register->registered[$handle]->src;
        }

        // If not available, we'll look up the url from our
        // cache
        else {
            $map = $this->_get_resource_map($resource_type);
            if (isset($map[$handle])) $retval = $map[$handle];
        }

        return $retval;
    }
    
    /**
     * Remote request which checks for status code
     */
    function _remote_fopen($uri)
    {
    	$options = array();
    	$options['timeout'] = 10;
    	
    	$response = wp_remote_get($uri, $options);
    	
    	if (!is_wp_error($response))
    	{
				$code = wp_remote_retrieve_response_code($response);
				
				if ($code >= 200 && $code < 300)
				{
					return wp_remote_retrieve_body($response);
				}
    	}
  		
  		return null;
    } 
    
    /**
     * Gets the source of a script/stylesheet
     * @param $url
     * @return string
     */
    function _get_source($url, $resource_type)
    {
        $retval     = "/* {$url } */\n";
        $fs         = $this->_get_fs_utility();
        $docroot    = $fs->get_document_root();
        $http_site  = $this->get_router()->get_base_url();
        $https_site = str_replace('http://', 'https://', $http_site);
        $path       = FALSE;

        // Is this a local file?
        if (strpos($url, '/') === 0) {
            $path = $fs->join_paths($docroot, $url);
        }

        // This is a real url. Is it local?
        elseif (strpos($url, $http_site) !== FALSE) {
            $path = str_replace($http_site, '', $url);
            $path = $fs->join_paths($docroot, $path);
        }

        // This is a real url. Is it local and using HTTPS?
        elseif (strpos($url, $https_site) !== FALSE) {
            $path = str_replace($https_site, '', $url);
            $path = $fs->join_paths($docroot, $path);
        }

        // This is a real url and it's not local. We'll have to fetch it
        else {
            $retval .= $this->_remote_fopen($url);
        }

        // ensure there's no url parameters (strip ? and on) and no newlines in our potential filename
        $path = str_replace(array("\r", "\n"), '', substr($path, 0, strpos($path, "?")));

        // If a path has been set, and it's exists on the filesystem
        if ($path && file_exists($path)) {
            $retval .= file_get_contents($path);
        }

        // This a local but dynamically generated resource. We need
        // to fetch it using HTTP
        else {
            if (strpos($url, '/') === 0) {
                $url = $this->get_router()->get_url($url, FALSE);
            }
            $retval .= $this->_remote_fopen($url);
        }

        // Now that we have the content, we have to adjust any links within CSS
        $dir = trailingslashit(dirname($url));
        if ($resource_type == 'styles' && preg_match_all("/url\\((['\"])?([^'\"\\)]*)(['\"])?\\)/", $retval, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $original_url       = $match[2];

                // If the original url isn't absolute, then we need to make it so
                if (strpos($original_url, 'http') !== 0) {
                    $new_url        = $original_url;
                    if (strpos($new_url, '/') === 0) $new_url = substr($new_url, 1);
                    if (strpos($dir, '/') === 0 && strpos($dir, $http_site) !== 0) {
                        $dir = untrailingslashit($http_site).$dir;
                    }
                    $new_url        = $dir.$new_url;
                    $original_match = $match[0];
                    $new_match      = str_replace($original_url, $new_url, $original_match);
                    $retval         = str_replace($original_match, $new_match, $retval);
                }
            }
        }

        return $retval;
    }

    /**
     * Determines whether the cache should be refreshed or not
     * @return bool
     */
    function _do_refresh_cache()
    {
        return ((defined('SCRIPT_DEBUG') && SCRIPT_DEBUG == TRUE) OR $this->param('refresh'));
    }

    /**
     * Concatenates the resources requested
     * @param $resource_type
     * @return string
     */
    function _concatenate_resources($resource_type)
    {
        $retval = '';

        if (($handles = $this->param('load'))) {

            $cache_name = md5('ngg_resources_' . $resource_type . $handles);
            $retval = $this->_do_refresh_cache() ? FALSE : wp_cache_get($cache_name, 'photocrati-nextgen');

            // Generate the results and cache
            if (!$retval) {
                $retval = array();
                foreach (explode(';', $handles) as $handle) {
                    if (($src = $this->_get_handle_url($handle, $resource_type))) {
                        $retval[] = $this->_get_source($src, $resource_type);
                    }
                    else $retval[]= "// {$handle} was not found";
                }

                $retval = implode("\n", $retval);
                wp_cache_set($cache_name, $retval, 'photocrati-nextgen', 3600);
            }
        }

        return $retval;
    }

    function flush_cache()
    {
        return wp_cache_flush();
    }

    function static_scripts_action()
    {
        $this->set_content_type('javascript');
        if (!$this->_do_refresh_cache()) $this->expires("+1 hour");
        $this->render();
        echo $this->_concatenate_resources('scripts');
    }

    function dynamic_scripts_action()
    {
        $this->set_content_type('javascript');
        $this->do_not_cache();
        $this->render();
        echo $this->_concatenate_resources('scripts');
    }

    function static_styles_action()
    {
        $this->set_content_type('css');
        if (!$this->_do_refresh_cache()) $this->expires("+1 hour");
        $this->render();
        echo $this->_concatenate_resources('styles');
    }

    function dynamic_styles_action()
    {
        $this->set_content_type('css');
        $this->do_not_cache();
        $this->render();
        echo $this->_concatenate_resources('styles');
    }
}
