jQuery(document).ready(function($) {

    $('#seo_toolkit_person_picture_button').click(function(e) {
        e.preventDefault();

        var mediaUploader;

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: socialmedia_upload.person,
            button: {
                text: socialmedia_upload.button,
            }, multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#seo_toolkit_person_avatar').val(attachment.url);
        });

        mediaUploader.open();
    });

    $('#seo_toolkit_organization_logo_button').click(function(e) {
        e.preventDefault();

        var mediaUploader;

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: socialmedia_upload.organization,
            button: {
                text: socialmedia_upload.button,
            }, multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#seo_toolkit_organization_logo').val(attachment.url);
        });

        mediaUploader.open();
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
            $('#seo_toolkit_facebook_image').val(attachment.url);
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
            $('#seo_toolkit_twitter_image').val(attachment.url);
        });

        mediaUploader.open();
    });

});
