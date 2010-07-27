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
$head = array('title' => "$h1 | $h2",
              'bodyclass' => 'contribution primary',
              'content_class' => 'horizontal-nav');
head($head);
echo js('jquery');
?>
<script type="text/javascript">
jQuery.noConflict();

function getNewTypeRow(index) {
    var displayNameInput = '<input type="text" class="textinput" name="newTypes[' + index + '][display_name]"/>';
    var itemTypeSelect = <?php echo js_escape(contribution_select_item_type('newTypes[REPLACE][item_type_id]')); ?>;
    itemTypeSelect = itemTypeSelect.replace(/REPLACE/g, index);
    return '<tr><td>' + displayNameInput + '</td><td colspan="3">' + itemTypeSelect + '</td></tr>'
}
jQuery(document).ready(function() {
    var index = 0;
    jQuery('#add-type').click(function(event) {
        jQuery('#types-table-body').append(getNewTypeRow(index++));
        return false;
    });
});
</script>
<h1><a href="<?php echo uri('contribution'); ?>"><?php echo $h1; ?></a> | <?php echo $h2; ?></h1>
<ul id="section-nav" class="navigation">
<?php echo nav(array('Start' => uri('contribution/index'), 'Settings' => uri('contribution/settings'), 'Types' => uri('contribution/types'))); ?>
</ul>
<div id="primary">
    <?php echo flash(); ?>
    <form action="<?php echo uri('contribution/types/add'); ?>" method="POST">
    <table>
        <thead id="types-table-head">
            <tr>
                <th>Name</th>
                <th>Item Type</th>
                <th>Contributed Fields</th>
                <th>File Upload</th>
            </tr>
        </thead>
        <tfoot>
            <tr id="types-table-foot">
                <td colspan="4"><?php echo $this->formSubmit('add-type', 'Add a Type', array('class' => 'add-element')); ?></td>
            </tr>
        </tfoot>
        <tbody id="types-table-body">
<?php foreach ($typeInfoArray as $typeInfo): ?>
    <tr>
        <td><a href="<?php echo uri('contribution/types/edit/id/'.$typeInfo['id']); ?>"><?php echo html_escape($typeInfo['display_name']); ?></a></td>
        <td><?php echo html_escape($typeInfo['item_type_name']); ?></td>
        <td></td>
        <td><?php echo html_escape($typeInfo['file_permissions']); ?></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    <?php echo $this->formSubmit('submit-changes', 'Submit Changes', array('class' => 'submit-button')); ?>
    </form>
</div>

<?php foot();