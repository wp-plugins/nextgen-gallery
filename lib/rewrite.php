<?php

/**
* nggRewrite - Rewrite Rules for NextGEN Gallery
*
* sorry wp-guys I didn't understand this at all. 
* I tried it a couple of hours : this is the only pooooor result
*
* @package NextGEN Gallery
* @author Alex Rabe
* @copyright 2008-2011
*/
class nggRewrite {

	// default value
	var $slug = 'nggallery';	

	/**
	* Constructor
	*/
	function nggRewrite() {
		
		// read the option setting
		$this->options = get_option('ngg_options');
		
		// get later from the options
        $this->slug = $this->options['permalinkSlug'];

		/*WARNING: Do nothook rewrite rule regentation on the init hook for anything other than dev. */
		//add_action('init',array(&$this, 'flush'));
		
		add_filter('query_vars', array(&$this, 'add_queryvars') );
		add_filter('wp_title' , array(&$this, 'rewrite_title') );
		
        //DD32 recommend : http://groups.google.com/group/wp-hackers/browse_thread/thread/50ac0d07e30765e9
        //add_filter('rewrite_rules_array', array($this, 'RewriteRules')); 
        	
		if ($this->options['usePermalinks'])
			add_action('generate_rewrite_rules', array(&$this, 'RewriteRules'));
		
		
	} // end of initialization

	/**
	* Get the permalink to a picture/album/gallery given its ID/name/...
	*/
	function get_permalink( $args ) {
		global $wp_rewrite, $wp_query;

		//TODO: Watch out for ticket http://trac.wordpress.org/ticket/6627
		if ($wp_rewrite->using_permalinks() && $this->options['usePermalinks'] ) {
			$post = &get_post(get_the_ID());

			// $_GET from wp_query
			$album = get_query_var('album');
			if ( !empty( $album ) )
				$args ['album'] = $album;
			
			$gallery = get_query_var('gallery');
			if ( !empty( $gallery ) )
				$args ['gallery'] = $gallery;
			
			$gallerytag = get_query_var('gallerytag');
			if ( !empty( $gallerytag ) )
				$args ['gallerytag'] = $gallerytag;
			
			/** urlconstructor =  post url | slug | tags | [nav] | [show]
				tags : 	album, gallery 	-> /album-([0-9]+)/gallery-([0-9]+)/
						pid 			-> /image/([0-9]+)/
						gallerytag		-> /tags/([^/]+)/
				nav	 : 	nggpage			-> /page-([0-9]+)/
				show : 	show=slide		-> /slideshow/
						show=gallery	-> /images/	
			**/

			// 1. Post / Page url + main slug
            $url = trailingslashit ( get_permalink ($post->ID) ) . $this->slug; 

			// 2. Album, pid or tags
			if (isset ($args['album']) && ($args['gallery'] == false) )
				$url .= '/' . $args['album'];
			elseif  (isset ($args['album']) && isset ($args['gallery']) )
				$url .= '/' . $args['album'] . '/' . $args['gallery'];
				
			if  (isset ($args['gallerytag']))
				$url .= '/tags/' . $args['gallerytag'];
				
			if  (isset ($args['pid']))
				$url .= '/image/' . $args['pid'];			
			
			// 3. Navigation
			if  (isset ($args['nggpage']) && ($args['nggpage']) )
				$url .= '/page-' . $args['nggpage'];
            elseif (isset ($args['nggpage']) && ($args['nggpage'] === false) && ( count($args) == 1 ) )
                $url = trailingslashit ( get_permalink ($post->ID) ); // special case instead of showing page-1, we show the clean url
			
			// 4. Show images or Slideshow
			if  (isset ($args['show']))
				$url .= ( $args['show'] == 'slide' ) ? '/slideshow' : '/images';

			return apply_filters('ngg_get_permalink', $url, $args);
			
		} else {			
			// we need to add the page/post id at the start_page otherwise we don't know which gallery is clicked
			if (is_home())
				$args['pageid'] = get_the_ID();
			
			// taken from is_frontpage plugin, required for static homepage
			$show_on_front = get_option('show_on_front');
			$page_on_front = get_option('page_on_front');
			
			if (($show_on_front == 'page') && ($page_on_front == get_the_ID()))
				$args['page_id'] = get_the_ID();
			
			if ( !is_singular() )
				$query = htmlspecialchars( add_query_arg($args, get_permalink( get_the_ID() )) );
			else
				$query = htmlspecialchars( add_query_arg( $args ) );
			
            return apply_filters('ngg_get_permalink', $query, $args);
		}
	}

