<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

queue_js_file('contribution-public-form');
queue_js_string('enableContributionAjaxForm("contribution/type-form");');

$head = array('title' => 'Contribute',
              'bodyclass' => 'contribution');
echo head($head); ?>

<div id="primary">
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>
    <form method="post" action="" enctype="multipart/form-data">
        <fieldset id="contribution-item-metadata">
            <div class="inputs">
                <label for="contribution-type">What type of item do you want to contribute?</label>
                <?php $options = get_table_options('ContributionType' ); ?>
                <?php $typeId = isset($type) ? $type->id : '' ; ?>
                <?php echo $this->formSelect( 'contribution_type', $typeId, array('multiple' => false, 'id' => 'contribution-type') , $options); ?>
                <input type="submit" name="submit-type" id="submit-type" value="Select" />
            </div>
            <div id="contribution-type-form">
            <?php if(isset($type)) { include('type-form.php'); }?>
            </div>
        </fieldset>
        <fieldset id="contribution-contributor-metadata" <?php if (!isset($type)) { echo 'style="display: none;"'; }?>>
            <legend>Personal Information</legend>
            <div class="field">
                <label for="contributor-name">Name</label>
                <div class="inputs">
                    <div class="input">
                    <?php $name = isset($_POST['contributor-name']) ? $_POST['contributor-name'] : ''; ?>
                        <?php echo $this->formText('contributor-name', $name, array('class' => 'textinput')); ?>
                    </div>
                </div>
            </div>
            <div class="field">
                <label for="contributor-email">Email Address</label>
                <div class="inputs">
                    <div class="input">
                    <?php $email = isset($_POST['contributor-email']) ? $_POST['contributor-email'] : ''; ?>
                        <?php echo $this->formText('contributor-email', $email, array('class' => 'textinput')); ?>
                    </div>
                </div>
            </div>
        <?php
        
        foreach (contribution_get_contributor_fields() as $field) {
            echo $field;
        }
        
        ?>
        </fieldset>
        <fieldset id="contribution-confirm-submit" <?php if (!isset($type)) { echo 'style="display: none;"'; }?>>
            <?php if(isset($captchaScript)): ?>
            <div id="captcha" class="inputs"><?php echo $captchaScript; ?></div>
            <?php endif; ?>
            <div class="inputs">
                <?php $public = isset($_POST['contribution-public']) ? $_POST['contribution-public'] : 0; ?>
                <?php echo $this->formCheckbox('contribution-public', $public, null, array('1', '0')); ?>
                <?php echo $this->formLabel('contribution-public', 'Publish my contribution on the web.'); ?>
            </div>
            <p>In order to contribute, you must read and agree to the <a href="<?php echo url('contribution/terms') ?>" target="_blank">Terms and Conditions.</a></p>
            <div class="inputs">
                <?php $agree = isset( $_POST['terms-agree']) ?  $_POST['terms-agree'] : 0 ?>
                <?php echo $this->formCheckbox('terms-agree', $agree, null, array('1', '0')); ?>
                <?php echo $this->formLabel('terms-agree', 'I agree to the Terms and Conditions.'); ?>
            </div>
            <?php echo $this->formSubmit('form-submit', 'Contribute', array('class' => 'submitinput')); ?>
        </fieldset>
    </form>
</div>
<?php echo foot();
