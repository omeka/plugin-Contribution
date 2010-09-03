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
        jQuery('#form-submit').click(function (event) {
            var name = jQuery('#contributor-name').val();
            var email = jQuery('#contributor-email').val();
            var terms = jQuery('#terms-agree').val();
            var emailPattern = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i;

            if (name == "" || email == "") {
                alert('Please provide your name and email address.');
                return false;
            }
            if (!emailPattern.test(email)) {
                alert('The email you provided was invalid. Please provide another.');
                return false;
            }
            if (terms != 'on') {
                alert('You must agree to the Terms and Conditions to contribute.');
                return false;
            }
        });
    });
}