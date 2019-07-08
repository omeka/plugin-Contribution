function setUpSettingsWysiwyg() {
    jQuery(window).load(function() {
        Omeka.wysiwyg({
           mode: "specific_textareas",
           editor_selector: "html-editor",
           forced_root_block: ""
        });
    });
}
