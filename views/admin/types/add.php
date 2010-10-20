<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$contributionType = $contributiontype;
contribution_admin_header(array('Types', 'Add a New Type'));
?>
<div id="primary">
    <?php echo flash(); ?>
<form method="post" action="">
    <fieldset>
        <legend>Type Metadata</legend>
        <div class="field">
            <?php echo $this->formLabel('item_type_id', 'Item Type'); ?>
            <div class="inputs">
                <?php echo contribution_select_item_type('item_type_id', $contributionType->item_type_id); ?>
                <p class="explanation">The Item Type, from your site's list of types, you would like to use.</p>
            </div>
        </div>
        <div class="field">
            <?php echo $this->formLabel('display_name', 'Display Name'); ?>
            <div class="inputs">
                <?php echo $this->formText('display_name', $contributionType->display_name, array('class' => 'textinput')); ?>
                <p class="explanation">The label you would like to use for this contribution type. If blank, the Item Type name will be used.</p>
            </div>
        </div>
        <div class="field">
            <?php echo $this->formLabel('file_permissions', 'Allow File Upload Via Form'); ?>
            <div class="inputs">
                <?php echo $this->formSelect('file_permissions', $contributionType->file_permissions, null, ContributionType::getPossibleFilePermissions()); ?>
                <p class="explanation">Enable or disable file uploads through the public contribution form. If set to &#8220;Required,&#8220; users must add a file to their contribution when selecting this item type.</p>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <input type="submit" class="form-submit" value="Save Changes" />
    </fieldset>
</form>
</div>
<?php foot();