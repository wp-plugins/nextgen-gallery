<?php  

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 	die('You are not allowed to call this page directly.'); }

// *** show main gallery list
function nggallery_manage_gallery_main() {

	global $wpdb, $ngg, $nggdb, $wp_query;
	
	// get the default path for a new gallery
	$defaultpath = $ngg->options['gallerypath'];	
	
	if (isset ($_POST['addgallery']) && isset ($_POST['galleryname'])){
		check_admin_referer('ngg_addgallery');
		$newgallery = attribute_escape( $_POST['galleryname']);
		if ( !empty($newgallery) )
			nggAdmin::create_gallery($newgallery, $defaultpath);
	}
	
	if ( ! isset( $_GET['paged'] ) || $_GET['paged'] < 1 )
		$_GET['paged'] = 1;
	
	$start = ( $_GET['paged'] - 1 ) * 25;
	$gallerylist = $nggdb->find_all_galleries('gid', 'asc', TRUE, 25, $start);

	$page_links = paginate_links( array(
		'base' => add_query_arg( 'paged', '%#%' ),
		'format' => '',
		'prev_text' => __('&laquo;'),
		'next_text' => __('&raquo;'),
		'total' => $nggdb->paged['max_objects_per_page'],
		'current' => $_GET['paged']
	));

	?>
	<script type="text/javascript"> 
	<!--
	function showDialog( windowId ) {
		tb_show("", "#TB_inline?width=640&height=130&inlineId=" + windowId + "&modal=true", false);
	}
	//-->
	</script>
	<div class="wrap">
		<h2><?php _e('Gallery Overview', 'nggallery'); ?></h2>
		<form class="search-form" action="" method="get">
		<p class="search-box">
			<label class="hidden" for="media-search-input"><?php _e( 'Search Images', 'nggallery' ); ?>:</label>
			<input type="hidden" id="page-name" name="page" value="nggallery-manage-gallery" />
			<input type="text" id="media-search-input" name="s" value="<?php the_search_query(); ?>" />
			<input type="submit" value="<?php _e( 'Search Images', 'nggallery' ); ?>" class="button" />
		</p>
		</form>
		<div class="tablenav">
			<?php if ( current_user_can('NextGEN Upload images') ) : ?>
			<div class="alignleft actions">
				<input id="doaction" class="button-secondary action" type="submit" onclick="showDialog('addGallery');" name="doaction" value="<?php _e('Add new gallery', 'nggallery') ?>"/>
			</div>
			<?php endif; ?>
		</div>
		<?php if ( $page_links ) : ?>
		<div class="tablenav">
			<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
				number_format_i18n( ( $_GET['paged'] - 1 ) * $nggdb->paged['objects_per_page'] + 1 ),
				number_format_i18n( min( $_GET['paged'] * $nggdb->paged['objects_per_page'], $nggdb->paged['total_objects'] ) ),
				number_format_i18n( $nggdb->paged['total_objects'] ),
				$page_links
			); echo $page_links_text; ?></div>
		</div>
		<?php endif; ?>
		<table class="widefat" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" ><?php _e('ID'); ?></th>
				<th scope="col" ><?php _e('Title', 'nggallery'); ?></th>
				<th scope="col" ><?php _e('Description', 'nggallery'); ?></th>
				<th scope="col" ><?php _e('Author', 'nggallery'); ?></th>
				<th scope="col" ><?php _e('Page ID', 'nggallery'); ?></th>
				<th scope="col" ><?php _e('Quantity', 'nggallery'); ?></th>
				<th scope="col" ><?php _e('Action'); ?></th>
			</tr>
			</thead>
			<tbody>
<?php

if($gallerylist) {
	foreach($gallerylist as $gallery) {
		$class = ( !isset($class) || $class == 'class="alternate"' ) ? '' : 'class="alternate"';
		$gid = $gallery->gid;
		$name = (empty($gallery->title) ) ? $gallery->name : $gallery->title;
		$author_user = get_userdata( (int) $gallery->author );
		?>
		<tr id="gallery-<?php echo $gid ?>" <?php echo $class; ?> >
			<th scope="row"><?php echo $gid; ?></th>
			<td>
				<?php if (nggAdmin::can_manage_this_gallery($gallery->author)) { ?>
					<a href="<?php echo wp_nonce_url( $ngg->manage_page->base_page . '&amp;mode=edit&amp;gid=' . $gid, 'ngg_editgallery')?>" class='edit' title="<?php _e('Edit'); ?>" >
						<?php echo nggGallery::i18n($name); ?>
					</a>
				<?php } else { ?>
					<?php echo nggGallery::i18n($gallery->title); ?>
				<?php } ?>
			</td>
			<td><?php echo nggGallery::i18n($gallery->galdesc); ?>&nbsp;</td>
			<td><?php echo $author_user->display_name; ?></td>
			<td><?php echo $gallery->pageid; ?></td>
			<td><?php echo $gallery->counter; ?></td>
			<td>
				<?php if (nggAdmin::can_manage_this_gallery($gallery->author)) : ?>
					<a href="<?php echo wp_nonce_url( $ngg->manage_page->base_page . '&amp;mode=delete&amp;gid=' . $gid, 'ngg_editgallery')?>" class="delete" onclick="javascript:check=confirm( '<?php _e('Delete this gallery ?', 'nggallery'); ?>');if(check==false) return false;"><?php _e('Delete'); ?></a>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
} else {
	echo '<tr><td colspan="7" align="center"><strong>' . __('No entries found', 'nggallery') . '</strong></td></tr>';
}
?>			
			</tbody>
		</table>
	</div>
	<!-- #addGallery -->
	<div id="addGallery" style="display: none;" >
		<form id="form-tags" method="POST" accept-charset="utf-8">
		<?php wp_nonce_field('ngg_addgallery'); ?>
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
		  	<tr>
		    	<td>
					<strong><?php _e('New Gallery', 'nggallery') ;?>:</strong> <input type="text" size="35" name="galleryname" value="" /><br />
					<?php if(!IS_WPMU) { ?>
					<?php _e('Create a new , empty gallery below the folder', 'nggallery') ;?>  <strong><?php echo $defaultpath ?></strong><br />
					<?php } ?>
					<i>( <?php _e('Allowed characters for file and folder names are', 'nggallery') ;?>: a-z, A-Z, 0-9, -, _ )</i>
				</td>
		  	</tr>
		  	<tr align="right">
		    	<td class="submit">
		    		<input class="button-primary" type="submit" name="addgallery" value="<?php _e('OK','nggallery'); ?>" />
		    		&nbsp;
		    		<input class="button-secondary" type="reset" value="&nbsp;<?php _e('Cancel', 'nggallery'); ?>&nbsp;" onclick="tb_remove()"/>
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#addGallery -->
<?php
} 
?>