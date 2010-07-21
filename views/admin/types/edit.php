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
?>
<style type="text/css">
    table input.textinput
    {
        font-size: 1.0em;
    }
</style>
<script type="text/javascript">
    function getNewElementRow(index) {
        return '<tr><td><input name="newElements[' + index + '][prompt]" class="textinput" /></td><td colspan="4">' + <?php echo js_escape(select_element()); ?> + '</td></tr>';
    }

    jQuery.noConflict();
    var index = 0;
    jQuery(document).ready(function() {
        jQuery('#add-element').click(function() {
            jQuery('#add-element-row').before(getNewElementRow(index++));
            return false;
        });
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
        <table>
            <thead>
                <tr>
                    <th>Prompt</th>
                    <th>Element</th>
                    <th>Set</th>
                    <th>Description</th>
                    <th>Order</th>
                    <th>Delete?</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($contributionTypeElements as $element):
    $id = $element->id; ?>
                <tr>
                    <td><?php echo $this->formText("Elements[$id][prompt]", $element->prompt, array('class' => 'textinput')); ?></td>
                    <td><?php echo html_escape($element->Element->name); ?></td>
                    <td><?php echo html_escape($element->Element->getElementSet()->name); ?></td>
                    <td><?php echo html_escape($element->Element->description); ?></td>
                    <td><?php echo $this->formText("Elements[$id][order]", $element->order, array('class' => 'textinput')); ?></td>
                    <td><?php echo $this->formCheckbox("Elements[$id][delete]", null, array('checked' => false))?></td>
                </tr>
    <?php endforeach; ?>
                <tr id="add-element-row">
                    <td colspan="5"><input type="submit" class="add-element" id="add-element" value="Add an Element" /></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <fieldset>
        <input type="submit" class="form-submit" value="Save Changes" />
    </fieldset>
</form>
</div>

<?php foot();