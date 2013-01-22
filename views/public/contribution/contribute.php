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
<?php queue_js_file('contribution-public-form'); ?>
<script type="text/javascript">
// <![CDATA[
enableContributionAjaxForm(<?php echo js_escape(url(get_option('contribution_page_path').'/type-form')); ?>);
// ]]>
</script>

<div id="primary">
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>

    <?php if(!$user = current_user()) :?>
        <p>You must <a href='<?php echo url('guest-user/user/register'); ?>'>create an account</a> or <a href='<?php echo url('guest-user/user/login'); ?>'>log in</a> before contributing. You can still leave your identity to site visitors anonymous.</p>        
    <?php else: ?>
        <form method="post" action="" enctype="multipart/form-data">
            <fieldset id="contribution-item-metadata">
                <div class="inputs">
                    <label for="contribution-type">What type of item do you want to contribute?</label>
                    <?php $options = get_table_options('ContributionType' ); ?>
                    <?php $typeId = $type ? $type->id : '' ; ?>
                    <?php echo $this->formSelect( 'contribution_type', $typeId, array('multiple' => false, 'id' => 'contribution-type') , $options); ?>
                    <input type="submit" name="submit-type" id="submit-type" value="Select" />
                </div>
                <div id="contribution-type-form">
                <?php if (isset($typeForm)): echo $typeForm; endif; ?>
                </div>
            </fieldset>
            <?php if($user): ?>
                <p>You are logged in as: <?php echo metadata($user, 'name'); ?>
                <?php if(plugin_is_active('UserProfiles')) :?>
                <!-- Display UserProfile info -->
                <?php endif; ?>
            <?php endif; ?>
            <fieldset id="contribution-confirm-submit" <?php if (!isset($typeForm)) { echo 'style="display: none;"'; }?>>
                <?php if(isset($captchaScript)): ?><div id="captcha" class="inputs"><?php echo $captchaScript; ?></div><?php endif; ?>
                <div class="inputs">
                    <?php $public = isset($_POST['contribution-public']) ? $_POST['contribution-public'] : 0; ?>
                    <?php echo $this->formCheckbox('contribution-public', $public, null, array('1', '0')); ?>
                    <?php echo $this->formLabel('contribution-public', 'Publish my contribution on the web.'); ?>
                </div>
                <div class="inputs">
                    <?php $anonymous = isset($_POST['contribution-anonymous']) ? $_POST['contribution-anonymous'] : 0; ?>
                    <?php echo $this->formCheckbox('contribution-anonymous', $anonymous, null, array(1, 0)); ?>
                    <?php echo $this->formLabel('contribution-anonymous', "Don't publish my name."); ?>
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
    <?php endif; ?>
</div>
<?php echo foot();
