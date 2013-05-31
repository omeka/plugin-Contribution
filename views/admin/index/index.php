<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array(__('Getting Started')));
?>

<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <div id="getting-started">
    <h2><?php echo __("Getting Started"); ?></h2>
    <p><?php echo __("A basic contribution form is installed and ready to ask users to submit a Story or Image type, and to include their name and email address. If you want to modify the form, follow the steps below."); ?></p>
    <p><?php echo __("While an account exists for all contributors, they can make any contribution anonymously. Only the admin and super roles can see the full information."); ?></p>
    <p><?php echo __("Contributors can make any contribution public or not. Non-public contributions cannot be made publicly available on the site, but they are visible to the super, admin, contributor, and researcher roles, as well as to the contributors themselves."); ?></p>  
    <dl>
        <dt><?php echo __("1. Set up Guest User information:"); ?></dt>
        <dd>
            <p><?php echo __("To make repeat contributions easier, a reusable 'Guest User' account is created for contributors.") ?>  <a href="<?php echo url('plugins/config?name=GuestUser'); ?>"><?php echo __("Configure Guest Users"); ?></a>, <?php echo __("with the following suggestions."); ?></p>
            <ul>            
            <li><?php echo __("Let visitors know a bit about how their contributions will be used and why they are so valuable in the 'Registration Features' information."); ?></li>
            <li><?php echo __("It is easiest to contribute if administrator approval is not required and you allow 20 minute instant access. To prevent spam, using ReCaptcha is recommended."); ?></li>
            <li><?php echo __("Additional contribution-specific information for guest users can be created here."); ?></li>
            </ul>
        </dd>
    
        <dt><?php echo __("2. Modify the contribution form:"); ?></dt>
        <dd>
            <ul>
                <li><?php echo __("Choose item types you wish visitors to share, and customize the fields they should use, in %s", "<a href='" . url('contribution/types') . "'>" . __("Contribution Types") . ".</a>"); ?></li>
                <?php if(plugin_is_active('UserProfiles')):?>
                <li><?php echo __("Set up profile information you would like from your contributors by setting up a %s ", "<a href='" . url('user-profiles') . "'>" . __('user profiles type') . "</a>"); ?> </li>
                <?php else:?>
                <li><?php echo __("The optional User Profiles plugin lets you set up additional information you would like to ask from your contributors. To use those features, please install that, then return here for additional guidance.");?></li>
                <?php endif; ?>
            </ul>
        </dd>
        <dt><?php echo __("3. Configure the %s for contributions:", "<a href='" . url('contribution/settings') . "'>" . __('submission settings') . "</a>"); ?></dt>
        <dd>
            <ul>
                <li><?php echo __("Set the terms of service for contributing to the site."); ?></li>
                <li><?php echo __("Set up an auto-generated email to send to all contributors after they submit their contribution."); ?></li>
                <li><?php echo __("Decide whether to use the 'Simple' options. This requires only that contributors provide an email address."); ?></li>
                <li><?php echo __("Specify a collection for new contributed items."); ?></li>
            </ul>
        </dd>
        <dt><?php echo __("4. Browse contributions and their status, with links to more contributor information, in %s", "<a href='" . url('contribution/items'). "'>" . __('Contributions') . "</a>"); ?></dt>
    </dl>
    </div>
</div>
<?php echo foot(); ?>
