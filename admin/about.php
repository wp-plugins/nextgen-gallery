<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	function nggallery_admin_about()  {	

	?>

	<div class="wrap">
	<h2><?php _e('Copyright notes / Credits', 'nggallery') ;?></h2>
		<fieldset class="options">
		<legend><?php _e("About this plugin", 'nggallery'); ?></legend>
		<p><?php _e('This plugin is written as last study on WordPress. I took the chance to learn PHP, WordPress and programming technics with this plugin.
		If you study the code of this plugin, you will find out that I mixed a lot of good already existing code and ideas together.', 'nggallery') ;?></p>
		<p><?php _e('So, I would like to thank the following people for their pioneer work (without this work it\'s impossible to create such a plugin so fast)', 'nggallery') ;?></p>		
		<ul>
		<li><a href="http://wordpress.org" target="_blank">The WordPress Team</a> <?php _e('for their great documented code', 'nggallery') ;?></li>
		<li><a href="http://jquery.com" target="_blank">The jQuery Team</a> <?php _e('for jQuery, which is the best Web2.0 framework', 'nggallery') ;?></li>
		<li><a href="http://www.gen-x-design.com" target="_blank">Ian Selby</a> <?php _e('for the fantastic PHP Thumbnail Class', 'nggallery') ;?></li>
		<li><a href="http://www.phpconcept.net" target="_blank">Vincent Blavet</a> <?php _e('for PclZip , a PHP library that manage ZIP archives', 'nggallery') ;?></li>
		<li><a href="http://www.lesterchan.net/" target="_blank">GaMerZ</a> <?php _e('for a lot of very useful plugins and ideas', 'nggallery') ;?></li>
		<li><a href="http://www.sargant.com/" target="_blank">Rob Sargant</a> <?php _e('for RS-Discuss, it saved me a lot of time', 'nggallery') ;?></li>
		<li><a href="http://www.jeroenwijering.com/" target="_blank">Jeroen Wijering</a> <?php _e('for the best Media Flash Scripts on earth', 'nggallery') ;?></li>
		<li><a href="http://watermark.malcherek.com/" target="_blank">Marek Malcherek</a> <?php _e('for the Watermark plugin', 'nggallery') ;?></li>
		<li><a href="http://www.wildbits.de" target="_blank">Thomas Boley</a> <?php _e('for MyGallery, the best gallery plugin concept', 'nggallery') ;?></li>
		</ul>
		<p><?php _e('If you didn\'t find your name on this list and there is some code which I integrate in my plugin, don\'t hesitate to send me a mail.', 'nggallery') ;?></p>		
		<legend><?php _e("How to support ?", 'nggallery'); ?></legend>
		<p><?php _e('There exist several ways to contribute, help or support me in this work. Non of them are mandatory.', 'nggallery') ;?></p>
		<ul>
			<li><strong><?php _e('Send me bugfixes / code changes', 'nggallery') ;?></strong><br /><?php _e('The most motivated support for this plugin are your ideas and brain work', 'nggallery') ;?></li>
			<li><strong><?php _e('Translate my plugin', 'nggallery') ;?></strong><br /><?php _e('To help people to work with this plugin, I would like to have it in all avaivable languages', 'nggallery') ;?></li>
			<li><strong><?php _e('Donate my work via paypal', 'nggallery') ;?></strong><br />
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" >
				<input type="hidden" name="cmd" value="_xclick"/><input type="hidden" name="business" value="alterego@boelinger.com"/>
				<input type="hidden" name="item_name" value="WordPress Plugins www.alexrabe.boelinger.com"/>
				<input type="hidden" name="no_shipping" value="1"/><input type="hidden" name="return" value="http://alexrabe.boelinger.com/" />
				<input type="hidden" name="cancel_return" value="http://alexrabe.boelinger.com/"/>
				<input type="hidden" name="currency_code" value="USD"/>
				<input type="hidden" name="tax" value="0"/>
				<input type="hidden" name="bn" value="PP-DonationsBF"/>
				<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" style="border: none;"/>
				</form><?php _e('No doubt a very usefull and easy motivation :-) I send you an email , when I can buy my first Audi S3 with this money...', 'nggallery') ;?>
			</li>
			<li><strong><?php _e('Place a link to my plugin in your blog/webpage', 'nggallery') ;?></strong><br /><?php _e('Yes, share and trackback is also a good support for this work ', 'nggallery') ;?></li>
		</ul>
		<legend><?php _e("Thanks!", 'nggallery'); ?></legend>
		<p><?php _e('I would like to thank this people which support me in my work :', 'nggallery') ;?></p>
		<p><a href="http://www.boelinger.com/heike/" target="_blank">HEIKE</a>, <?php ngg_list_support(); ?></p>
		</fieldset>
	</div>
	
	<?php
}

function ngg_list_support()	{
/* The list of my supporters. Thanks to all of them !*/
	
	$supporter = array(
	
	"Mike DAmbrose" => "http://www.videovisions.com/",
	"Ideablogger" => "http://www.ablogofideas.net/",
	"Chuck Coury" => "",
	"Kelter" => "http://www.badcat.com",
	"Martin Bahr" => "http://www.law-vodcast.de/",
	"Marcel Kneuer" => "",
	"Martin Bahr" => "http://www.law-vodcast.de/",
	"Alakhnor" => "http://www.alakhnor.info",
	"Rod" => "http://www.le-hiboo.com",
	"Ttancm" => "http://www.ttancm.com/",
	"Francoise Pescatore" => ""	
	);
	
	ksort($supporter);
	$i = count($supporter);
	foreach ($supporter as $name => $url)
	{
		if ($url)
			echo "<a href=\"$url\">$name</a>";
		else
			echo $name;
		$i--;
		if ($i == 1)
			echo " & ";
		elseif ($i)
			echo ", ";
	}	
}
?>