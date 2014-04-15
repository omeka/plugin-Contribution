jQuery(document).ready(function() {
    // Handle approved / proposed from any status.
    jQuery('.contribution.toggle-status').click(function(event) {
        event.preventDefault();
        var id = jQuery(this).attr('id');
        var current = jQuery('#' + id);
        id = id.substr(id.lastIndexOf('-') + 1);
        var ajaxUrl = jQuery(this).attr('href') + '/contribution/ajax/update';
        jQuery(this).addClass('transmit');
        if (jQuery(this).hasClass('approved')) {
            jQuery.post(ajaxUrl,
                {
                    status: 'proposed',
                    id: id
                },
                function(data) {
                    if (data == 'private') {
                        current.removeClass('transmit');
                    } else {
                        current.addClass('proposed');
                        current.removeClass('approved');
                        current.removeClass('rejected');
                        current.removeClass('private');
                        current.removeClass('transmit');
                        if (current.text() != '') {
                            current.text(Omeka.messages.contribution.proposed);
                        }
                    }
                }
            );
        } else {
            jQuery.post(ajaxUrl,
                {
                    status: 'approved',
                    id: id
                },
                function(data) {
                    if (data == 'private') {
                        current.removeClass('transmit');
                    } else {
                        current.addClass('approved');
                        current.removeClass('proposed');
                        current.removeClass('rejected');
                        current.removeClass('private');
                        current.removeClass('transmit');
                        if (current.text() != '') {
                            current.text(Omeka.messages.contribution.approved);
                        }
                    }
                }
            );
        }
    });
});
