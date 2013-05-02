
<div class="field">
    <div class="two columns alpha">
        <label><?php echo __("Notify people about new submissions"); ?></label>    
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Enter email addresses of people to be notified when new contributions are made, separated by commas."); ?></p>
        <div class="input-block">
        <textarea name='contribution_email_recipients' ><?php echo get_option("contribution_email_recipients"); ?></textarea>        
        </div>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __("Use 'simple' options"); ?></label>    
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("This will require an email address from contributors, and create a guest user from that information. If those users want to use the account, they will have to request a new password for the account. If you want to collect additional information about contributors, you cannot use the simple option. See documentation for details. "); ?></p>
        <div class="input-block">
        <input type='checkbox' name='contribution_simple' <?php if(get_option("contribution_simple")) {echo "checked='checked'";} ?> />        
        </div>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __("Email text to send to contributors with 'simple'"); ?></label>    
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("We recommend that you notify contributors that a guest user account has been created for them, and what they gain by confirming their account."); ?></p>
        <div class="input-block">
        <textarea name='contribution_simple_email'><?php echo get_option('contribution_simple_email');?></textarea>        
        </div>
    </div>
</div>


