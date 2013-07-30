jQuery(function($){
    var callback = function(){
        var shutterLinks = {}, shutterSets = {}; shutterReloaded.init();
    };
    $(this).bind('refreshed', callback);

    $(document).on('lazy_resources_loaded', function(){
        var flag = 'shutter';
        if (typeof($(window).data(flag)) == 'undefined')
            $(window).data(flag, true);
        else return;

        callback();
    });
});
