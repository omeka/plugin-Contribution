jQuery.noConflict();

function setUpTableAppend(triggerSelector, targetSelector, newRow) {
    function getNewRow(index) {
        // The replace here allows us to dynamically add elements created by
        // Zend helpers. !!INDEX!! is replaced by, obviously, the index.
        return newRow.replace(/!!INDEX!!/g, index);
    }
    jQuery(document).ready(function() {
        var index = 0;
        jQuery(triggerSelector).click(function() {
            jQuery(targetSelector).append(getNewRow(index++));
            return false;
        });
    });
}

function setUpTableSorting(tableSelector, sectionSelector, orderInputSelector, dragHandle) {
    jQuery(document).ready(function() {
        var sortableSection = jQuery(sectionSelector);
        var sortableRows = sortableSection.children('tr');

        jQuery(tableSelector + ' > thead > tr').prepend('<th></th>');
        sortableRows.prepend('<td class="sorting-handle"><img src="' + dragHandle + '" /></td>');
        jQuery(orderInputSelector).hide();
        sortableSection.sortable({
            update: function(event, ui) {
                // We need to re-get the rows to see the new order.
                jQuery.each(sortableSection.children('tr'), function(index, element) {
                    var orderInput = jQuery(element).find(orderInputSelector + ' input');
                    orderInput.val(index + 1);
                    });
                }
        });
    });
}

function setUpSettingsWysiwyg() {
    jQuery(window).load(function() {
        var config = {
            theme: "advanced",
                    force_br_newlines : true,
                    forced_root_block : '', // Needed for 3.x
                    remove_linebreaks : true,
                    fix_content_duplication : false,
                    fix_list_elements : true,
                    valid_child_elements:"ul[li],ol[li]",
            theme_advanced_toolbar_location : "top",
            theme_advanced_buttons1 : "bold,italic,underline,justifyleft,justifycenter,justifyright,bullist,numlist,link,formatselect,code",
                    theme_advanced_buttons2 : "",
                    theme_advanced_buttons3 : "",
                    theme_advanced_toolbar_align : "left"
        };
        tinyMCE.init(config);
        tinyMCE.execCommand("mceAddControl", false, 'contribution_consent_text');
    });
}