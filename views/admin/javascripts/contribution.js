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
        Omeka.wysiwyg({
           mode: "specific_textareas",
           editor_selector: "html-editor",
           forced_root_block: ""
        });
    });
}