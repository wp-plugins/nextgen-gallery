jQuery(function($){
	$('#wp-admin-bar-ngg-menu').pointer({
		content: nggAdmin.content,
		pointerClass: 'pointer ngg_latest_news_notice',
		close: function(){
			var data = {
				action: 'hide_news_notice',
				nonce: nggAdmin.nonce
			}
			jQuery.post(ajaxurl, data);
		}
	}).pointer('open');
});