	/**
	* The permalinks needs to be flushed after activation
	*/
	function flush() { 
		global $wp_rewrite, $ngg;
		
        // reload slug, maybe it changed during the flush routine
        $this->slug = $ngg->options['permalinkSlug'];
        
		if ($ngg->options['usePermalinks'])
			add_action('generate_rewrite_rules', array(&$this, 'RewriteRules'));
			
		$wp_rewrite->flush_rules();
	}

	/**
	* add some more vars to the big wp_query
	*/
	function add_queryvars( $query_vars ){
		
		$query_vars[] = 'pid';
		$query_vars[] = 'pageid';
		$query_vars[] = 'nggpage';
		$query_vars[] = 'gallery';
		$query_vars[] = 'album';
		$query_vars[] = 'gallerytag';
		$query_vars[] = 'show';
        $query_vars[] = 'callback';

		return $query_vars;
	}
	
	/**
	* rewrite the blog title if the gallery is used
	*/	
	function rewrite_title($title) {
		
		$new_title = '';
		// the separataor
		$sep = ' &laquo; ';
		
		// $_GET from wp_query
		$pid     = get_query_var('pid');
		$pageid  = get_query_var('pageid');
		$nggpage = get_query_var('nggpage');
		$gallery = get_query_var('gallery');
		$album   = get_query_var('album');
		$tag  	 = get_query_var('gallerytag');
		$show    = get_query_var('show');

		//TODO: I could parse for the Picture name , gallery etc, but this increase the queries
		//TODO: Class nggdb need to cache the query for the nggfunctions.php

		if ( $show == 'slide' )
			$new_title .= __('Slideshow', 'nggallery') . $sep ;
		elseif ( $show == 'show' )
			$new_title .= __('Gallery', 'nggallery') . $sep ;	

		if ( !empty($pid) )
			$new_title .= __('Picture', 'nggallery') . ' ' . esc_attr($pid) . $sep ;

		if ( !empty($album) )
			$new_title .= __('Album', 'nggallery') . ' ' . esc_attr($album) . $sep ;

		if ( !empty($gallery) )
			$new_title .= __('Gallery', 'nggallery') . ' ' . esc_attr($gallery) . $sep ;
			
		if ( !empty($nggpage) )
			$new_title .= __('Page', 'nggallery') . ' ' . esc_attr($nggpage) . $sep ;
		
		//esc_attr should avoid XSS like http://domain/?gallerytag=%3C/title%3E%3Cscript%3Ealert(document.cookie)%3C/script%3E
		if ( !empty($tag) )
			$new_title .= esc_attr($tag) . $sep;
		
		//prepend the data
		$title = $new_title . $title;
		
		return $title;
	}
	
	/**
	 * Canonical support for a better SEO (Dupilcat content), not longer nedded for Wp 2.9
	 * See : http://googlewebmastercentral.blogspot.com/2009/02/specify-your-canonical.html
	 * 
	 * @deprecated
	 * @return string $meta 
	 */
	function add_canonical_meta()
    {
            // create the meta link
 			$meta  = "\n<link rel='canonical' href='" . get_permalink() ."' />";
 			// add a filter for SEO plugins, so they can remove it
 			echo apply_filters('ngg_add_canonical_meta', $meta);
  			
        return; 
    }
		
