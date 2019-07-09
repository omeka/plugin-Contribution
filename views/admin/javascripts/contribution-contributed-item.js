jQuery(document).ready(function() {
    // Handle approved / proposed from any status.
    //SB handle also reject
    var currentid = null;
    jQuery('.contribution.reject').click(function(event){
        event.preventDefault();
        var id = jQuery(this).attr('id');
        var current = jQuery('#' + id);
        currentid = id.substr(id.lastIndexOf('-') + 1);

        jQuery('#reject-overlay').show();
    });
    jQuery('#reject-button').click(function(event){
        event.preventDefault();
        var rejectid = jQuery("#popup-select").val();
        var current = jQuery('#contribution-' + currentid);
        var ajaxUrl = current.attr('href') + '/contribution/ajax/update';
        current.addClass('transmit');
		jQuery.post(ajaxUrl,
			{
				status: 'rejected',
				id: currentid,
				reject: rejectid
			},
			function(data) {
				if (data == 'private') {
					current.removeClass('transmit');
				} else {
					current.addClass('rejected');
					current.removeClass('proposed');
					current.removeClass('approved');
					current.removeClass('private');
					current.removeClass('transmit');
					jQuery('#contribution-reject-' + currentid).hide();
					if (current.text() != '') {
						current.text(Omeka.messages.contribution.rejected);
					}
				}
			}
		);
        
        jQuery('#reject-overlay').hide();
    });
    jQuery('#cancel-button').click(function(event){
    	currentid = null;
        event.preventDefault();
        jQuery('#reject-overlay').hide();
    });
    
    jQuery('.contribution.toggle-status').click(function(event) {
        event.preventDefault();
        var id = jQuery(this).attr('id');
        var current = jQuery('#' + id);
        id = id.substr(id.lastIndexOf('-') + 1);
        var ajaxUrl = jQuery(this).attr('href') + '/contribution/ajax/update';
        jQuery(this).addClass('transmit');
        if(jQuery(this).hasClass('proposed')){
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
                        jQuery('#contribution-reject-' + id).hide();
                        if (current.text() != '') {
                            current.text(Omeka.messages.contribution.approved);
                        }
                    }
                }
            );
        } else{
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
                        jQuery('#contribution-reject-' + id).show();
                        if (current.text() != '') {
                            current.text(Omeka.messages.contribution.proposed);
                        }
                    }
                }
            );
        }
    });
});
