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
    <table>
        <thead id="types-table-head">
            <tr>
                <th>Name</th>
                <th>Item Type</th>
                <th>Contributed Items</th>
                <th>File Upload</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tfoot>
            <tr id="types-table-foot">
                <td colspan="6"><?php echo $this->formSubmit('add-type', 'Add a Type', array('class' => 'add-element')); ?></td>
            </tr>
        </tfoot>
        <tbody id="types-table-body">
<?php foreach ($contributiontypes as $type): ?>
    <tr>
        <td width="28%"><strong><?php echo html_escape($type->display_name); ?></strong></td>
        <td><?php echo html_escape($type->ItemType->name); ?></td>
        <td><a href="<?php echo uri('items/browse/contributed/1/type/' . $type->item_type_id); ?>">View</a></td>
        <td><?php echo html_escape($type->file_permissions); ?></td>
        <td><a href="<?php echo uri(array('action' => 'edit', 'id' => $type->id)); ?>" class="edit">Edit</a></td>
        <td><?php echo delete_button(uri(array('action' => 'delete', 'id' => $type->id)), "delete-type-{$type->id}", 'Delete'); ?></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
echo js('contribution');
?>
<script type="text/javascript">
    var newRow = <?php
        $nameInput = $this->formText('newTypes[!!INDEX!!][display_name]', null, array('class' => 'textinput'));
        $typeSelect = contribution_select_item_type('newTypes[!!INDEX!!][item_type_id]');
        echo js_escape("<tr><td>$nameInput</td><td colspan=\"5\">$typeSelect</td></tr>");
        ?>;
    setUpTableAppend('#add-type', '#types-table-body', newRow);
</script>
<?php foot();