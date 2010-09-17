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
            <?php echo contribution_select_type(array( 'name' => 'contribution_type', 'id' => 'contribution-type'), $_POST['contribution_type']); ?>
            <input type="submit" name="submit-type" id="submit-type" value="Select" />
        </div>
        <div id="contribution-type-form">
        <?php if (isset($typeForm)): echo $typeForm; endif; ?>
        </div>
        <div id="captcha-submit" <?php if (!isset($typeForm)) { echo 'style="display: none;"'; }?>>
            <fieldset>
            <div id="captcha" class="inputs"><?php echo $captchaScript; ?></div>
            <div class="inputs">
                <?php echo $this->formCheckbox('contribution-public', $_POST['contribution-public'], null, array('1', '0')); ?>
                <?php echo $this->formLabel('contribution-public', 'Publish my contribution on the web.'); ?>
            </div>
            <p>In order to contribute, you must read and agree to the <a href="<?php echo uri('contribution/terms') ?>" target="_blank">Terms and Conditions.</a></p>
            <div class="inputs">
                <?php echo $this->formCheckbox('terms-agree', $_POST['terms-agree'], null, array('1', '0')); ?>
                <?php echo $this->formLabel('terms-agree', 'I agree to the Terms and Conditions.'); ?>
            </div>
            <?php echo $this->formSubmit('form-submit', 'Contribute', array('class' => 'submitinput')); ?>
            </fieldset>
        </div>
    </form>
</div>
<?php foot();