<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Types'));
?>
<style type="text/css">
    td {
        vertical-align: middle;
    }
</style>
<div id="primary">
    <?php echo flash(); ?>
    <form action="<?php echo uri(array('action' => 'multiple-add')); ?>" method="POST">
    <table>
        <thead id="types-table-head">
            <tr>
                <th>Name</th>
                <th>Item Type</th>
                <th>Contributed Items</th>
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
        <td><a href="<?php echo uri('items/browse/contributed/1/type/'.$typeInfo['item_type_id']); ?>">View</a></td>
        <td><?php echo html_escape($typeInfo['file_permissions']); ?></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    <?php echo $this->formSubmit('submit-changes', 'Submit Changes', array('class' => 'submit-button')); ?>
    </form>
</div>
<?php
echo js('jquery');
echo js('contribution');
?>
<script type="text/javascript">
    var newRow = <?php
        $nameInput = $this->formText('newTypes[!!INDEX!!][display_name]', null, array('class' => 'textinput'));
        $typeSelect = contribution_select_item_type('newTypes[!!INDEX!!][item_type_id]');
        echo js_escape("<tr><td>$nameInput</td><td colspan=\"3\">$typeSelect</td></tr>");
        ?>;
    setUpTableAppend('#add-type', '#types-table-body', newRow);
</script>
<?php foot();