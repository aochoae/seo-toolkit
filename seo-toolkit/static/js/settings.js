jQuery(function ($) {

    /* jQuery UI Tabs */
    $(".wp-admin .seo-toolkit-tabs").tabs({
        activate: function(event, ui) {
            localStorage.setItem(pagenow, ui.newTab.index());
        },
        active: localStorage.getItem(pagenow)
    });

    /* Website profile */
    var profile = {
        init: function() {
            $( 'select#seo_toolkit_website_profile' ).change(function() {
                var option = $(this).val();

                $('#person-name').hide();
                $('#person-picture').hide();
                $('#organization-name').hide();
                $('#organization-logo').hide();

                switch(option) {
                    case 'person':
                        $('#person-name').show();
                        $('#person-picture').show();
                        break;
                    case 'organization':
                        $('#organization-name').show();
                        $('#organization-logo').show();
                        break;
                }
            }).change();
        }
    };
    profile.init();
});
