(function($){

    window.Lazy_Resources = {
        urls: [],

        enqueue: function(url){
            this.urls.push(url);
        },

        load: function() {
            if (this.urls.length == 0) {
                // it's possible there's nothing to load, but NextGEN galleries act on this trigger being
                // emitted as their "is the page loaded yet" check-emit this event manually to be certain.
                $(document).trigger('lazy_resources_loaded');
            } else {
                Sid.css(this.urls, function(){
                    var $window = $(document);
                    if (typeof($window.data('lazy_resources_loaded')) == 'undefined') {
                        $window.data('lazy_resources_loaded', true);
                        var urls = Lazy_Resources.urls;
                        $window.trigger('lazy_resources_loaded', urls);
                    }
                });
            }
        }
    };

})(jQuery);