	/**
	* The actual rewrite rules
	*/
	function RewriteRules($wp_rewrite) {
        global $ngg;
        
		$rewrite_rules = array (
        
            // new page rewrites
            '(.+?)/' . $this->slug . '/page-([0-9]+)/?$' => 'index.php?pagename=$matches[1]&nggpage=$matches[2]',
    		'(.+?)/' . $this->slug . '/image/([^/]+)/?$' => 'index.php?pagename=$matches[1]&pid=$matches[2]',
    		'(.+?)/' . $this->slug . '/image/([^/]+)/page-([0-9]+)/?$' => 'index.php?pagename=$matches[1]&pid=$matches[2]&nggpage=$matches[3]',
    		'(.+?)/' . $this->slug . '/slideshow/?$' => 'index.php?pagename=$matches[1]&show=slide',
    		'(.+?)/' . $this->slug . '/images/?$' => 'index.php?pagename=$matches[1]&show=gallery',
    		'(.+?)/' . $this->slug . '/tags/([^/]+)/?$' => 'index.php?pagename=$matches[1]&gallerytag=$matches[2]',
    		'(.+?)/' . $this->slug . '/tags/([^/]+)/page-([0-9]+)/?$' => 'index.php?pagename=$matches[1]&gallerytag=$matches[2]&nggpage=$matches[3]',

    		'(.+?)/' . $this->slug . '/([^/]+)/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]',
    		'(.+?)/' . $this->slug . '/([^/]+)/page-([0-9]+)/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]&nggpage=$matches[3]',
    		'(.+?)/' . $this->slug . '/([^/]+)/([^/]+)/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]&gallery=$matches[3]',
    		'(.+?)/' . $this->slug . '/([^/]+)/([^/]+)/slideshow/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]&gallery=$matches[3]&show=slide',
    		'(.+?)/' . $this->slug . '/([^/]+)/([^/]+)/images/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]&gallery=$matches[3]&show=gallery',
    		'(.+?)/' . $this->slug . '/([^/]+)/([^/]+)/page-([0-9]+)/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]&gallery=$matches[3]&nggpage=$matches[4]',
    		'(.+?)/' . $this->slug . '/([^/]+)/([^/]+)/page-([0-9]+)/slideshow/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]&gallery=$matches[3]&nggpage=$matches[4]&show=slide',
    		'(.+?)/' . $this->slug . '/([^/]+)/([^/]+)/page-([0-9]+)/images/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]&gallery=$matches[3]&nggpage=$matches[4]&show=gallery',
    		'(.+?)/' . $this->slug . '/([^/]+)/([^/]+)/image/([^/]+)/?$' => 'index.php?pagename=$matches[1]&album=$matches[2]&gallery=$matches[3]&pid=$matches[4]',
            
            // XML request
            $this->slug . '/slideshow/([0-9]+)/?$' => 'index.php?imagerotator=true&gid=$matches[1]',
		);
        
        $rewrite_rules = array_merge($this->generate_rewrite_rules(), $rewrite_rules);                                                
		$wp_rewrite->rules = array_merge($rewrite_rules, $wp_rewrite->rules);		
	}

