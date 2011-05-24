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

function setUpTableSorting(sectionSelector, orderInputSelector) {
    jQuery(document).ready(function() {
        var sortableSection = jQuery(sectionSelector);
        var sortableRows = sortableSection.children('li');

        sortableRows.prepend('<span class="sorting-handle ui-icon ui-icon-arrowthick-2-n-s"></span>');
        jQuery(orderInputSelector).hide();
        sortableSection.sortable({
            update: function(event, ui) {
                // We need to re-get the rows to see the new order.
                jQuery(sortableSection).find(orderInputSelector).each(function(index) {
                    jQuery(this).val(index + 1);
                });
            },
            handle: '.sorting-handle',
            axis: 'y',
            containment: 'parent',
            tolerance: 'pointer',
            items: '.element-row',
            placeholder: 'ui-state-highlight',
            forcePlaceholderSize: true,
            opacity: 0.6
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
