<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

require_once(ABSPATH . WPINC . '/rss.php');
require_once(ABSPATH . WPINC . '/class-snoopy.php');

function nggallery_admin_overview()  {	
global $wpdb;

?>
  <div class="wrap">
    <h2><?php _e('NextGEN Gallery Overview', 'nggallery') ?></h2>
        
    <div id="zeitgeist">
    	  <h2><?php _e('Summary', 'nggallery') ?></h2>
      <p>
         <?php
          $replace = array
          (
            '<strong>'.$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures").'</strong>',
            '<strong>'.$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggallery").'</strong>',
            '<strong>'.$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggalbum").'</strong>'
           );              
          vprintf(__('There are totally %1$s pictures in %2$s gallerys, which are spread across %3$s albums.', 'nggallery'), $replace);
        ?>
       </p> 
	  <?php if (ngg_version_check()) { ?>
	   <h3><font color="red"><?php _e('New Version available', 'nggallery') ?></font></h3>
	   <p><?php _e('The server reports that a new NextGEN Gallery Version is now available. Please visit the plugin homepage for more information.', 'nggallery') ?></p>
	  <?php } ?>		
       <h3><?php _e('Server Settings', 'nggallery') ?></h3>
      <ul>
      	<?php ngg_get_serverinfo(); ?>
	   </ul>
		<?php ngg_gd_info(); ?>
    </div>
    
    <h3><?php _e('Welcome', 'nggallery') ?></h3>
    
    <p>
      <?php
        $userlevel = '<strong>' . (current_user_can('manage_options') ? __('forum administrator', 'nggallery') : __('forum moderator', 'nggallery')) . '</strong>';
        printf(__('Welcome to NextGEN Gallery. Here you can control your images, galleries and albums. You currently have %s rights.', 'nggallery'), $userlevel);
      ?>
    </p>
    
    <ul>
      <?php if(current_user_can('manage_options')): ?><li><a href="admin.php?page=nggallery-add-gallery"><?php _e('Add a new gallery or import pictures', 'nggallery') ?></a></li><?php endif; ?>
      <li><a href="admin.php?page=nggallery-manage-gallery"><?php _e('Manage galleries and images', 'nggallery') ?></a></li>
      <?php if(current_user_can('manage_options')): ?><li><a href="admin.php?page=nggallery-manage-album"><?php _e('Create and manage albums', 'nggallery') ?></a></li><?php endif; ?>
      <?php if(current_user_can('manage_options')): ?><li><a href="admin.php?page=nggallery-options"><?php _e('Change the settings of NextGEN Gallery', 'nggallery') ?></a></li><?php endif; ?>
    </ul>
    <div id="devnews">
    <h3><?php _e('Latest News', 'nggallery') ?></h3>
    
    <?php
      $rss = fetch_rss('http://alexrabe.boelinger.com/?tag=nextgen-gallery&feed=rss2');
      
      if ( isset($rss->items) && 0 != count($rss->items) )
      {
        $rss->items = array_slice($rss->items, 0, 3);
        foreach ($rss->items as $item)
        {
        ?>
          <h4><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a> &#8212; <?php echo human_time_diff(strtotime($item['pubdate'], time())); ?></h4>
          <p><?php echo '<strong>'.date("F, jS", strtotime($item['pubdate'])).'</strong> - '.$item['description']; ?></p>
        <?php
        }
      }
      else
      {
        ?>
        <p><?php printf(__('Newsfeed could not be loaded.  Check the <a href="%s">front page</a> to check for updates.', 'nggallery'), 'http://alexrabe.boelinger.com/') ?></p>
        <?php
      }
    ?>
    </div>
    <br style="clear: both" />
   </div>
<?php
}

