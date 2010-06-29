jQuery.noConflict();
function enableContributionAjaxForm(url) {
    jQuery(document).ready(function() {
        jQuery('#submit-type').remove();
        jQuery('#captcha-submit').hide();
        jQuery('#contribution-type').change(function () {
            var form = jQuery('#contribution-type-form');
            var submit = jQuery('#captcha-submit');
            var value = this.value;
            submit.hide();
            form.slideUp(400, function() { 
                form.empty(); 
                if (value != "") {
                    form.hide();
                    jQuery.post(url, {contribution_type: value}, function(data) {
                       form.append(data); 
                       form.slideDown(400, function() {
                           submit.show();
                       });
                    });
                }
            });
        });
    });
}