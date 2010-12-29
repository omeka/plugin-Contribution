<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Dashboard'));
?>
<div id="primary">
    <?php echo flash(); ?>
    <?php contribution_check_captcha(); ?>
    <div id="getting-started">
    <h2>Getting Started</h2>
    <p>A basic contribution form is installed and ready to ask users to submit a Story or Image type, and to include their name and email address. If you want to modify the form, follow the steps below.</p>  
    <dl>
        <dt>1. Modify the contribution form:</dt>
        <dd>
            <ul style="width: 100%;">
                <li>Choose item types you wish visitors to share in <a href="<?php echo uri('contribution/types'); ?>">Contribution Types</a>.</li>
                <li>Pick the fields you want users to complete by editing each item type in <a href="<?php echo uri('contribution/types'); ?>">Contribution Types</a>.</li>
                <li>Create questions about contributors in <a href="<?php echo uri('contribution/contributor-metadata'); ?>">Contributor Questions</a>.</li>
            </ul>
        </dd>
        <dt>2. Configure settings for submitting contributions:</dt>
        <dd>
            <ul style="width: 100%;">
                <li>Set the terms of service for contributing to the site in <a href="<?php echo uri('contribution/settings'); ?>">Submission Settings</a>.</li>
                <li>Set up an auto-generated email to send to all contributors after they submit their contribution in <a href="<?php echo uri('contribution/settings'); ?>">Submission Settings</a>.</li>
                <li>Specify a collection for new contributed items in <a href="<?php echo uri('contribution/settings'); ?>">Submission Settings</a>.</li>
            </ul>
        </dd>
        <dt>3. Browse contributors' names, emails, and items in <a href="<?php echo uri('contribution/contributors'); ?>">Contributors</a>.</dt>
    </dl>
    </div>
</div>
<?php foot();
