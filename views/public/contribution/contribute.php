<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$head = array('title' => 'Contribute',
              'bodyClass' => 'contribution primary');
head(array('title' => $head['title'])); ?>
<?php echo js('jquery'); ?>
<?php echo js('contribution-public-form'); ?>
<script type="text/javascript">
enableContributionAjaxForm(<?php echo js_escape(uri('contribution/type-form')); ?>);
</script>
<style type="text/css">
textarea {
    height: auto;
}

#captcha textarea {
    float: none;
    width: auto;
}
</style>

<div id="primary">
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="inputs">
            <label for="contribution_type">What type of item do you want to contribute?</label>
            <?php echo contribution_select_type(array( 'name' => 'contribution_type', 'id' => 'contribution-type')); ?>
            <input type="submit" name="submit-type" id="submit-type" value="Select" />
        </div>
        <div id="contribution-type-form">
        <?php if (isset($typeForm)): echo $typeForm; endif; ?>
        </div>
        <div id="captcha-submit" <?php if (!isset($typeForm)) { echo 'style="display: none;"'; }?>>
            <fieldset>
            <div id="captcha" class="inputs"><?php echo $captchaScript; ?></div>
            <p>In order to contribute, you must read and agree to the <a href="<?php echo uri('contribution/terms') ?>" target="_blank">Terms and Conditions.</a></p>
            <div class="inputs">
                <input type="checkbox" name="terms-agree" id="terms-agree" />
                <label for="terms-agree">I agree to the Terms and Conditions.</label>
            </div>
            <input type="submit" class="submitinput" name="form-submit" id="form-submit" value="Contribute" />
            </fieldset>
        </div>
    </form>
</div>
<?php foot();