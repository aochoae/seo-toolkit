jQuery(function ($) {

    /* jQuery UI Tabs */
    $(".wp-admin .seo-toolkit-tabs").tabs({
        activate: function(event, ui) {
            localStorage.setItem(pagenow, ui.newTab.index());
        },
        active: localStorage.getItem(pagenow)
    });

    $('#seo_toolkit_facebook_button').click(function(e) {
        e.preventDefault();

        var mediaUploader;

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: socialmedia_upload.facebook,
            button: {
                text: socialmedia_upload.button,
            }, multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#_seo_toolkit_facebook_image').val(attachment.url);
        });

        mediaUploader.open();
    });

    $('#seo_toolkit_twitter_button').click(function(e) {
        e.preventDefault();

        var mediaUploader;

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: socialmedia_upload.twitter,
            button: {
                text: socialmedia_upload.button,
            }, multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#_seo_toolkit_twitter_image').val(attachment.url);
        });

        mediaUploader.open();
    });
});
