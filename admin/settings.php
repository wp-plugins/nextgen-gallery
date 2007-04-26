<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	function nggallery_admin_options()  {
	
	global $wpdb;

	// get the options
	$ngg_options=get_option('ngg_options');	

	if ( isset($_POST['updateoption']) ) {	
		// get the hidden option fields, taken from WP core
		if ( $_POST['page_options'] )	
			$options = explode(',', stripslashes($_POST['page_options']));
		if ($options) {
			foreach ($options as $option) {
				$option = trim($option);
				$value = trim($_POST[$option]);
		//		$value = sanitize_option($option, $value); // This does stripslashes on those that need it
				$ngg_options[$option] = $value;
			}
		}

		update_option('ngg_options', $ngg_options);
	 	$messagetext = '<font color="green">'.__('Update successfully','nggallery').'</font>';
	}		
		
	// message windows
	if(!empty($messagetext)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$messagetext.'</p></div>'; }
	
	?>
	<script type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo NGGALLERY_URLPATH ?>admin/js/interface.js"></script>
	<script type="text/javascript">
		var currentTab = null;
		var inSlide = false;
		$(document).ready(
			function()
			{
				var url, tab = 0, tabIteration = 0;
				
				url = window.location.href.split("#");
				if (url.length == 2 && url[1].indexOf('-slider') > 0) {
					currentTab = $('#' + url[1].substr(0, url[1].length-7));
					if (currentTab.size() == 1) {
						$('#slider div').each(
							function(iteration)
							{
								if(this === currentTab.get(0)) {
									tabIteration = iteration;
								}	
							}
						);
					}
				}
				
				if(!currentTab) {
					currentTab = $('#slider div:first');
				}
				//Nicer, but buggy: DropToggleRight
				currentTab.SlideToggleUp(500);
				$('#tabs a')
					.eq(tabIteration).addClass('active')
					.end()
					.bind('click', switchTab);
			}
		);

		var switchTab = function()
		{
			// get id from link
			var tabName = this.href.split('#')[1];
			this.blur();
			if (inSlide == false && currentTab.get(0) != $('#' + tabName.substr(0, tabName.length-7)).get(0)) {
				$('#tabs a').removeClass('active');
				$(this).addClass('active');
				inSlide = true;
				currentTab.SlideToggleUp(
					500,
					function()
					{
						currentTab = $('#' + tabName.substr(0, tabName.length-7)).SlideToggleUp(500, function(){inSlide=false;});
					}
				);
			} else {
				return false;
			}
		};
		
		function insertcode(value) {
			var effectcode;
			switch (value) {
			  case "none":
			    effectcode = "";
			    break;
			  case "thickbox":
			    effectcode = 'class="thickbox" rel="%GALLERY_NAME%"';
			    break;
			  case "lightbox":
			    effectcode = 'rel="lightbox[%GALLERY_NAME%]"';
			    break;
			  case "highslide":
			    effectcode = 'class="highslide" onclick="return hs.expand(this, { slideshowGroup: %GALLERY_NAME% })"';
			    break;
			  default:
			    break;
			}
			$("#thumbCode").val(effectcode);
		};
		function setcolor(fileid,color) {
			$(fileid).css("background", color );
		};
	</script>
	<div class="wrap" style="text-align: center">
		<div id="tabs">
			<a href="#generaloptions-slider"><?php _e('General Options', 'nggallery') ;?></a> -
			<a href="#thumbnails-slider"><?php _e('Thumbnails', 'nggallery') ;?></a> -
			<a href="#images-slider"><?php _e('Images', 'nggallery') ;?></a> -
			<a href="#gallery-slider"><?php _e('Gallery', 'nggallery') ;?></a> -
			<a href="#effects-slider"><?php _e('Effects', 'nggallery') ;?></a> -
			<a href="#watermark-slider"><?php _e('Watermark', 'nggallery') ;?></a> -
			<a href="#slideshow-slider"><?php _e('Slideshow', 'nggallery') ;?></a>
		</div>
	</div>
	
	<div class="wrap">
	<div id="slider">
	
		<!-- General Options -->
		
		<div id="generaloptions" style="display:none">
			<h2><?php _e('General Options','nggallery'); ?></h2>
			<form name="generaloptions" method="post">
			<input type="hidden" name="page_options" value="gallerypath,scanfolder,deleteImg" />
			<fieldset class="options"> 
				<table class="optiontable editform">
					<tr valign="top">
						<th align="left"><?php _e('Gallery path','nggallery') ?></th>
						<td><input type="text" size="35" name="gallerypath" value="<?php echo $ngg_options[gallerypath]; ?>" title="TEST" /><br />
						<?php _e('This is the default path for all galleries','nggallery') ?></td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Scan folders during runtime','nggallery') ?></th>
						<td><input type="checkbox" name="scanfolder" value="1" <?php checked('1', $ngg_options[scanfolder]); ?> /><br />
						<?php _e('Search automatic in the folders for new images (not working)','nggallery') ?></td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Delete image files','nggallery') ?></th>
						<td><input type="checkbox" name="deleteImg" value="1" <?php checked('1', $ngg_options[deleteImg]); ?> /><br />
						<?php _e('Delete files, when removing a gallery in the database','nggallery') ?></td>
					</tr>
				</table>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</fieldset>	
			</form>	
		</div>	
		
		<!-- Thumbnail settings -->
		
		<div id="thumbnails" style="display:none">
			<h2><?php _e('Thumbnail settings','nggallery'); ?></h2>
			<form name="thumbnailsettings" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#thumbnails-slider'; ?>" >
			<input type="hidden" name="page_options" value="thumbwidth,thumbheight,thumbfix,thumbcrop,thumbquality,thumbResampleMode" />
			<fieldset class="options"> 
				<table class="optiontable editform">
					<tr valign="top">
						<th align="left"><?php _e('Width x height (in pixel)','nggallery') ?></th>
						<td><input type="text" size="4" maxlength="4" name="thumbwidth" value="<?php echo $ngg_options[thumbwidth]; ?>" /> x <input type="text" size="4" maxlength="4" name="thumbheight" value="<?php echo $ngg_options[thumbheight]; ?>" /><br />
						<?php _e('This values are maxium values ','nggallery') ?></td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Set fix dimension','nggallery') ?></th>
						<td><input type="checkbox" name="thumbfix" value="1" <?php checked('1', $ngg_options[thumbfix]); ?> /><br />
						<?php _e('Ignore the aspect ratio, no portrait thumbnails','nggallery') ?></td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Crop thumbnail from image','nggallery') ?></th>
						<td><input type="checkbox" name="thumbcrop" value="1" <?php checked('1', $ngg_options[thumbcrop]); ?> /><br />
						<?php _e('Create square thumbnails from the center','nggallery') ?></td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Thumbnail quality','nggallery') ?></th>
						<td><input type="text" size="3" maxlength="3" name="thumbquality" value="<?php echo $ngg_options[thumbquality]; ?>" /> %</td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Resample Mode','nggallery') ?></th>
						<td><input type="text" size="1" maxlength="1" name="thumbResampleMode" value="<?php echo $ngg_options[thumbResampleMode]; ?>" /><br />
						<?php _e('Value between 1-5 (higher value, more CPU load)','nggallery') ?></td>
					</tr>
				</table>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</fieldset>	
			</form>	
		</div>
		
		<!-- Image settings -->
		
		<div id="images" style="display:none">
			<h2><?php _e('Image settings','nggallery'); ?></h2>
			<form name="imagesettings" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#images-slider'; ?>" >
			<input type="hidden" name="page_options" value="imgResize,imgWidth,imgHeight,imgQuality,imgResampleMode,imgSinglePicLink" />
			<fieldset class="options"> 
				<table class="optiontable">
					<tr valign="top">
						<th scope="row"><label for="fixratio"><?php _e('Resize Images','nggallery') ?></label></th>
						<td><input id="fixratio" type="checkbox" name="imgResize" value="1" <?php checked('1', $ngg_options[imgResize]); ?> /> </td>
						<td><input type="text" size="5" name="imgWidth" value="<?php echo $ngg_options[imgWidth]; ?>" /> x <input type="text" size="5" name="imgHeight" value="<?php echo $ngg_options[imgHeight]; ?>" /><br />
						<?php _e('Width x height (in pixel). NextGEN Gallery will keep ratio size','nggallery') ?></td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Image quality','nggallery') ?></th>
						<td></td>
						<td><input type="text" size="3" maxlength="3" name="imgQuality" value="<?php echo $ngg_options[imgQuality]; ?>" /> %</td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Resample Mode','nggallery') ?></th>
						<td></td>
						<td><input type="text" size="1" maxlength="1" name="imgResampleMode" value="<?php echo $ngg_options[imgResampleMode]; ?>" /><br />
						<?php _e('Value between 1-5 (higher value, more CPU load)','nggallery') ?></td>
					</tr>
					<tr valign="top">
						<th align="left"><?php _e('Add link in [singlepic] tag ','nggallery') ?></th>
						<td></td>
						<td><input type="checkbox" name="imgSinglePicLink" value="1" <?php checked('1', $ngg_options[imgSinglePicLink]); ?> /><br />
						<?php _e('Add the fullsize picture as link. Didn\'t support watermark mode on the fly.','nggallery') ?></td>
					</tr>
				</table>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</fieldset>	
			</form>	
		</div>
		
		<!-- Gallery settings -->
		
		<div id="gallery" style="display:none">
			<h2><?php _e('Gallery settings','nggallery'); ?></h2>
			<form name="galleryform" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#gallery-slider'; ?>" >
			<input type="hidden" name="page_options" value="galImages,galShowSlide,galTextSlide,galTextGallery,galShowOrder,galSort" />
			<fieldset class="options"> 
				<table class="optiontable">
					<tr>
						<th valign="top"><?php _e('Number of images per page','nggallery') ?>:</th>
						<td><input type="text" name="galImages" value="<?php echo $ngg_options[galImages] ?>" size="3" maxlength="3" /><br />
						<?php _e('Value 0 = No Navigation, all images on one page','nggallery') ?></td>
					</tr>
					<tr>
						<th valign="top"><?php _e('Integrate slideshow','nggallery') ?>:</th>
						<td><input name="galShowSlide" type="checkbox" value="1" <?php checked('1', $ngg_options[galShowSlide]); ?> />
							<input type="text" name="galTextSlide" value="<?php echo $ngg_options[galTextSlide] ?>" size="20" />
							<input type="text" name="galTextGallery" value="<?php echo $ngg_options[galTextGallery] ?>" size="20" />
						</td>
					</tr>
					<tr>
						<th valign="top"><?php _e('Show first','nggallery') ?>:</th>
						<td><label><input name="galShowOrder" type="radio" value="gallery" <?php checked('gallery', $ngg_options[galShowOrder]); ?> /> <?php _e('Thumbnails', 'nggallery') ;?></label><br />
						<label><input name="galShowOrder" type="radio" value="slide" <?php checked('slide', $ngg_options[galShowOrder]); ?> /> <?php _e('Slideshow', 'nggallery') ;?></label></td>
					</tr>
					<tr>
						<th valign="top"><?php _e('Sort thumbnails','nggallery') ?>:</th>
						<td><label><input name="galSort" type="radio" value="pid" <?php checked('pid', $ngg_options[galSort]); ?> /> <?php _e('Image ID', 'nggallery') ;?></label><br />
						<label><input name="galSort" type="radio" value="filename" <?php checked('filename', $ngg_options[galSort]); ?> /> <?php _e('File name', 'nggallery') ;?></label><br />
						<label><input name="galSort" type="radio" value="alttext" <?php checked('alttext', $ngg_options[galSort]); ?> /> <?php _e('Alt / Title text', 'nggallery') ;?></label></td>
					</tr>
				</table>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</fieldset>	
			</form>	
		</div>
		
		<!-- Effects settings -->
		
		<div id="effects" style="display:none">
			<h2><?php _e('Effects','nggallery'); ?></h2>
			<form name="effectsform" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#effects-slider'; ?>" >
			<input type="hidden" name="page_options" value="thumbEffect,thumbCode" />
			<p><?php _e('Here you can select the thumbnail effect, NextGEN Gallery will integrate the required HTML code in the images. Please note that only the Thickbox effect will automatic added to your theme.','nggallery'); ?>
			<?php _e('With the placeholder','nggallery'); ?><strong> %GALLERY_NAME% </strong> <?php _e('you can activate a navigation through the images (depend on the effect). Change the code line only , when you use a different thumbnail effect or you know what you do.','nggallery'); ?></p>
			<fieldset class="options"> 
				<table class="optiontable">
					<tr valign="top">
						<th><?php _e('JavaScript Thumbnail effect','nggallery') ?>:</th>
						<td>
						<select size="1" id="thumbEffect" name="thumbEffect" onchange="insertcode(this.value)">
							<option value="none" <?php selected('none', $ngg_options[thumbEffect]); ?> ><?php _e('None', 'nggallery') ;?></option>
							<option value="thickbox" <?php selected('thickbox', $ngg_options[thumbEffect]); ?> ><?php _e('Thickbox', 'nggallery') ;?></option>
							<option value="lightbox" <?php selected('lightbox', $ngg_options[thumbEffect]); ?> ><?php _e('Lightbox', 'nggallery') ;?></option>
							<option value="highslide" <?php selected('highslide', $ngg_options[thumbEffect]); ?> ><?php _e('Highslide', 'nggallery') ;?></option>
							<option value="custom" <?php selected('custom', $ngg_options[thumbEffect]); ?> ><?php _e('Custom', 'nggallery') ;?></option>
						</select>
					</tr>
					<tr valign="top">
						<th><?php _e('Link Code line','nggallery') ?> :</th>
						<td><textarea id="thumbCode" name="thumbCode" cols="50" rows="5"><?php echo htmlspecialchars(stripslashes($ngg_options[thumbCode])); ?></textarea></td>
					</tr>
				</table>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</fieldset>	
			</form>	
		</div>
		
		<!-- Watermark settings -->
		
		<?php
		$imageID = $wpdb->get_var("SELECT MIN(pid) FROM $wpdb->nggpictures");
		$imageID = $wpdb->get_row("SELECT * FROM $wpdb->nggpictures WHERE pid = '$imageID'");	
		if ($imageID) $imageURL = '<img width="75%" src="'.NGGALLERY_URLPATH.'nggshow.php?pid='.$imageID->pid.'&amp;mode=watermark&amp;width=320&amp;height=240" alt="'.$imageID->alttext.'" title="'.$imageID->alttext.'" />';

		?>
		<div id="watermark" style="display:none">
			<h2><?php _e('Watermark','nggallery'); ?></h2>
			<form name="watermarkform" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#watermark-slider'; ?>" >
			<input type="hidden" name="page_options" value="wmPos,wmXpos,wmYpos,wmType,wmPath,wmFont,wmSize,wmColor,wmText,wmOpaque" />
			<div id="zeitgeist">
				<h3><?php _e('Preview','nggallery') ?></h3>
				<p><center><?php echo $imageURL; ?></center></p>
				<h3><?php _e('Position','nggallery') ?></h3>
			    <table width="80%" border="0">
				<tr>
					<td valign="top">
						<strong><?php _e('Position','nggallery') ?></strong><br />
						<table border="1">
						<tr>
							<td><input type="radio" name="wmPos" value="topLeft" <?php checked('topLeft', $ngg_options[wmPos]); ?> /></td>
							<td><input type="radio" name="wmPos" value="topCenter" <?php checked('topCenter', $ngg_options[wmPos]); ?> /></td>
							<td><input type="radio" name="wmPos" value="topRight" <?php checked('topRight', $ngg_options[wmPos]); ?> /></td>
						</tr>
						<tr>
							<td><input type="radio" name="wmPos" value="midLeft" <?php checked('midLeft', $ngg_options[wmPos]); ?> /></td>
							<td><input type="radio" name="wmPos" value="midCenter" <?php checked('midCenter', $ngg_options[wmPos]); ?> /></td>
							<td><input type="radio" name="wmPos" value="midRight" <?php checked('midRight', $ngg_options[wmPos]); ?> /></td>
						</tr>
						<tr>
							<td><input type="radio" name="wmPos" value="botLeft" <?php checked('botLeft', $ngg_options[wmPos]); ?> /></td>
							<td><input type="radio" name="wmPos" value="botCenter" <?php checked('botCenter', $ngg_options[wmPos]); ?> /></td>
							<td><input type="radio" name="wmPos" value="botRight" <?php checked('botRight', $ngg_options[wmPos]); ?> /></td>
						</tr>
						</table>
					</td>
					<td valign="top">
						<strong><?php _e('Offset','nggallery') ?></strong><br />
						<table border="0">
							<tr>
								<td>x</td>
								<td><input type="text" name="wmXpos" value="<?php echo $ngg_options[wmXpos] ?>" size="4" /> px</td>
							</tr>
							<tr>
								<td>y</td>
								<td><input type="text" name="wmYpos" value="<?php echo $ngg_options[wmYpos] ?>" size="4" /> px</td>
							</tr>
						</table>
					</td>
				</tr>
				</table>
			</div> 
			<fieldset class="options">
				<table class="optiontable" border="0">
					<tr>
						<td align="left" colspan="2"><label><input type="radio" name="wmType" value="image" <?php checked('image', $ngg_options[wmType]); ?> /> <?php _e('Use image as watermark','nggallery') ?></label></td>
					</tr>
					<tr>
						<th><?php _e('URL to file','nggallery') ?> :</th>
						<td><input type="text" size="40" name="wmPath" value="<?php echo $ngg_options[wmPath]; ?>" /></td>
					</tr>
					<tr>
						<td colspan="2"><hr /></td>
					</tr>
					<tr>
						<td align="left" colspan="2"><label><input type="radio" name="wmType" value="text" <?php checked('text', $ngg_options[wmType]); ?> /> <?php _e('Use text as watermark','nggallery') ?></label></td>
					</tr>
					<tr>
						<th><?php _e('Font','nggallery') ?>:</th>
						<td><select name="wmFont" size="1">	<?php 
								$fontlist = ngg_get_TTFfont();
								foreach ( $fontlist as $fontfile ) {
									echo "\n".'<option value="'.$fontfile.'" '.ngg_input_selected($fontfile, $ngg_options[wmFont]).' >'.$fontfile.'</option>';
								}
								?>
							</select><br />
							<?php _e('You can upload more fonts in the folder <strong>nggallery/fonts</strong>','nggallery') ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Size','nggallery') ?>:</th>
						<td><input type="text" name="wmSize" value="<?php echo $ngg_options[wmSize] ?>" size="4" maxlength="2" /> px</td>
					</tr>
					<tr>
						<th><?php _e('Color','nggallery') ?>:</th>
						<td><input type="text" size="6" maxlength="6" id="wmColor" name="wmColor" onchange="setcolor('#previewText', this.value)" value="<?php echo $ngg_options[wmColor] ?>" />
						<input type="text" size="1" readonly="readonly" id="previewText" style="background-color: #<?php echo $ngg_options[wmColor] ?>" /> <?php _e('(hex w/o #)','nggallery') ?></td>
					</tr>
					<tr>
						<th valign="top"><?php _e('Text','nggallery') ?>:</th>
						<td><textarea name="wmText" cols="40" rows="4"><?php echo $ngg_options[wmText] ?></textarea></td>
					</tr>
					<tr>
						<th><?php _e('Opaque','nggallery') ?>:</th>
						<td><input type="text" name="wmOpaque" value="<?php echo $ngg_options[wmOpaque] ?>" size="3" maxlength="3" /> % </td>
					</tr>
				</table>
			</fieldset>
			<div class="clear"> &nbsp; </div>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</form>	
			<center>Based on Marekki's Watermark plugin</center>
		</div>
		
		<!-- Slideshow settings -->
		
		<div id="slideshow" style="display:none">
		<form name="player_options" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']).'#slideshow-slider'; ?>" >
		<input type="hidden" name="page_options" value="irWidth,irHeight,irShuffle,irLinkfromdisplay,irShownavigation,irShowicons,irOverstretch,irRotatetime,irTransition,irBackcolor,irFrontcolor,irLightcolor" />
		<h2><?php _e('Slideshow','nggallery'); ?></h2>
		<fieldset class="options">
		<?php if (!NGGALLERY_IREXIST) { ?><p><div id="message" class="error fade"><p><?php _e('The imagerotator.swf is not in the nggallery folder, the slideshow will not work.','nggallery') ?></p></div></p><?php }?>
		<p><?php _e('The settings are used in the Flash Image Rotator Version 3.2 .', 'nggallery') ?> 
		   <?php _e('See more information for the Flash Player on the web page', 'nggallery') ?> <a href="http://www.jeroenwijering.com/?item=Flash_Image_Rotator" target="_blank">Flash Image Rotator from Jeroen Wijering</a>.<br />
				<table class="optiontable" border="0" >
					<tr>
						<th><?php _e('Default size (W x H)','nggallery') ?>:</th>
						<td><input type="text" size="3" maxlength="4" name="irWidth" value="<?php echo $ngg_options[irWidth] ?>" /> x
						<input type="text" size="3" maxlength="4" name="irHeight" value="<?php echo $ngg_options[irHeight] ?>" /></td>
					</tr>					
					<tr>
						<th><?php _e('Shuffle mode','nggallery') ?>:</th>
						<td><input name="irShuffle" type="checkbox" value="1" <?php checked('1', $ngg_options[irShuffle]); ?> /></td>
					</tr>
					<tr>
						<th><?php _e('Show next image on click','nggallery') ?>:</th>
						<td><input name="irLinkfromdisplay" type="checkbox" value="1" <?php checked('1', $ngg_options[irLinkfromdisplay]); ?> /></td>
					</tr>					
					<tr>
						<th><?php _e('Show navigation bar','nggallery') ?>:</th>
						<td><input name="irShownavigation" type="checkbox" value="1" <?php checked('1', $ngg_options[irShownavigation]); ?> /></td>
					</tr>
					<tr>
						<th><?php _e('Show loading icon','nggallery') ?>:</th>
						<td><input name="irShowicons" type="checkbox" value="1" <?php checked('1', $ngg_options[irShowicons]); ?> /></td>
					</tr>
					<tr>
						<th><?php _e('Stretch image','nggallery') ?>:</th>
						<td>
						<select size="1" name="irOverstretch">
							<option value="true" <?php selected('true', $ngg_options[irOverstretch]); ?> ><?php _e('true', 'nggallery') ;?></option>
							<option value="false" <?php selected('false', $ngg_options[irOverstretch]); ?> ><?php _e('false', 'nggallery') ;?></option>
							<option value="fit" <?php selected('fit', $ngg_options[irOverstretch]); ?> ><?php _e('fit', 'nggallery') ;?></option>
							<option value="none" <?php selected('none', $ngg_options[irOverstretch]); ?> ><?php _e('none', 'nggallery') ;?></option>
						</select>
						</td>
					</tr>
					<tr>					
						<th><?php _e('Duration time','nggallery') ?>:</th>
						<td><input type="text" size="3" maxlength="3" name="irRotatetime" value="<?php echo $ngg_options[irRotatetime] ?>" /> <?php _e('sec.', 'nggallery') ;?></td>
					</tr>					
					<tr>					
						<th><?php _e('Transition / Fade effect','nggallery') ?>:</th>
						<td>
						<select size="1" name="irTransition">
							<option value="fade" <?php selected('fade', $ngg_options[irTransition]); ?> ><?php _e('fade', 'nggallery') ;?></option>
							<option value="bgfade" <?php selected('bgfade', $ngg_options[irTransition]); ?> ><?php _e('bgfade', 'nggallery') ;?></option>
							<option value="circles" <?php selected('circles', $ngg_options[irTransition]); ?> ><?php _e('circles', 'nggallery') ;?></option>
							<option value="blocks" <?php selected('blocks', $ngg_options[irTransition]); ?> ><?php _e('blocks', 'nggallery') ;?></option>
							<option value="fluids" <?php selected('fluids', $ngg_options[irTransition]); ?> ><?php _e('fluids', 'nggallery') ;?></option>
							<option value="lines" <?php selected('lines', $ngg_options[irTransition]); ?> ><?php _e('lines', 'nggallery') ;?></option>
							<option value="random" <?php selected('random', $ngg_options[irTransition]); ?> ><?php _e('random', 'nggallery') ;?></option>
						</select>
					</tr>
					<tr>
						<th><?php _e('Background Color','nggallery') ?>:</th>
						<td><input type="text" size="6" maxlength="6" id="irBackcolor" name="irBackcolor" onchange="setcolor('#previewBack', this.value)" value="<?php echo $ngg_options[irBackcolor] ?>" />
						<input type="text" size="1" readonly="readonly" id="previewBack" style="background-color: #<?php echo $ngg_options[irBackcolor] ?>" /></td>
					</tr>
					<tr>					
						<th><?php _e('Texts / Buttons Color','nggallery') ?>:</th>
						<td><input type="text" size="6" maxlength="6" id="irFrontcolor" name="irFrontcolor" onchange="setcolor('#previewFront', this.value)" value="<?php echo $ngg_options[irFrontcolor] ?>" />
						<input type="text" size="1" readonly="readonly" id="previewFront" style="background-color: #<?php echo $ngg_options[irFrontcolor] ?>" /></td>
					</tr>
					<tr>					
						<th><?php _e('Rollover / Active Color','nggallery') ?>:</th>
						<td><input type="text" size="6" maxlength="6" id="irLightcolor" name="irLightcolor" onchange="setcolor('#previewLight', this.value)" value="<?php echo $ngg_options[irLightcolor] ?>" />
						<input type="text" size="1" readonly="readonly" id="previewLight" style="background-color: #<?php echo $ngg_options[irLightcolor] ?>" /></td>
					</tr>
					</table>
				<div class="clear"> &nbsp; </div>
				<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</fieldset>
		</form>
		</div>
	</div>
	</div>

	<?php
}

function ngg_get_TTFfont() {
	
	$ttf_fonts = array ();
	
	// Files in wp-content/plugins/nggallery/fonts directory
	$plugin_root = NGGALLERY_ABSPATH."fonts";
	
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
						if (preg_match('|\.ttf$|', $subfile))
							$ttf_fonts[] = "$file/$subfile";
					}
				}
			} else {
				if (preg_match('|\.ttf$|', $file))
					$ttf_fonts[] = $file;
			}
		}
	}

	return $ttf_fonts;
}

/**********************************************************/
// taken from WP Core

function ngg_input_selected( $selected, $current) {
	if ( $selected == $current)
		return ' selected="selected"';
}
	
function ngg_input_checked( $checked, $current) {
	if ( $checked == $current)
		return ' checked="checked"';
}
?>