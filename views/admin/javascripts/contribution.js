jQuery.noConflict();

function setUpElementForm(dragHandle, elementSelect) {
    function getNewElementRow(index) {
        var promptElement = '<input name="newElements[' + index + '][prompt]" class="textinput" />';
        elementSelectOutput = elementSelect.replace(/REPLACE/g, index);
        return '<tr><td></td><td class="element-prompt">' + promptElement + '</td><td colspan="6">' + elementSelectOutput + '</td></tr>';
    }

    var index = 0;

    jQuery(document).ready(function() {
        jQuery('#add-element').click(function() {
            jQuery('#add-element-row').before(getNewElementRow(index++));
            return false;
        });

        var sortableSection = jQuery('#sortable');
        var sortableRows = sortableSection.children('tr');

        jQuery('#element-table > thead > tr').prepend('<th></th>');
        sortableRows.prepend('<td class="sorting-handle"><img src="' + dragHandle + '" /></td>');
        jQuery('.element-order').hide();
        sortableSection.sortable({
            update: function(event, ui) {
                // We need to re-get the rows to see the new order.
                jQuery.each(sortableSection.children('tr'), function(index, element) {
                    var orderInput = jQuery(element).find('.element-order input');
                    orderInput.val(index + 1);
                    });
                }
        });
    });
}

function setUpTypesForm(typeSelect) {
    function getNewTypeRow(index) {
        var displayNameInput = '<input type="text" class="textinput" name="newTypes[' + index + '][display_name]"/>';
        typeSelectDisplay = typeSelect.replace(/REPLACE/g, index);
        return '<tr><td>' + displayNameInput + '</td><td colspan="3">' + typeSelectDisplay + '</td></tr>';
    }
    jQuery(document).ready(function() {
        var index = 0;
        jQuery('#add-type').click(function(event) {
            jQuery('#types-table-body').append(getNewTypeRow(index++));
            return false;
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