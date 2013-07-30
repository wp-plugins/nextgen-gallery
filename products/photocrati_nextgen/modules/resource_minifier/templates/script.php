<script type="text/javascript">
    (function($){
        var data = {
            urls:   <?php echo json_encode($urls); ?>,
            action: "minify",
            resource: 'scripts'
        };
        $.post(photocrati_ajax.url, data, function(response) {
            if (typeof(response) != 'object') response = JSON.parse(response);
            if (typeof(response.js) == 'string') {
                var script = document.createElement("script");
                script.type = 'text/javascript';
                script.innerHTML = response.js;
                try {
                    $('body').append(script);
                }
                catch (err) {
                    if (typeof(console) != 'undefined') console.log(err.message);
                }
            }
            else alert(response.error);
        });
    })(jQuery);
</script>