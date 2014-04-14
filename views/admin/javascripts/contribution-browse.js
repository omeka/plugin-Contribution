if (!Omeka) {
    var Omeka = {};
}

Omeka.ContributionBrowse = {};

(function ($) {
    Omeka.ContributionBrowse.setupBatchEdit = function () {
        var contributionCheckboxes = $("table#contributions tbody input[type=checkbox]");
        var globalCheckbox = $('th.batch-edit-heading').html('<input type="checkbox">').find('input');
        var batchEditSubmit = $('.batch-edit-option input');
        /**
         * Disable the batch submit button first, will be enabled once
         * checkboxes are checked.
         */
        batchEditSubmit.prop('disabled', true);

        /**
         * Check all the checkboxes if the globalCheckbox is checked.
         */
        globalCheckbox.change(function() {
            contributionCheckboxes.prop('checked', !!this.checked);
            checkBatchEditSubmitButton();
        });

        /**
         * Uncheck the global checkbox if any of the checkboxes are unchecked.
         */
        contributionCheckboxes.change(function(){
            if (!this.checked) {
                globalCheckbox.prop('checked', false);
            }
            checkBatchEditSubmitButton();
        });

        /**
         * Check whether the batchEditSubmit button should be enabled.
         * If any of the checkboxes is checked, the batchEditSubmit button is
         * enabled.
         */
        function checkBatchEditSubmitButton() {
            var checked = false;
            contributionCheckboxes.each(function() {
                if (this.checked) {
                    checked = true;
                    return false;
                }
            });

            batchEditSubmit.prop('disabled', !checked);
        }
    };
})(jQuery);

jQuery(document).ready(function() {
    // Approve (make public) from any status.
    jQuery('input[name="submit-batch-approve"]').click(function(event) {
        event.preventDefault();
        jQuery('table#contributions thead tr th.batch-edit-heading input').attr('checked', false);
        jQuery('.batch-edit-option input').prop('disabled', true);
        jQuery('table#contributions tbody input[type=checkbox]:checked').each(function(){
            var checkbox = jQuery(this);
            var current = jQuery('#contribution-' + this.value);
            var ajaxUrl = current.attr('href') + '/contribution/ajax/update';
            current.addClass('transmit');
            jQuery.post(ajaxUrl,
                {
                    status: 'approved',
                    id: this.value
                },
                function(data) {
                    if (data == 'private') {
                        current.removeClass('transmit');
                    } else {
                        checkbox.attr('checked', false);
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
        });
    });

    // Proposed (make needs review) from any status.
    jQuery('input[name="submit-batch-proposed"]').click(function(event) {
        event.preventDefault();
        jQuery('table#contributions thead tr th.batch-edit-heading input').attr('checked', false);
        jQuery('.batch-edit-option input').prop('disabled', true);
        jQuery('table#contributions tbody input[type=checkbox]:checked').each(function(){
            var checkbox = jQuery(this);
            var current = jQuery('#contribution-' + this.value);
            var ajaxUrl = current.attr('href') + '/contribution/ajax/update';
            current.addClass('transmit');
            jQuery.post(ajaxUrl,
                {
                    status: 'proposed',
                    id: this.value
                },
                function(data) {
                    checkbox.attr('checked', false);
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
        });
    });

    // Delete a contribution.
    jQuery('input[name="submit-batch-delete"]').click(function(event) {
        event.preventDefault();
        if (!confirm(Omeka.messages.contribution.confirmation)) {
            return;
        }
        jQuery('table#contributions thead tr th.batch-edit-heading input').attr('checked', false);
        jQuery('.batch-edit-option input').prop('disabled', true);
        jQuery('table#contributions tbody input[type=checkbox]:checked').each(function(){
            var checkbox = jQuery(this);
            var row = jQuery(this).closest('tr.contribution');
            var current = jQuery('#contribution-' + this.value);
            var ajaxUrl = current.attr('href') + '/contribution/ajax/delete';
            checkbox.addClass('transmit');
            jQuery.post(ajaxUrl,
                {
                    id: this.value
                },
                function(data) {
                    checkbox.attr('checked', false);
                    row.remove();
                }
            );
        });
    });
});
