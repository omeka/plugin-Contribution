function setUpSettingsWysiwyg() {
    jQuery(window).load(function() {
        Omeka.wysiwyg({
           selector: ".html-editor",
           forced_root_block: ""
        });
    });
}
