jQuery(function($) {
    $('.seo-toolkit_page_seo-toolkit-sitemaps .seo-toolkit-ping').click(function(e) {
        e.preventDefault();

        var sitemap = $(this).data('sitemap');

        var data = {
            'action': 'sitemaps-ping',
            'security': sitemaps_ping.nonce,
            'sitemap': sitemap
        };

        $.post(ajaxurl, data, function(response) {

            var notice = '<div class="notice notice-info is-dismissible"><p>' + sitemaps_ping.submitted + '</p></div>';

            $('#wpbody-content .wp-heading-inline').after(notice);

        }).fail(function() {
            console.log(sitemaps_ping.error);
        });
    });
});

