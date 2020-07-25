jQuery(function($) {
    $('.seo-toolkit_page_seo-toolkit-sitemaps .seo-toolkit-ping').click(function(e) {
        e.preventDefault();

        var data = {
            'action': 'sitemaps-ping',
            'security': sitemaps_ping.nonce
        };

        $.post(ajaxurl, data, function(response) {
            location.reload();
        });
    });
});

