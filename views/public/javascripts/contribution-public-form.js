function enableContributionAjaxForm(url) {
    jQuery(document).ready(function() {
        var form = jQuery('#contribution-type-form');
        var contributionType = jQuery('#contribution-type');
        var submit = jQuery('#contribution-confirm-submit');
        var contributorMetadata = jQuery('#contribution-contributor-metadata');

        jQuery('#submit-type').remove();
        
        contributionType.change(function () {
            var value = this.value;
            submit.hide();
            contributorMetadata.hide();
            form.hide(400, function() {
                form.empty();
                if (value != "") {
                    jQuery.post(url, {contribution_type: value}, function(data) {
                       form.append(data); 
                       form.show(400, function() {
                           form.trigger('contribution-form-shown');
                           submit.show();
                           contributorMetadata.show();
                       });
                    });
                }
            });
        });
        jQuery('#form-submit').click(function (event) {
            var name = jQuery('#contributor-name').val();
            var email = jQuery('#contributor-email').val();
            var terms = jQuery('#terms-agree').attr('checked');
            var emailPattern = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i;

            if (name == "" || email == "") {
                alert('Please provide your name and email address.');
                return false;
            }
            if (!emailPattern.test(email)) {
                alert('The email you provided was invalid. Please provide another.');
                return false;
            }
            if (!terms) {
                alert('You must agree to the Terms and Conditions to contribute.');
                return false;
            }
            return true;
        });
    });
}