<fieldset id="contribution-confirm-submit" <?php if (empty($type)) { echo 'style="display: none;"'; }?>>
    <?php if (!empty($captchaScript)): ?>
        <div id="captcha" class="inputs"><?php echo $captchaScript; ?></div>
    <?php endif; ?>
    <div class="inputs">
        <?php $public = isset($_POST['contribution-public']) ? $_POST['contribution-public'] : ($process == 'add' ? 0 : $contribution_contributed_item->public); ?>
        <?php echo $this->formCheckbox('contribution-public', $public, null, array('1', '0')); ?>
        <?php echo $this->formLabel('contribution-public', __('Publish my contribution on the web.')); ?>
    </div>
    <div class="inputs">
        <?php $anonymous = isset($_POST['contribution-anonymous']) ? $_POST['contribution-anonymous'] : ($process == 'add' ? 0 : $contribution_contributed_item->anonymous); ?>
        <?php echo $this->formCheckbox('contribution-anonymous', $anonymous, null, array(1, 0)); ?>
        <?php echo $this->formLabel('contribution-anonymous', __("Keep identity private.")); ?>
    </div>
    <p><?php echo __("In order to contribute, you must read and agree to the %s",  "<a href='" . contribution_contribute_url('terms') . "' target='_blank'>" . __('Terms and Conditions') . ".</a>"); ?></p>
    <div class="inputs">
        <?php $agree = isset( $_POST['terms-agree']) ? $_POST['terms-agree'] : ($process == 'add' ? 0 : 1); ?>
        <?php echo $this->formCheckbox('terms-agree', $agree, null, array('1', '0')); ?>
        <?php echo $this->formLabel('terms-agree', __('I agree to the Terms and Conditions.')); ?>
    </div>
    <?php echo $this->formSubmit('form-submit', __('Contribute'), array('class' => 'submitinput')); ?>
</fieldset>
