(function($){
    $(document).on('lazy_resources_loaded', function(){
       $('.ngg-album-desc').dotdotdot();
       $('.ngg-albumoverview').each(function(){
          $(this).css('opacity', 1.0);
       });
    });
})(jQuery);