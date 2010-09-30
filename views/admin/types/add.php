<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$contributionType = $contributiontype;
contribution_admin_header(array('Types', 'Add'));
?>
<div id="primary">
    <?php echo flash(); ?>
<form method="post" action="">
    <fieldset>
        <legend>Type Metadata</legend>
        <div class="field">
            <?php echo $this->formLabel('display_name', 'Display Name'); ?>
            <div class="input">
                <?php echo $this->formText('display_name', $contributionType->display_name, array('class' => 'textinput')); ?>
            </div>
        </div>
        <div class="field">
            <?php echo $this->formLabel('file_permissions', 'File Permissions'); ?>
            <div class="input">
                <?php echo $this->formSelect('file_permissions', $contributionType->file_permissions, null, ContributionType::getPossibleFilePermissions()); ?>
            </div>
        </div>
        <div class="field">
            <?php echo $this->formLabel('item_type_id', 'Item Type'); ?>
            <div class="input">
                <?php echo contribution_select_item_type('item_type_id', $contributionType->item_type_id); ?>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <input type="submit" class="form-submit" value="Save Changes" />
    </fieldset>
</form>
</div>
<?php foot();