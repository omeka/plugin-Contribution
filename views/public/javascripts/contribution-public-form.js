function enableContributionAjaxForm(url) {
    jQuery(document).ready(function() {
        // Div that will contain the AJAX'ed form.
        var form = jQuery('#contribution-type-form');
        // Select element that controls the AJAX form.
        var contributionType = jQuery('#contribution-type');
        // Elements that should be hidden when there is no type form on the page.
        var elementsToHide = jQuery('#contribution-confirm-submit, #contribution-contributor-metadata');
        // Duration of hide/show animation.
        var duration = 400;

        // Remove the noscript-fallback type submit button.
        jQuery('#submit-type').remove();

        // When the select is changed, AJAX in the type form
        contributionType.change(function () {
            var value = this.value;
            elementsToHide.hide();
            form.hide(duration, function() {
                form.empty();
                if (value != "") {
                    jQuery.post(url, {contribution_type: value}, function(data) {
                       form.append(data); 
                       form.show(duration, function() {
                           form.trigger('contribution-form-shown');
                           elementsToHide.show();
                       });
                    });
                }
            });
        });

        // Do some quick-and-dirty validation of some of the required inputs.
        // TODO: replace alerts with a better notification method.
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