// ***************************************************************
function ngg_gd_info() {
	if(function_exists("gd_info")){
		echo '<div><h3>'.__('GD support', 'nggallery').'</h3><ul>';
		$info = gd_info();
		$keys = array_keys($info);
		for($i=0; $i<count($keys); $i++) {
			if(is_bool($info[$keys[$i]]))
				echo "<li> " . $keys[$i] ." : <strong>" . ngg_gd_yesNo($info[$keys[$i]]) . "</strong></li>\n";
			else
				echo "<li> " . $keys[$i] ." : <strong>" . $info[$keys[$i]] . "</strong></li>\n";
		}
	}
	else {
		echo '<div><h3>'.__('No GD support', 'nggallery').'!</h3><ul>';
	}
	echo '</ul></div>';
}

// ***************************************************************		
function ngg_gd_yesNo($bool){
	if($bool) return __('Yes', 'nggallery');
	else return __('No', 'nggallery');
}

// ***************************************************************
function ngg_get_serverinfo() {
// thx to GaMerZ for WP-ServerInfo	
// http://www.lesterchan.net

	global $wpdb;
	// Get MYSQL Version
	$sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
	// GET SQL Mode
	$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
	if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
	if (empty($sql_mode)) $sql_mode = __('Not set', 'nggallery');
	// Get PHP Safe Mode
	if(ini_get('safe_mode')) $safe_mode = __('On', 'nggallery');
	else $safe_mode = __('Off', 'nggallery');
	// Get PHP Max Upload Size
	if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');	
	else $upload_max = __('N/A', 'nggallery');
	// Get PHP Max Post Size
	if(ini_get('post_max_size')) $post_max = ini_get('post_max_size');
	else $post_max = __('N/A', 'nggallery');
	// Get PHP Max execution time
	if(ini_get('max_execution_time')) $max_execute = ini_get('max_execution_time');
	else $max_execute = __('N/A', 'nggallery');
	// Get PHP Memory Limit 
	if(ini_get('memory_limit')) $memory_limit = ini_get('memory_limit');
	else $memory_limit = __('N/A', 'nggallery');
?>
	<li><?php _e('Operating System', 'nggallery'); ?> : <strong><?php echo PHP_OS; ?></strong></li>
	<li><?php _e('Server', 'nggallery'); ?> : <strong><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></strong></li>
	<li><?php _e('MYSQL Version', 'nggallery'); ?> : <strong><?php echo $sqlversion; ?></strong></li>
	<li><?php _e('SQL Mode', 'nggallery'); ?> : <strong><?php echo $sql_mode; ?></strong></li>
	<li><?php _e('PHP Version', 'nggallery'); ?> : <strong><?php echo PHP_VERSION; ?></strong></li>
	<li><?php _e('PHP Safe Mode', 'nggallery'); ?> : <strong><?php echo $safe_mode; ?></strong></li>
	<li><?php _e('PHP Memory Limit', 'nggallery'); ?> : <strong><?php echo $memory_limit; ?></strong></li>
	<li><?php _e('PHP Max Upload Size', 'nggallery'); ?> : <strong><?php echo $upload_max; ?></strong></li>
	<li><?php _e('PHP Max Post Size', 'nggallery'); ?> : <strong><?php echo $post_max; ?></strong></li>
	<li><?php _e('PHP Max Script Execute Time', 'nggallery'); ?> : <strong><?php echo $max_execute; ?>s</strong></li>
<?php
}

// ***************************************************************	
function ngg_version_check() {
	// check for a new version
	$check_intervall = get_option( "ngg_next_update" );
			
	if ( ($check_intervall < time() ) or (empty($check_intervall)) ) {
		if (class_exists(snoopy)) {
			$client = new Snoopy();
			$client->_fp_timeout = 10;
			if (@$client->fetch(NGGURL) === false) {
				return false;
			}
			
		   	$remote = $client->results;
		   	
			$server_version = unserialize($remote);
			if (is_array($server_version)) {
				if ( version_compare($server_version[0], NGGVERSION, '>') )
				 	return true;
			} 
			// come back in 24 hours :-)
			$check_intervall = time() + 86400;
			update_option( "ngg_next_update", $check_intervall );
			return false;
		}				
	}
}

?>