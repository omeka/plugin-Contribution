<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$head = array('title' => 'Contribute',
              'bodyClass' => 'contribution primary');
head(array('title' => $head['title'])); ?>
<?php echo js('jquery'); ?>
<script type="text/javascript">
jQuery.noConflict();

function displayTypeForm() {
    var form = jQuery('#contribution-type-form');
    var submit = jQuery('#captcha-submit');
    var value = this.value;
    submit.hide();
    form.slideUp(400, function() { 
        form.empty(); 
        if (value != "") {
            form.hide();
            jQuery.post('type-form', {contribution_type: value}, function(data) {
               form.append(data); 
               form.slideDown(400, function() {
                   submit.show();
               });
            });
        }
    });
}

jQuery(document).ready(function() {
    jQuery('#submit-type').remove();
    jQuery('#captcha-submit').hide();
    jQuery('#contribution-type').change(displayTypeForm);
});
</script>
<style type="text/css">
#captcha textarea {
    float: none;
    height: auto;
    width: auto;
}
</style>

<div id="primary">
    <h1><?php echo $head['title']; ?></h1>
    <form method="POST">
        <div class="inputs">
            <label for="contribution_type">What type of item do you want to contribute?</label>
            <?php echo contribution_select_type(array( 'name' => 'contribution_type', 'id' => 'contribution-type')); ?>
            <input type="submit" name="submit-type" id="submit-type" value="Select" />
        </div>
        <div id="contribution-type-form">
        <?php if (isset($typeForm)): echo $typeForm; endif; ?>
        </div>
        <div id="captcha-submit" <?php if (!isset($typeForm)) { echo 'style="display: none;"'; }?>>
            <div id="captcha"><?php echo $captchaScript; ?></div>
            <input type="submit" name="form-submit" id="form-submit" value="Contribute" />
        </div>
    </form>
</div>
<?php foot();