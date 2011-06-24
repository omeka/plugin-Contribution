<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$contributionType = $contributiontype;
$contributionTypeElements = $contributionType->ContributionTypeElements;
$itemType = $contributionType->ItemType;
$elements = $itemType->Elements;

$typeName = html_escape($contributionType->display_name);
queue_css('contribution-type-form');
contribution_admin_header(array('Types', "Edit &ldquo;$typeName&rdquo;"));
?>
<?php echo delete_button(null, 'delete-type', 'Delete this Type', array(), 'delete-record-form'); ?>
<div id="primary">
    <?php echo flash(); ?>
<form method="post" action="">
    <fieldset>
        <legend>Type Metadata</legend>
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
        <legend>Contributed Elements</legend>
        <div id="elements-header">
            <span class="element-prompt">Prompt</span>
            <span class="element-name">Element</span>
            <span class="element-set-name">Set</span>
            <span class="element-order">Order</span>
            <span class="element-delete">Delete?</span>
        </div>
        <ol id="elements">
        <?php foreach ($contributionTypeElements as $element):
    $id = $element->id; ?>
            <li class="element-row">
                <?php echo $this->formText("Elements[$id][prompt]", $element->prompt, array('class' => 'textinput element-prompt')); ?>
            <?php if (($realElement = $element->Element)): ?>
                <span class="element-name"><?php echo html_escape($realElement->name); ?></span>
                <span class="element-set-name"><?php echo html_escape($realElement->getElementSet()->name); ?></span>
            <?php else: ?>
                <span class="element-missing">This element has been deleted or no longer exists.</span>
            <?php endif; ?>
                <?php echo $this->formText("Elements[$id][order]", $element->order, array('class' => 'textinput element-order')); ?>
                <?php echo $this->formCheckbox("Elements[$id][delete]", null, array('checked' => false))?></span>
            </li>
        <?php endforeach; ?>
        </ol>
        <input type="submit" class="add-element" id="add-element" value="Add an Element" />
    </fieldset>

    <fieldset>
        <input type="submit" class="form-submit" value="Save Changes" />
    </fieldset>
</form>
</div>
<?php
echo js('contribution');
?>
<script type="text/javascript">
// <![CDATA[
    var newRow = <?php
        $promptInput = $this->formText('newElements[!!INDEX!!][prompt]', null, array('class' => 'textinput element-prompt'));
        $elementSelect = contribution_select_element_for_type($contributionType, 'newElements[!!INDEX!!][element_id]');
        echo js_escape("<li>$promptInput $elementSelect</li>");
        ?>;
    setUpTableSorting('#elements', '.element-order');
    setUpTableAppend('#add-element', '#elements', newRow);
// ]]>
</script>
<?php foot();
