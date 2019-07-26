if (!Omeka) {
    var Omeka = {};
}

// From admin/themes/default/javascripts/items.js.
if (typeof Omeka === 'undefined') {
    Omeka = {};
}

Omeka.Items = {};

(function ($) {
    /**
     * Enable drag and drop sorting for files.
     */
    Omeka.Items.enableSorting = function () {
        $('.sortable').sortable({
            items: 'li.file',
            forcePlaceholderSize: true,
            forceHelperSize: true,
            revert: 200,
            placeholder: "ui-sortable-highlight",
            containment: 'document',
            update: function (event, ui) {
                $(this).find('.file-order').each(function (index) {
                    $(this).val(index + 1);
                });
            }
        });
        $( ".sortable" ).disableSelection();

        $( ".sortable input[type=checkbox]" ).each(function () {
            $(this).css("display", "none");
        });
    };

    /**
     * Allow adding an arbitrary number of file input elements to the items form so that
     * more than one file can be uploaded at once.
     *
     * @param {string} label
     */
    Omeka.Items.enableAddFiles = function (label) {
        var filesDiv = $('#files-metadata .files');

        var link = $('<a href="#" id="add-file" class="add-file button">' + label + '</a>');
        link.click(function (event) {
            event.preventDefault();
            var inputs = filesDiv.find('input');
            var inputCount = inputs.length;
            var fileHtml = '<input name="file[' + inputCount + ']" type="file" class="fileinput button"></div>';
            $(fileHtml).insertAfter(inputs.last()).hide().slideDown(200, function () {
                // Extra show fixes IE bug.
                $(this).show();
            });
        });

        $('#upload-files').after(link);
    };
})(jQuery);

function toggleProfileEdit() {
    jQuery('div.contribution-userprofile').toggle();
    jQuery('span.contribution-userprofile-visibility').toggle();
}

function enableContributionAjaxForm(url) {
    jQuery(document).ready(function() {
        // Div that will contain the AJAX'ed form.
        var form = jQuery('#contribution-type-form');
        // Select element that controls the AJAX form.
        var contributionType = jQuery('#contribution-type');
        // Elements that should be hidden when there is no type form on the page.
        var elementsToHide = jQuery('#contribution-confirm-submit, #contribution-contributor-metadata');
        // Duration of hide/show animation.
        var duration = 0;

        // Remove the noscript-fallback type submit button.
        jQuery('#submit-type').remove();

        // When the select is changed, AJAX in the type form
        contributionType.change(function () {
            var value = this.value;
            elementsToHide.hide();
            form.hide(duration, function() {
                form.empty();
                if (value != "") {
                    jQuery.post(url, {contribution_type: value}, function(data) {
                       form.append(data);
                       form.show(duration, function() {
                           form.trigger('contribution-form-shown');
                           form.trigger('omeka:tabselected');
                           elementsToHide.show();
                           //in case profile info is also being added, do the js for that form
                           jQuery(form).trigger('omeka:elementformload');
                           jQuery('.contribution-userprofile-visibility').click(toggleProfileEdit);
                       });
                    });
                }
            });
        });
    });
}

jQuery(document).ready(function() {
    jQuery('.contribution-userprofile-visibility').click(toggleProfileEdit);
    var form = jQuery('#contribution-type-form');
    jQuery(form).trigger('omeka:elementformload');
});
