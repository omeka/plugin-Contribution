<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$h1 = 'Contribution';
$h2 = 'Types';
$h3 = "&ldquo;$contributionType->display_name&rdquo;";
$head = array('title' => "$h1 | $h2 | $h3",
              'bodyclass' => 'contribution primary',
              'content_class' => 'horizontal-nav');
head($head);
echo js('jquery');
echo js('jquery-ui');
?>
<style type="text/css">
    table input.textinput
    {
        font-size: 1.0em;
    }

    td.element-prompt
    {
        width: 50%;
    }

    td.element-prompt input
    {
        width: 100%;
    }
}
</style>
<script type="text/javascript">
    function getNewElementRow(index) {
        var promptElement = '<input name="newElements[' + index + '][prompt]" class="textinput" />';
        var selectElement = <?php echo js_escape(contribution_select_element_for_type($contributionType, 'newElements[REPLACE][element_id]')); ?>;
        selectElement = selectElement.replace(/REPLACE/g, index);
        return '<tr><td></td><td class="element-prompt">' + promptElement + '</td><td colspan="6">' + selectElement + '</td></tr>';
    }

    jQuery.noConflict();
    var index = 0;
    jQuery(document).ready(function() {
        jQuery('#add-element').click(function() {
            jQuery('#add-element-row').before(getNewElementRow(index++));
            return false;
        });

        var sortableSection = jQuery('#sortable');
        var sortableRows = sortableSection.children('tr');

        jQuery('#element-table > thead > tr').prepend('<th></th>');
        sortableRows.prepend('<td class="sorting-handle"><img src="/omeka/admin/themes/default/images/arrow_move.gif" /></td>');
        jQuery('.element-order').hide();
        sortableSection.sortable({
            update: function(event, ui) {
                // We need to re-get the rows to see the new order.
                jQuery.each(sortableSection.children('tr'), function(index, element) {
                    var orderInput = jQuery(element).find('.element-order input');
                    orderInput.val(index + 1);
                })
        }});
    });
</script>
<h1><a href="<?php echo uri('contribution'); ?>"><?php echo $h1; ?></a> | <a href="<?php echo uri('contribution/types'); ?>"><?php echo $h2; ?></a> | <?php echo $h3; ?></h1>
<ul id="section-nav" class="navigation">
<?php echo nav(array('Start' => uri('contribution/index'), 'Settings' => uri('contribution/settings'), 'Types' => uri('contribution/types'))); ?>
</ul>
<div id="primary">
    <?php echo flash(); ?>
<form method="POST">
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
    </fieldset>
    <fieldset>
        <legend>Contributed Elements</legend>
        <table id ="element-table">
            <thead>
                <tr>
                    <th>Prompt</th>
                    <th>Element</th>
                    <th>Set</th>
                    <th class="element-order">Order</th>
                    <th>Delete?</th>
                </tr>
            </thead>
            <tfoot>
                <tr id="add-element-row">
                    <td colspan="6"><input type="submit" class="add-element" id="add-element" value="Add an Element" /></td>
                </tr>
            </tfoot>
            <tbody id="sortable">
    <?php foreach ($contributionTypeElements as $element):
    $id = $element->id; ?>
                <tr class="element-row">
                    <td class="element-prompt"><?php echo $this->formText("Elements[$id][prompt]", $element->prompt, array('class' => 'textinput')); ?></td>
                    <td><?php echo html_escape($element->Element->name); ?></td>
                    <td><?php echo html_escape($element->Element->getElementSet()->name); ?></td>
                    <td class="element-order"><?php echo $this->formText("Elements[$id][order]", $element->order, array('class' => 'textinput')); ?></td>
                    <td><?php echo $this->formCheckbox("Elements[$id][delete]", null, array('checked' => false))?></td>
                </tr>
    <?php endforeach; ?>
            </tbody>
        </table>
    </fieldset>
    <fieldset>
        <input type="submit" class="form-submit" value="Save Changes" />
    </fieldset>
</form>
</div>

<?php foot();