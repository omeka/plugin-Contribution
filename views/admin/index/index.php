<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Getting Started'));
?>

<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <div id="getting-started">
    <h2>Getting Started</h2>
    <p>A basic contribution form is installed and ready to ask users to submit a Story or Image type, and to include their name and email address. If you want to modify the form, follow the steps below.</p>
    <p>While an account exists for all contributors, they can make any contribution anonymously. Only the admin and super roles can see the full information.</p>
    <p>Contributors can make any contribution public or not. Non-public contributions cannot be made publicly available on the site, but they are visible to the super, admin, contributor, and researcher roles, as well as to the contributors themselves.</p>  
    <dl>
        <dt>1. Set up Guest User information:</dt>
        <dd>
            <p>To make repeat contributions easier, a reusable "Guest User" account is created for contributors. <a href="<?php echo url('plugins/config?name=GuestUser'); ?>">Configure Guest Users</a>, with the following suggestions.</p>
            <ul>            
            <li>Let visitors know a bit about how their contributions will be used and why they are so valuable in the "Registration Features" information.</li>
            <li>It is easiest to contribute if administrator approval is not required and you allow 20 minute instant access. To prevent spam, using ReCaptcha is recommended. </li>
            <li>Additional contribution-specific information for guest users can be created here.</li>
            </ul>
        </dd>
    
        <dt>2. Modify the contribution form:</dt>
        <dd>
            <ul>
                <li>Choose item types you wish visitors to share, and customize the fields they should use, in <a href="<?php echo url('contribution/types'); ?>">Contribution Types</a>.</li>
                <?php if(plugin_is_active('UserProfiles')):?>
                <li>Set up profile information you would like from your contributors by setting up a <a href="<?php echo url('user-profiles');?>">user profiles type</a></li>
                <?php else:?>
                <li>The optional User Profiles plugin lets you set up additional information you would like to ask from your contributors. To use those features, please install that, then return here for additional guidance.</li>
                <?php endif; ?>
            </ul>
        </dd>
        <dt>3. Configure the <a href="<?php echo url('contribution/settings'); ?>">submission settings</a> for contributions:</dt>
        <dd>
            <ul>
                <li>Set the terms of service for contributing to the site.</li>
                <li>Set up an auto-generated email to send to all contributors after they submit their contribution.</li>
                <li>Decide whether to use the 'Simple' options. This requires only that contributors provide an email address.</li>
                <li>Specify a collection for new contributed items.</li>
            </ul>
        </dd>
        <dt>4. Browse contributions and their status, with links to more contributor information, in <a href="<?php echo url('contribution/items'); ?>">Contributions</a>.</dt>
    </dl>
    </div>
</div>
<?php echo foot(); ?>
