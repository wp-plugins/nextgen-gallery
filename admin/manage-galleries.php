<?php  

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 	die('You are not allowed to call this page directly.'); }

// *** show main gallery list
function nggallery_manage_gallery_main() {

	global $wpdb, $ngg;
	
	?>
	<div class="wrap">
		<h2><?php _e('Gallery Overview', 'nggallery') ?></h2>
		<br style="clear: both;"/>
		<table class="widefat">
			<thead>
			<tr>
				<th scope="col" ><?php _e('ID') ?></th>
				<th scope="col" ><?php _e('Title', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Description', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Author', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Page ID', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Quantity', 'nggallery') ?></th>
				<th scope="col" ><?php _e('Action'); ?></th>
			</tr>
			</thead>
			<tbody>
<?php
			
$gallerylist = nggdb::find_all_galleries('gid', 'asc', TRUE);

if($gallerylist) {
	foreach($gallerylist as $gallery) {
		$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
		$gid = $gallery->gid;
		$author_user = get_userdata( (int) $gallery->author );
		?>
		<tr id="gallery-<?php echo $gid ?>" <?php echo $class; ?> >
			<th scope="row"><?php echo $gid; ?></th>
			<td>
				<?php if(nggAdmin::can_manage_this_gallery($gallery->author)) { ?>
					<a href="<?php echo wp_nonce_url( $ngg->manage_page->base_page . "&amp;mode=edit&amp;gid=" . $gid, 'ngg_editgallery')?>" class='edit' title="<?php _e('Edit') ?>" >
						<?php echo $gallery->title; ?>
					</a>
				<?php } else { ?>
					<?php echo $gallery->title; ?>
				<?php } ?>
			</td>
			<td><?php echo $gallery->galdesc; ?>&nbsp;</td>
			<td><?php echo $author_user->display_name; ?></td>
			<td><?php echo $gallery->pageid; ?></td>
			<td><?php echo $gallery->counter; ?></td>
			<td>
				<?php if(nggAdmin::can_manage_this_gallery($gallery->author)) : ?>
					<a href="<?php echo wp_nonce_url( $ngg->manage_page->base_page . "&amp;mode=delete&amp;gid=" . $gid, 'ngg_editgallery')?>" class="delete" onclick="javascript:check=confirm( '<?php _e("Delete this gallery ?",'nggallery')?>');if(check==false) return false;"><?php _e('Delete') ?></a>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
} else {
	echo '<tr><td colspan="7" align="center"><strong>'.__('No entries found','nggallery').'</strong></td></tr>';
}
?>			
			</tbody>
		</table>
	</div>
<?php
} 
?>