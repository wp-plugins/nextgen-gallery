<?php
/*
{
    Module: photocrati-resource_minifier,
    Depends: { photocrati-nextgen_settings }
}
*/

class M_Resource_Minifier extends C_Base_Module
{
    var $minifier_enabled = TRUE;
    var $resources        = array();

    function define()
    {
        parent::define(
            'photocrati-resource_minifier',
            'Resource Minifier',
            'Minifies and concatenates static resources',
            '0.1',
            'http://www.nextgen-gallery.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );

		include_once('class.resource_minifier_installer.php');
		C_Photocrati_Installer::add_handler($this->module, 'C_Resource_Minifier_Installer');
    }

    /**
     * Registers necessary hooks for WordPress
     */
    function _register_hooks()
    {
        add_action('init', array($this, 'start_buffering'));
        add_action('wp_print_footer_scripts', array(&$this, 'start_lazy_loading'), PHP_INT_MAX);
        add_action('admin_print_footer_scripts', array(&$this, 'start_lazy_loading'), PHP_INT_MAX);
        add_action('wp_enqueue_scripts', array(&$this, 'write_tags'), PHP_INT_MAX);
        add_action('wp_print_footer_scripts', array($this, 'move_resource_tags'), 1);
        add_action('wp_print_footer_scripts', array($this, 'write_footer_tags'), 2);
        add_action('admin_print_footer_scripts', array($this, 'move_resource_tags'), 1);
        add_action('admin_print_footer_scripts', array($this, 'write_footer_tags'), 2);
        add_filter('script_loader_src', array(&$this, 'append_script'), PHP_INT_MAX, 2);
    }

    function _register_utilities()
    {
        $this->get_registry()->add_utility('I_Resource_Manager', 'C_Resource_Manager_Controller');
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Router', 'A_Resource_Minifier_Routes');
    }
    
    function start_buffering()
    {
    	ob_start();
    	
    	$this->register_lazy_resources();
    }
    
    function move_resource_tags()
    {
        // Get the buffer of the page thus far
    	$contents = ob_get_clean();
        $tags = null;

        // Get the style tags
        ob_start();
        $this->write_resource_tags('styles');
        $tags = ob_get_clean();

        // Move the style tags to the head element
        echo str_ireplace("</head>", $tags."</head>", $contents);
    }

    function register_lazy_resources()
    {
        // Register SidJS: http://www.diveintojavascript.com/projects/sidjs-load-javascript-and-stylesheets-on-demand
        $router = $this->get_registry()->get_utility('I_Router');
        wp_register_script(
            'sidjs',
            $router->get_static_url('photocrati-resource_minifier#sidjs-0.1.js'),
            array('jquery'),
            '0.1'
        );

        wp_register_script(
            'lazy_resources',
            $router->get_static_url('photocrati-resource_minifier#lazy_resources.js'),
            array('sidjs')
        );

        wp_enqueue_script('lazy_resources');
    }

    /**
     * Writes the HTML tags in the footer
     */
    function write_footer_tags()
    {
        $this->write_tags(TRUE);
    }

    /**
     * Writes the resource tags to the browser
     */
    function write_tags($in_footer=FALSE)
    {
        $this->write_resource_tags('styles',  $in_footer);
        $this->write_resource_tags('scripts', $in_footer);
    }

    /**
     * Gets the resouce map for a particular type
     * @param $resource_type
     * @return mixed|void
     */
    function get_resource_map($resource_type)
    {
        return get_option('ngg_'.$resource_type.'_map');
    }


    function initialize_resources()
    {
        $this->resources = array(
            'scripts'       =>  array(
                'static'    =>  array(),
                'dynamic'   =>  array(),
                'map'       =>  $this->get_resource_map('scripts')
            ),
            'styles'        =>  array(
                'static'    =>  array(),
                'dynamic'   =>  array(),
                'map'       =>  $this->get_resource_map('styles')
            )
        );

        // Determine if the minifier should be enabled or not
        if (defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS == FALSE)
            $this->minifier_enabled = FALSE;
        else {
            $this->minifier_enabled = C_NextGen_Settings::get_instance()->resource_minifier;
        }
    }

    /**
     * Gets the list of scripts that should be minified
     */
    function write_resource_tags($resource_type, $in_footer=FALSE)
    {
        // Initialize this portion
        $router = NULL;
        $tagname = $resource_type == 'scripts' ? 'script' : 'link';
        $output_func = $resource_type == 'scripts' ? 'wp_print_scripts' : 'wp_print_styles';
        $this->initialize_resources();

        // If the minifier is enabled, we'll concatenate all scripts and styles. If the
        // minifier is disabled, but we're writing styles in the footer, then we'll
        // continue with the routine and let $this->write_tags() decide what to do
        if ($this->minifier_enabled OR ($in_footer AND $resource_type == 'styles')) {

            // Parse scripts for inclusion
            ob_start();
            $output_func();
            $html = ob_get_contents();
            ob_end_clean();

            // Populate styles
            if ($resource_type == 'styles') echo $this->extract_enqueued_styles($html);

            // Strip out any scripts be loading by url, and outputs the rest. We
            // need to this for wp_localize_script() calls
            else echo $this->strip_tags_with_urls($tagname, $html);

            // Store the map
            update_option('ngg_'.$resource_type.'_map', $this->resources[$resource_type]['map']);

            // Load the static scripts. These scripts will be concatenated and the final result will be
            // cached and never regenerated
            $this->write_tag($resource_type, 'static', $in_footer);

            // Load the dynamic scripts. These scripts will be concatenated but not cached,
            // as their content is known to change
            $this->write_tag($resource_type, 'dynamic', $in_footer);
        }

        // Otherwise we'll just output the html
        else {
            $output_func();
        }

    }

    /**
     * Writes the HTML tag for a resource tag of a particular group
     * @param $resource_type
     * @param $group
     */
    function write_tag($resource_type, $group, $in_footer=FALSE)
    {
        if (isset($this->resources[$resource_type])) {
            if (isset($this->resources[$resource_type][$group])) {
                if (empty($this->resources[$resource_type][$group])) return;
                $router     = $this->get_registry()->get_utility('I_Router');
                $handles    = $this->get_enqueued($resource_type, $group);

                // Are we enqueuing scripts?
                if ($resource_type == 'scripts') {

                    // Use the concatenated url if the minifier is enabled
                    if ($this->minifier_enabled) {
                        $url = $router->get_url("/nextgen-{$group}/{$resource_type}", FALSE).'?load='.$handles;
                        echo "<script type='text/javascript' src='{$url}'></script>\n";
                    }

                    // If the minifier is disabled, then just out the scripts as normal
                    else {
                        foreach ($this->resources[$resource_type][$group] as $handle) {
                            $url = $this->resources[$resource_type]['map'][$handle];
                            $handle = esc_attr($handle);
                            echo "<script name='{$handle}' type='text/javascript' src='{$url}'></script>\n";
                        }
                    }
                }

                // If we're enqueuing stylesheets
                else {

                    // If we're in the footer, we need to lazy load the stylesheet
                    if ($in_footer) {

                        // Use the concatenated url if the minifier is enabled
                        if ($this->minifier_enabled) {
                            $url = $router->get_url("/nextgen-{$group}/{$resource_type}", FALSE).'?load='.$handles;
                            echo '<script type="text/javascript">Lazy_Resources.enqueue("'.$url.'")</script>';
                            echo "\n";
                        }

                        // If the minifier is disabled, we still need to lazy load the stylesheets
                        else {
                            foreach ($this->resources[$resource_type][$group] as $handle) {
                                $url = $this->resources[$resource_type]['map'][$handle];
                                echo '<script type="text/javascript">Lazy_Resources.enqueue("'.$url.'")</script>';
                                echo "\n";
                            }
                        }
                    }

                    // We're not in the footer, meaning we don't have to lazy load
                    else {
                        if ($this->minifier_enabled) {
                            $url = $router->get_url("/nextgen-{$group}/{$resource_type}", FALSE).'?load='.$handles;
                            echo "<link href='{$url}' rel='stylesheet' type='text/css'/>\n";
                        }
                        else {
                            foreach ($this->resources[$resource_type][$group] as $handle) {
                                $url = $this->resources[$resource_type]['map'][$handle];
                                echo "<link href='{$url}' rel='stylesheet' type='text/css' name='{$handle}-css'/>\n";
                            }
                        }
                    }

                }

                $resources = &$this->resources[$resource_type];
                unset($resources[$group]);
            }
        }
    }


    /**
     * Gets a list of enqueued resources
     * @param string $resource_type
     * @param string $group
     * @return string
     */
    function get_enqueued($resource_type='scripts', $group='static')
    {
        return implode(";", $this->resources[$resource_type][$group]);
    }

    /**
     * Strips HTML tags from the given HTML content if a url is present
     * @param $tagname
     * @param $content
     * @return mixed
     */
    function strip_tags_with_urls($tagname, $content)
    {
        if (preg_match_all("/\\s*<{$tagname}.*(src|href)=['\"]([^'\"]+).*(<\\/{$tagname}>|\\/>)\\s*/mi", $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tag = $match[0];
                $url = $match[2];
                if (!$this->is_resource_external($url)) {
                    $content = str_replace($tag, '', $content);
                }
            }
        }
        return $content;
    }

    function extract_enqueued_styles($content)
    {
        global $wp_styles;
        $this->resources['styles']['static'] = array();
        $this->resources['styles']['dynamic'] = array();

        // Find stylesheet handle ids and urls
        if (preg_match_all("/id=['\"]([^'\"]+)['\"].*href=['\"]([^'\"]+)/m", $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $handle         = substr($match[1], 0, -4);
                $url            = $match[2];
                $handle_parts   = explode('@', $handle);
                $group          = 'static';
                if (isset($handle_parts[1])) $group = $handle_parts[1];
                if (!isset($wp_styles->registered[$handle]->extra['conditional']) AND !$this->is_resource_external($url)) {
                    if (preg_match("/\\s*<link.*{$handle}.*\\/>/m", $content, $link_match)) {
                        $content = str_replace($link_match[0], '', $content);
                    }
                    $this->resources['styles'][$group][] = $handle;
                    $this->resources['styles']['map'][$handle] = $url;
                }
            }
        }

        return $content;
    }
    
    /**
     * check if resource is provided by external site
     * @param $src
     * @param $match_count - minimum amount of components to match eg comp1.comp2.comp3.ext
     */
    function is_resource_external($src, $match_count = 2)
    {
        $host = parse_url(site_url(), PHP_URL_HOST);
        $host_src = parse_url($src, PHP_URL_HOST);

        $parts = explode('.', $host);
        $parts_src = explode('.', $host_src);

        $count = count($parts);
        $count_src = count($parts_src);

        $max = min($count, $count_src);
        $max = min($match_count, $max);
        $external = false;

        for ($i = 0; $i < $max; $i++) {
            $l = $i + 1;
            $comp = $parts[$count - $l];
            $comp_src = $parts_src[$count_src - $l];

            if (strtolower($comp) != strtolower($comp_src)) {
                $external = true;

                break;
            }
        }

        return $external;
    }

    /**
     * Appends a script to the queue of resources to load
     * @param $src
     * @param $handle
     */
    function append_script($src, $handle)
    {
        // Both the src passed in and the src registered aren't reliable, and
        // I'm not 100% sure why - it looks to be related to the esc_url() function.
        // It sucks, but we'll have to live with it for now.
        if (!preg_match("#^http(s)?://#", $src) && substr($src, 0, 2) != '//') {
            global $wp_scripts;
            $src = $wp_scripts->registered[$handle]->src;
        }

        if (!preg_match("#^http(s)?://#", $src) && substr($src, 0, 2) != '//') {
            $src = site_url() . '/' . ltrim($src, '/');
        }

        if (!$this->is_resource_external($src)) {
        	$this->append_resource('scripts', $handle, $src);
        }

        return $src;
    }


    /**
     * Appends a stylesheet to the resource queue
     * @param $tag
     * @param $handle
     * @return mixed
     */
    function append_stylesheet($src, $handle)
    {
        global $wp_styles;

        // Conditions will be output as usual, and not concatenated
        if (!isset($wp_styles->registered[$handle]->extra['conditional'])) {

            // Both the src passed in and the src registered aren't reliable, and
            // I'm not 100% sure why - it looks to be related to the esc_url() function.
            // It sucks, but we'll have to live with it for now.
            if (!preg_match("#^http(s)?://#", $src) && substr($src, 0, 2) != '//') {
                $src = $wp_styles->registered[$handle]->src;
            }

            if (!preg_match("#^http(s)?://#", $src) && substr($src, 0, 2) != '//') {
                $src = site_url() . '/' . ltrim($src, '/');
            }

//        if (!$this->is_resource_external($src)) {
        	$this->append_resource('styles', $handle, $src);
//        }
        }

        return $src;
    }

    /**
     * Appends a resource to the queue
     * @param $resource_type
     * @param $handle
     */
    function append_resource($resource_type, $handle, $url)
    {
        // Add the handle to the appropriate group
        $resources = &$this->resources[$resource_type];
        $group = 'static';

        // Store the association between the handle and the url. Not all
        // resources are registered before they are enqueued so we need
        // to store this information
        $resources['map'][$handle] = $url;

        // Ensure that the group hasn't been embedded in
        // the name of the handle
        if (strpos($handle, '@') !== FALSE) {
            $parts  = explode('@', $handle);
            $group  = $parts[1];
        }

        // Add the handle to the appropriate group
        $resources[$group][] = $handle;
    }


    function start_lazy_loading()
    {
        echo '<script type="text/javascript">jQuery(function(){Lazy_Resources.load()});</script>';
    }

    function get_type_list()
    {
        return array(
            'C_Resource_Minifier_Installer' =>  'class.resource_minifier_installer.php',
            'A_Resource_Minifier_Routes'    =>  'adapter.resource_minifier_routes.php',
            'C_Resource_Manager_Controller' =>  'class.resource_manager_controller.php',
            'I_Resource_Manager'            =>  'interface.resource_manager.php',
            'M_Resource_Minifier'           =>  'module.resource_minifier.php'
        );
    }
}

new M_Resource_Minifier;
