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