	/**
	 * Mainly a copy of the same function in wp-includes\rewrite.php
     * Adding the NGG tags to each post & page. Never found easier and proper way to handle this with other functions.
	 * 
	 * @return array the permalink structure
	 */
	function generate_rewrite_rules() {
        global $wp_rewrite;	   
        
        $new_rules = array(
            '/page-([0-9]+)/' => '&nggpage=[matches]',
    		'/image/([^/]+)/' => '&pid=[matches]',
    		'/image/([^/]+)/page-([0-9]+)/' => '&pid=[matches]&nggpage=[matches]',
    		'/slideshow/' => '&show=slide',
    		'/images/' => '&show=gallery',
    		'/tags/([^/]+)/' => '&gallerytag=[matches]',
    		'/tags/([^/]+)/page-([0-9]+)/' => '&gallerytag=[matches]&nggpage=[matches]',
    		'/([^/]+)/' => '&album=[matches]',
    		'/([^/]+)/page-([0-9]+)/' => '&album=[matches]&nggpage=[matches]',
    		'/([^/]+)/([^/]+)/' => '&album=[matches]&gallery=[matches]',
    		'/([^/]+)/([^/]+)/slideshow/' => '&album=[matches]&gallery=[matches]&show=slide',
    		'/([^/]+)/([^/]+)/images/' => '&album=[matches]&gallery=[matches]&show=gallery',
    		'/([^/]+)/([^/]+)/page-([0-9]+)/' => '&album=[matches]&gallery=[matches]&nggpage=[matches]',
    		'/([^/]+)/([^/]+)/page-([0-9]+)/slideshow/' => '&album=[matches]&gallery=[matches]&nggpage=[matches]&show=slide',
    		'/([^/]+)/([^/]+)/page-([0-9]+)/images/' => '&album=[matches]&gallery=[matches]&nggpage=[matches]&show=gallery',
    		'/([^/]+)/([^/]+)/image/([^/]+)/' => '&album=[matches]&gallery=[matches]&pid=[matches]'        
        );
        
        $permalink_structure =  $wp_rewrite->permalink_structure;      
		
        //get everything up to the first rewrite tag
		$front = substr($permalink_structure, 0, strpos($permalink_structure, '%'));
		//build an array of the tags (note that said array ends up being in $tokens[0])
		preg_match_all('/%.+?%/', $permalink_structure, $tokens);
        
		$num_tokens = count($tokens[0]);

		$index = $wp_rewrite->index; //probably 'index.php'

		//build a list from the rewritecode and queryreplace arrays, that will look something like
		//tagname=$matches[i] where i is the current $i
		for ( $i = 0; $i < $num_tokens; ++$i ) {
			if ( 0 < $i )
				$queries[$i] = $queries[$i - 1] . '&';
			else
				$queries[$i] = '';

			$query_token = str_replace($wp_rewrite->rewritecode, $wp_rewrite->queryreplace, $tokens[0][$i]) . $wp_rewrite->preg_index($i+1);
			$queries[$i] .= $query_token;
		}

		//get the structure, minus any cruft (stuff that isn't tags) at the front
		$structure = $permalink_structure;
		if ( $front != '/' )
			$structure = str_replace($front, '', $structure);

		//create a list of dirs to walk over, making rewrite rules for each level
		//so for example, a $structure of /%year%/%month%/%postname% would create
		//rewrite rules for /%year%/, /%year%/%month%/ and /%year%/%month%/%postname%
		$structure = trim($structure, '/');

		//strip slashes from the front of $front
		$struct = preg_replace('|^/+|', '', $front);

		//get the struct for this dir, and trim slashes off the front
		$struct .= $structure . '/'; //accumulate. see comment near explode('/', $structure) above
		$struct = ltrim($struct, '/');

		//replace tags with regexes
		$match = str_replace($wp_rewrite->rewritecode, $wp_rewrite->rewritereplace, $struct);

		//make a list of tags, and store how many there are in $num_toks
		$num_toks = preg_match_all('/%.+?%/', $struct, $toks);

		//get the 'tagname=$matches[i]'
		$query = ( isset($queries) && is_array($queries) ) ? $queries[$num_toks - 1] : '';

        $post_rewrite = array();
        
        foreach ( $new_rules as $regex => $new_query) {
            
            // first add your nextgen slug
            $final_match = $match . $this->slug;
           
            //add regex parameter
            $final_match .= $regex;
            // check how often we found matches fields
            $count = substr_count($new_query, '[matches]');
            // we need to know how many tags before
            $offset = $num_toks;
            // build the query and count up the matches : tagname=$matches[x]
            for ( $i = 0; $i < $count; $i++ ) {
                $new_query = preg_replace('/\[matches\]/', '$matches[' . ++$offset . ']', $new_query, 1);
            }
            $final_query = $query . $new_query;
            
            //close the match and finalise the query
            $final_match .= '?$';
            $final_query = $index . '?' . $final_query;
            
            $post_rewrite = array_merge($post_rewrite, array($final_match => $final_query));
        }

		return $post_rewrite; //the finished rules. phew!
	}
	
}  // of nggRewrite CLASS

?>