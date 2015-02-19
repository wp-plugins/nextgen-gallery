jQuery(function($) {
    var nextgen_jquery_lightbox_init = function() {
    		var selector = nextgen_lightbox_filter_selector($, $(".ngg_lightbox"));
        selector.lightBox({
            imageLoading:  nextgen_lightbox_loading_img_url,
            imageBtnClose: nextgen_lightbox_close_btn_url,
            imageBtnPrev:  nextgen_lightbox_btn_prev_url,
            imageBtnNext:  nextgen_lightbox_btn_next_url,
            imageBlank:    nextgen_lightbox_blank_img_url
        });
    };
    $(this).bind('refreshed', nextgen_jquery_lightbox_init);
    nextgen_jquery_lightbox_init();

});
