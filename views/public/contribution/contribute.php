<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$head = array('title' => 'Contribute',
              'bodyclass' => 'contribution');
head($head); ?>
<?php echo js('contribution-public-form'); ?>
<script type="text/javascript">
// <![CDATA[
enableContributionAjaxForm(<?php echo js_escape(uri('contribution/type-form')); ?>);
// ]]>
</script>

<div id="primary">
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>
    <form method="post" action="" enctype="multipart/form-data">
        <fieldset id="contribution-item-metadata">
            <div class="inputs">
                <label for="contribution-type">What type of item do you want to contribute?</label>
                <?php echo contribution_select_type(array( 'name' => 'contribution_type', 'id' => 'contribution-type'), $_POST['contribution_type']); ?>
                <input type="submit" name="submit-type" id="submit-type" value="Select" />
            </div>
            <div id="contribution-type-form">
            <?php if (isset($typeForm)): echo $typeForm; endif; ?>
            </div>
        </fieldset>
        <fieldset id="contribution-contributor-metadata" <?php if (!isset($typeForm)) { echo 'style="display: none;"'; }?>>
            <legend>Personal Information</legend>
            <div class="field">
                <label for="contributor-name">Name</label>
                <div class="inputs">
                    <div class="input">
                        <?php echo $this->formText('contributor-name', $_POST['contributor-name'], array('class' => 'textinput')); ?>
                    </div>
                </div>
            </div>
            <div class="field">
                <label for="contributor-email">Email Address</label>
                <div class="inputs">
                    <div class="input">
                        <?php echo $this->formText('contributor-email', $_POST['contributor-email'], array('class' => 'textinput')); ?>
                    </div>
                </div>
            </div>
        <?php
        foreach (contribution_get_contributor_fields() as $field) {
            echo $field;
        }
        ?>
        </fieldset>
        <fieldset id="contribution-confirm-submit" <?php if (!isset($typeForm)) { echo 'style="display: none;"'; }?>>
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
    </form>
</div>
<?php foot();
